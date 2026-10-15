<?php

namespace Tests\Feature;

use App\Exceptions\ReservationImpossibleException;
use App\Models\Ticket;
use App\Services\PaiementService;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\Support\DonneesBus;
use Tests\TestCase;

/**
 * Choix du siège (activé par agence, ex. Nzoko Transport).
 */
class ChoixSiegeTest extends TestCase
{
    use RefreshDatabase, DonneesBus;

    private ReservationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('À lancer sur MySQL/MariaDB.');
        }
        $this->service = app(ReservationService::class);
        config(['momo.simulation' => true]);
    }

    private function departAvecSieges(array $surcharge = [])
    {
        $depart = $this->creerDepart(array_merge(['nombre_tickets_total' => 8], $surcharge));
        DB::table('agences')->where('id', $depart->agence_id)->update(['choix_siege' => true]);

        return $depart->fresh();
    }

    public function test_le_client_obtient_les_sieges_choisis(): void
    {
        $depart = $this->departAvecSieges();
        $tickets = $this->service->reserver($depart->id, [['nom' => 'A', 'siege' => 3], ['nom' => 'B', 'siege' => 4]], 'MTN', 'R1');

        $this->assertSame([3, 4], $tickets->pluck('numero_siege')->map(fn ($n) => (int) $n)->all());
        $this->assertSame([3, 4], $this->service->siegesOccupes($depart));
    }

    public function test_un_siege_deja_pris_est_refuse(): void
    {
        $depart = $this->departAvecSieges();
        $this->service->reserver($depart->id, [['nom' => 'A', 'siege' => 5]], 'MTN', 'R1', payeImmediatement: true);

        try {
            $this->service->reserver($depart->id, [['nom' => 'B', 'siege' => 5]], 'MTN', 'R2');
            $this->fail('Siège vendu deux fois.');
        } catch (ReservationImpossibleException $e) {
            $this->assertSame(409, $e->statutHttp());
            $this->assertStringContainsString('siège 5', $e->getMessage());
        }
        $this->assertSame(1, Ticket::count());
    }

    public function test_siege_en_double_ou_inexistant_refuse(): void
    {
        $depart = $this->departAvecSieges();

        $this->expectException(ReservationImpossibleException::class);
        $this->service->reserver($depart->id, [['nom' => 'A', 'siege' => 2], ['nom' => 'B', 'siege' => 2]], 'MTN', 'R1');
    }

    public function test_siege_hors_du_bus_refuse(): void
    {
        $depart = $this->departAvecSieges();

        $this->expectExceptionMessage("Le siège 9 n'existe pas dans ce bus.");
        $this->service->reserver($depart->id, [['nom' => 'A', 'siege' => 9]], 'MTN', 'R1');
    }

    public function test_sans_choix_un_siege_libre_est_attribue(): void
    {
        $depart = $this->departAvecSieges();
        $this->service->reserver($depart->id, [['nom' => 'A', 'siege' => 1]], 'MTN', 'R1');

        $tickets = $this->service->reserver($depart->id, [['nom' => 'B'], ['nom' => 'C', 'siege' => 3]], 'MTN', 'R2');

        $this->assertSame([2, 3], $tickets->pluck('numero_siege')->map(fn ($n) => (int) $n)->all());
    }

    public function test_agence_sans_choix_du_siege_inchangee(): void
    {
        $depart = $this->creerDepart();   // choix_siege = false
        $tickets = $this->service->reserver($depart->id, [['nom' => 'A', 'siege' => 3]], 'MTN', 'R1');

        $this->assertNull($tickets->first()->numero_siege);
    }

    public function test_une_reservation_expiree_libere_son_siege(): void
    {
        $depart = $this->departAvecSieges();
        $this->service->reserver($depart->id, [['nom' => 'A', 'siege' => 6]], 'MTN', 'R1');
        Ticket::query()->update(['expire_le' => now()->subMinute()]);

        $tickets = $this->service->reserver($depart->id, [['nom' => 'B', 'siege' => 6]], 'MTN', 'R2');

        $this->assertSame(6, (int) $tickets->first()->numero_siege);
        $this->assertSame('Echoue', Ticket::where('reference_paiement', 'R1')->value('statut_paiement'));
        $this->assertSame(6, (int) Ticket::where('reference_paiement', 'R1')->value('numero_siege'), "L'historique garde le numéro.");
    }

    public function test_la_base_interdit_deux_billets_actifs_sur_le_meme_siege(): void
    {
        $depart = $this->departAvecSieges();
        $t = $this->service->reserver($depart->id, [['nom' => 'A', 'siege' => 7]], 'MTN', 'R1', payeImmediatement: true)->first();

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        $copie = $t->replicate(['siege_occupe']);
        $copie->reference_ticket = 'TKT-COPIE';
        $copie->qr_code_token = str_repeat('x', 64);
        $copie->save();
    }

    public function test_le_plan_public_ne_contient_aucune_donnee_personnelle(): void
    {
        $depart = $this->departAvecSieges();
        $this->service->reserver($depart->id, [['nom' => 'SECRET', 'siege' => 2]], 'MTN', 'R1', payeImmediatement: true);

        $r = $this->getJson("/api/departs/{$depart->id}/sieges")->assertOk()
            ->assertJsonPath('choix_siege', true)
            ->assertJsonPath('nombre_sieges', 8)
            ->assertJsonPath('disposition.gauche', 2)
            ->assertJsonPath('occupes', [2]);
        $this->assertStringNotContainsString('SECRET', $r->getContent());
    }

    public function test_paiement_avec_siege_via_l_api(): void
    {
        $depart = $this->departAvecSieges();
        Sanctum::actingAs($this->client());

        $this->postJson('/api/momo/pay', [
            'amount' => 7000, 'phone' => '061234567', 'planification_voyage_id' => $depart->id,
            'passagers' => [['nom' => 'A', 'siege' => 8]],
        ])->assertOk()->assertJsonPath('tickets.0.siege', 8);

        $this->postJson('/api/momo/pay', [
            'amount' => 7000, 'phone' => '061234567', 'planification_voyage_id' => $depart->id,
            'passagers' => [['nom' => 'B', 'siege' => 8]],
        ])->assertStatus(409);
    }

    public function test_l_admin_voit_le_plan_et_regle_l_option(): void
    {
        $depart = $this->departAvecSieges();
        $this->service->reserver($depart->id, [['nom' => 'MOUKALA', 'prenom' => 'Ines', 'siege' => 4]], 'MTN', 'R1', payeImmediatement: true);
        $admin = $this->personnel('Admin', $depart->agence_id);
        $agent = $this->personnel('Agent', $depart->agence_id);

        $this->actingAs($agent, 'web');
        $this->getJson("/admin/planifications/{$depart->id}/sieges")->assertOk()
            ->assertJsonPath('details.0.siege', 4)->assertJsonPath('details.0.nom', 'MOUKALA Ines');
        $this->patch('/admin/agence/choix-siege', ['choix_siege' => 0])->assertForbidden();

        $this->actingAs($admin, 'web');
        $this->patch('/admin/agence/choix-siege', ['choix_siege' => 0])->assertRedirect();
        $this->assertFalse((bool) DB::table('agences')->where('id', $depart->agence_id)->value('choix_siege'));

        // Une autre agence ne voit pas ce plan
        $autre = $this->creerDepart();
        $this->actingAs($this->personnel('Admin', $autre->agence_id), 'web');
        $this->getJson("/admin/planifications/{$depart->id}/sieges")->assertForbidden();
    }

    public function test_paiement_tardif_sur_un_siege_revendu_est_a_rembourser(): void
    {
        $depart = $this->departAvecSieges();
        $this->service->reserver($depart->id, [['nom' => 'A', 'siege' => 1]], 'MTN', 'REF-TARD');
        DB::table('paiements_mtn_momo')->insert([
            'reference_id' => 'REF-TARD', 'amount' => 7000, 'currency' => 'XAF', 'payer_msisdn' => '061234567',
            'status' => 'PENDING', 'environment' => 'sandbox', 'simulation' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        Ticket::query()->update(['expire_le' => now()->subMinute()]);
        $this->service->reserver($depart->id, [['nom' => 'B', 'siege' => 1]], 'MTN', 'REF-B', payeImmediatement: true);

        config(['momo.simulation' => false, 'momo.collection.api_user' => 'u', 'momo.collection.api_key' => 'k', 'momo.collection.primary_key' => 'p']);
        Http::fake([
            '*collection/token*' => Http::response(['access_token' => 'j'], 200),
            '*requesttopay/*'    => Http::response(['status' => 'SUCCESSFUL'], 200),
        ]);

        $etat = app(PaiementService::class)->actualiser('REF-TARD');

        $this->assertSame('A_REMBOURSER', $etat['statut']);
        $this->assertSame(1, Ticket::where('numero_siege', 1)->where('statut_paiement', 'Paye')->count());
    }
}
