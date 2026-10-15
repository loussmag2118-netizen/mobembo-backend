<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Support\DonneesBus;
use Tests\TestCase;

/**
 * Tarif enfant : prix par départ, âge limite par agence, contrôle du montant.
 */
class TarifEnfantTest extends TestCase
{
    use RefreshDatabase, DonneesBus;

    protected function setUp(): void
    {
        parent::setUp();
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('À lancer sur MySQL/MariaDB.');
        }
        config(['momo.simulation' => true]);
    }

    private function payer(int $departId, array $passagers, float $montant)
    {
        return $this->postJson('/api/momo/pay', [
            'amount' => $montant, 'phone' => '061234567',
            'planification_voyage_id' => $departId, 'passagers' => $passagers,
        ]);
    }

    public function test_un_parent_et_son_enfant_paient_le_bon_total(): void
    {
        $depart = $this->creerDepart(['prix_enfant' => 5000]);
        Sanctum::actingAs($this->client());

        $passagers = [
            ['nom' => 'PARENT', 'prenom' => 'Marie'],
            ['nom' => 'PARENT', 'prenom' => 'Junior', 'categorie' => 'Enfant', 'age' => 8],
        ];

        // Le prix adulte appliqué à l'enfant est refusé
        $this->payer($depart->id, $passagers, 14000)->assertStatus(422);

        $this->payer($depart->id, $passagers, 12000)->assertOk()
            ->assertJsonPath('tickets.1.categorie', 'Enfant')
            ->assertJsonPath('tickets.1.age', 8)
            ->assertJsonPath('tickets.1.prix', '5000');

        $enfant = Ticket::where('categorie_passager', 'Enfant')->first();
        $this->assertEquals(5000, (float) $enfant->montant_total);
        $this->assertEquals(7000, (float) Ticket::where('categorie_passager', 'Adulte')->value('montant_total'));
        $this->assertSame(1, $depart->fresh()->places_disponibles, 'Un enfant occupe une place.');
    }

    public function test_sans_prix_enfant_l_enfant_paie_le_prix_adulte(): void
    {
        $depart = $this->creerDepart();   // prix_enfant non renseigné
        Sanctum::actingAs($this->client());

        $this->payer($depart->id, [['nom' => 'E', 'categorie' => 'Enfant', 'age' => 5]], 7000)->assertOk();
    }

    public function test_l_age_de_l_enfant_est_obligatoire_et_limite_par_l_agence(): void
    {
        $depart = $this->creerDepart(['prix_enfant' => 5000]);
        DB::table('agences')->where('id', $depart->agence_id)->update(['age_max_enfant' => 10]);
        Sanctum::actingAs($this->client());

        $this->payer($depart->id, [['nom' => 'E', 'categorie' => 'Enfant']], 5000)
            ->assertStatus(422)->assertJsonPath('message', "Indiquez l'âge de chaque enfant.");

        $this->payer($depart->id, [['nom' => 'E', 'categorie' => 'Enfant', 'age' => 11]], 5000)
            ->assertStatus(422)->assertJsonFragment(['message' => "Le tarif enfant s'applique jusqu'à 10 ans. Choisissez le tarif adulte pour un passager de 11 ans."]);

        $this->assertSame(0, Ticket::count());
    }

    public function test_l_agent_voit_la_categorie_et_l_age_au_scan(): void
    {
        $depart = $this->creerDepart(['prix_enfant' => 5000]);
        $ticket = app(ReservationService::class)->reserver($depart->id,
            [['nom' => 'E', 'categorie' => 'Enfant', 'age' => 7]], 'MTN', 'REF-E', payeImmediatement: true)->first();
        Sanctum::actingAs($this->personnel('Agent', $depart->agence_id));

        $this->getJson('/api/agent/ticket/' . $ticket->reference_ticket)
            ->assertOk()->assertJsonPath('ticket.categorie', 'Enfant')->assertJsonPath('ticket.age', 7);
    }

    public function test_la_recherche_affiche_le_prix_enfant(): void
    {
        $this->creerDepart(['prix_enfant' => 5000]);
        $date = now()->addDays(3)->toDateString();

        $this->getJson("/api/voyages/recherche?ville_depart=Brazzaville&ville_arrivee=Nkayi&date={$date}")
            ->assertOk()->assertJsonPath('voyages.0.prix_enfant', 5000)->assertJsonPath('voyages.0.age_max_enfant', 12);
    }

    public function test_seul_l_admin_regle_l_age_enfant_de_son_agence(): void
    {
        $depart = $this->creerDepart();

        $this->actingAs($this->personnel('Agent', $depart->agence_id), 'web');
        $this->patch('/admin/agence/age-enfant', ['age_max_enfant' => 15])->assertForbidden();

        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');
        $this->patch('/admin/agence/age-enfant', ['age_max_enfant' => 11])->assertRedirect();
        $this->assertSame(11, (int) DB::table('agences')->where('id', $depart->agence_id)->value('age_max_enfant'));

        $this->patch('/admin/agence/age-enfant', ['age_max_enfant' => 30])->assertSessionHasErrors('age_max_enfant');
    }

    public function test_le_prix_enfant_ne_peut_pas_depasser_le_prix_adulte(): void
    {
        $depart = $this->creerDepart();
        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');

        $this->put("/admin/planifications/{$depart->id}", [
            'trajet_id' => $depart->trajet_id, 'bus_id' => $depart->bus_id, 'vague_depart_id' => $depart->vague_depart_id,
            'date_voyage' => $depart->date_voyage->toDateString(), 'prix' => 7000, 'prix_enfant' => 9000,
            'statut_voyage' => 'Programmation',
        ])->assertSessionHasErrors('prix_enfant');

        $this->put("/admin/planifications/{$depart->id}", [
            'trajet_id' => $depart->trajet_id, 'bus_id' => $depart->bus_id, 'vague_depart_id' => $depart->vague_depart_id,
            'date_voyage' => $depart->date_voyage->toDateString(), 'prix' => 7000, 'prix_enfant' => 5000,
            'statut_voyage' => 'Programmation',
        ])->assertSessionHasNoErrors();
        $this->assertEquals(5000, (float) $depart->fresh()->prix_enfant);
    }
}
