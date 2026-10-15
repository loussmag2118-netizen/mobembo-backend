<?php

namespace Tests\Feature;

use App\Exceptions\ReservationImpossibleException;
use App\Models\PlanificationVoyage;
use App\Models\Ticket;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Support\DonneesBus;
use Tests\TestCase;

/**
 * Processus du billet de bout en bout : capacité des départs, sièges déjà
 * vendus protégés, départ parti, embarquement le bon jour.
 */
class ProcessusBilletTest extends TestCase
{
    use RefreshDatabase, DonneesBus;

    private ReservationService $reservations;

    protected function setUp(): void
    {
        parent::setUp();
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('À lancer sur MySQL/MariaDB.');
        }
        $this->reservations = app(ReservationService::class);
        config(['momo.simulation' => true]);
    }

    /** Champs du formulaire de modification d'un départ. */
    private function formulaire(PlanificationVoyage $p, array $surcharge = []): array
    {
        return array_merge([
            'trajet_id' => $p->trajet_id, 'bus_id' => $p->bus_id, 'vague_depart_id' => $p->vague_depart_id,
            'nombre_tickets_total' => $p->nombre_tickets_total, 'prix' => 7000,
            'date_voyage' => $p->date_voyage_carbon->toDateString(), 'statut_voyage' => 'Programmation',
        ], $surcharge);
    }

    private function vendre(PlanificationVoyage $depart, int $n = 1): void
    {
        $this->reservations->reserver($depart->id, $this->passagers($n), 'MTN', 'R-' . uniqid(), payeImmediatement: true);
    }

    public function test_un_bus_deja_parti_ne_se_reserve_plus(): void
    {
        $depart = $this->creerDepart();
        DB::table('planification_voyages')->where('id', $depart->id)->update(['date_voyage' => today()->toDateString()]);
        DB::table('vague_departs')->where('id', $depart->vague_depart_id)->update(['heure_depart' => now()->subHour()->format('H:i:s')]);

        $this->expectException(ReservationImpossibleException::class);
        $this->expectExceptionMessage('déjà parti');
        $this->reservations->reserver($depart->id, $this->passagers(1), 'MTN', 'R-PARTI');
    }

    public function test_un_depart_ne_peut_pas_avoir_plus_de_billets_que_de_places_dans_le_bus(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 10]);   // bus de 50 places
        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');

        $this->put("/admin/planifications/{$depart->id}", $this->formulaire($depart, ['nombre_tickets_total' => 60]))
            ->assertSessionHasErrors('nombre_tickets_total');
        $this->put("/admin/planifications/{$depart->id}", $this->formulaire($depart, ['nombre_tickets_total' => 50]))
            ->assertSessionHasNoErrors();
        $this->assertSame(50, (int) $depart->fresh()->nombre_tickets_total);
    }

    public function test_les_places_deja_vendues_sont_protegees(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 10]);
        $this->vendre($depart, 4);
        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');

        // Moins de places que de billets vendus : refusé
        $this->put("/admin/planifications/{$depart->id}", $this->formulaire($depart, ['nombre_tickets_total' => 3]))
            ->assertSessionHasErrors('nombre_tickets_total');
        // Changer le trajet d'un départ vendu : refusé
        $autreTrajet = DB::table('trajets')->insertGetId([
            'agence_id' => $depart->agence_id, 'ville_depart' => 'Nkayi', 'ville_arrivee' => 'Brazzaville',
            'gare_depart_id' => DB::table('trajets')->where('id', $depart->trajet_id)->value('gare_arrivee_id'),
            'gare_arrivee_id' => DB::table('trajets')->where('id', $depart->trajet_id)->value('gare_depart_id'),
            'bus_id' => $depart->bus_id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->put("/admin/planifications/{$depart->id}", $this->formulaire($depart, ['trajet_id' => $autreTrajet]))
            ->assertSessionHasErrors();
        // Supprimer un départ vendu : refusé
        $this->delete("/admin/planifications/{$depart->id}")->assertSessionHas('error');
        $this->assertNotNull(PlanificationVoyage::find($depart->id));
    }

    public function test_changer_de_bus_met_a_jour_les_billets_vendus(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 10]);
        $this->vendre($depart, 2);
        $nouveauBus = DB::table('bus')->insertGetId([
            'agence_id' => $depart->agence_id, 'immatriculation' => '456 CD 4', 'modele' => 'Higer',
            'capacite_totale' => 30, 'statut' => 'Disponible', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');

        $this->put("/admin/planifications/{$depart->id}", $this->formulaire($depart, ['bus_id' => $nouveauBus]))
            ->assertSessionHasNoErrors();

        $this->assertSame(['456 CD 4'], Ticket::where('planification_voyage_id', $depart->id)->pluck('numero_bus')->unique()->values()->all());
    }

    public function test_un_depart_a_zero_franc_est_refuse(): void
    {
        $depart = $this->creerDepart();
        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');

        $this->put("/admin/planifications/{$depart->id}", $this->formulaire($depart, ['prix' => 0]))
            ->assertSessionHasErrors('prix');
    }

    public function test_l_embarquement_se_fait_le_jour_du_depart(): void
    {
        $depart = $this->creerDepart();   // dans 3 jours
        $ticket = $this->reservations->reserver($depart->id, $this->passagers(1), 'MTN', 'R-JOUR', payeImmediatement: true)->first();
        Sanctum::actingAs($this->personnel('Agent', $depart->agence_id));

        $this->postJson('/api/agent/update-presence', ['ticket_id' => $ticket->id, 'present' => true])
            ->assertStatus(409)->assertJsonPath('code', 'AUTRE_DATE');

        DB::table('planification_voyages')->where('id', $depart->id)->update(['date_voyage' => today()->toDateString(), 'statut_voyage' => 'Annulé']);
        $this->postJson('/api/agent/update-presence', ['ticket_id' => $ticket->id, 'present' => true])
            ->assertStatus(409)->assertJsonPath('code', 'DEPART_ANNULE');

        DB::table('planification_voyages')->where('id', $depart->id)->update(['statut_voyage' => 'Programmation']);
        $this->postJson('/api/agent/update-presence', ['ticket_id' => $ticket->id, 'present' => true])->assertOk();
    }

    public function test_les_sieges_attribues_sont_tous_differents_et_dans_le_bus(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 6]);
        DB::table('agences')->where('id', $depart->agence_id)->update(['choix_siege' => true]);

        $this->reservations->reserver($depart->id, [['nom' => 'A', 'siege' => 2], ['nom' => 'B']], 'MTN', 'R-S1', payeImmediatement: true);
        $this->reservations->reserver($depart->id, $this->passagers(4), 'MTN', 'R-S2', payeImmediatement: true);

        $sieges = Ticket::where('planification_voyage_id', $depart->id)->pluck('numero_siege')->map(fn ($s) => (int) $s)->sort()->values()->all();
        $this->assertSame([1, 2, 3, 4, 5, 6], $sieges);
        $this->assertSame(0, (int) $depart->fresh()->places_disponibles);

        $this->expectException(ReservationImpossibleException::class);
        $this->reservations->reserver($depart->id, $this->passagers(1), 'MTN', 'R-S3');
    }
}
