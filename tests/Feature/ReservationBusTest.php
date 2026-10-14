<?php

namespace Tests\Feature;

use App\Exceptions\ReservationImpossibleException;
use App\Models\PlanificationVoyage;
use App\Models\Ticket;
use App\Models\Utilisateur;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\Support\DonneesBus;
use Tests\TestCase;

/**
 * Réservation de places sur un départ de bus (phase 1).
 *
 * Nécessite MySQL/MariaDB (verrous de ligne, enums) :
 *   DB_CONNECTION=mysql DB_DATABASE=... php artisan test --filter=ReservationBusTest
 */
class ReservationBusTest extends TestCase
{
    use RefreshDatabase, DonneesBus;

    private ReservationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql' && DB::getDriverName() !== 'mariadb') {
            $this->markTestSkipped('Tests de réservation à lancer sur MySQL/MariaDB.');
        }

        $this->service = app(ReservationService::class);
    }

    // ------------------------------------------------------------------
    // Service de réservation
    // ------------------------------------------------------------------

    public function test_une_reservation_bloque_les_places_et_remplit_le_ticket(): void
    {
        $depart = $this->creerDepart();

        $tickets = $this->service->reserver($depart->id, $this->passagers(2), 'MTN', 'REF-1');

        $this->assertCount(2, $tickets);
        $this->assertSame(1, $depart->fresh()->places_disponibles);

        $t = $tickets->first()->fresh();
        $this->assertSame('En_Attente', $t->statut_paiement);
        $this->assertNotNull($t->expire_le);
        $this->assertSame('REF-1', $t->reference_paiement);
        $this->assertSame('Brazzaville', $t->ville_depart);
        $this->assertSame('Gare de Mpila → Agence de Nkayi', $t->nom_trajet);
        $this->assertSame('BZ-123-AA', $t->numero_bus);
        $this->assertSame('07:30:00', $t->horaire);
        $this->assertEquals(7000, (float) $t->montant_total);
    }

    public function test_impossible_de_reserver_plus_que_les_places_restantes(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 2]);

        try {
            $this->service->reserver($depart->id, $this->passagers(3), 'MTN', 'REF-2');
            $this->fail('La réservation aurait dû être refusée.');
        } catch (ReservationImpossibleException $e) {
            $this->assertSame(409, $e->statutHttp());
            $this->assertStringContainsString('2 place', $e->getMessage());
        }

        $this->assertSame(0, Ticket::count(), 'Aucun ticket ne doit être créé.');
        $this->assertSame(2, $depart->fresh()->places_disponibles);
    }

    public function test_un_depart_complet_est_refuse(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 1]);
        $this->service->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-3', payeImmediatement: true);

        $this->expectException(ReservationImpossibleException::class);
        $this->expectExceptionMessage('complet');
        $this->service->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-4');
    }

    public function test_un_depart_annule_ou_passe_est_refuse(): void
    {
        $annule = $this->creerDepart(['statut_voyage' => 'Annulé']);
        $passe  = $this->creerDepart(['date_voyage' => now()->subDay()->toDateString()]);

        foreach ([$annule, $passe] as $depart) {
            try {
                $this->service->reserver($depart->id, $this->passagers(1), 'MTN', uniqid());
                $this->fail('Départ fermé accepté.');
            } catch (ReservationImpossibleException) {
                $this->assertTrue(true);
            }
        }
        $this->assertSame(0, Ticket::count());
    }

    public function test_une_reservation_expiree_libere_ses_places(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 2]);
        $this->service->reserver($depart->id, $this->passagers(2), 'MTN', 'REF-5');
        $this->assertSame(0, $depart->fresh()->places_disponibles);

        // Le délai de paiement est dépassé
        Ticket::query()->update(['expire_le' => now()->subMinute()]);

        $this->assertSame(2, $this->service->expirerReservations());
        $this->assertSame(2, $depart->fresh()->places_disponibles);
        $this->assertSame(2, Ticket::where('statut_paiement', 'Echoue')->count());

        // La place libérée peut être réservée par quelqu'un d'autre
        $this->service->reserver($depart->id, $this->passagers(2), 'MTN', 'REF-6');
        $this->assertSame(0, $depart->fresh()->places_disponibles);
    }

    public function test_une_reservation_expiree_ne_bloque_pas_un_nouveau_client_meme_sans_tache_planifiee(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 1]);
        $this->service->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-7');
        Ticket::query()->update(['expire_le' => now()->subMinute()]);

        // Pas d'appel à expirerReservations() : reserver() expire lui-même
        $tickets = $this->service->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-8');

        $this->assertCount(1, $tickets);
        $this->assertSame('Echoue', Ticket::where('reference_paiement', 'REF-7')->value('statut_paiement'));
    }

    public function test_liberer_rend_les_places_d_un_paiement_echoue(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 3]);
        $this->service->reserver($depart->id, $this->passagers(2), 'MTN', 'REF-9');

        $this->assertSame(2, $this->service->liberer('REF-9'));
        $this->assertSame(3, $depart->fresh()->places_disponibles);
    }

    public function test_un_ticket_paye_n_est_jamais_expire(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 1]);
        $this->service->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-10', payeImmediatement: true);

        $this->assertSame(0, $this->service->expirerReservations());
        $this->assertSame('Paye', Ticket::value('statut_paiement'));
        $this->assertSame(0, $depart->fresh()->places_disponibles);
    }

    // ------------------------------------------------------------------
    // API de paiement
    // ------------------------------------------------------------------

    public function test_api_mtn_simulation_cree_les_tickets_et_bloque_les_places(): void
    {
        config(['momo.simulation' => true]);
        $depart = $this->creerDepart(['nombre_tickets_total' => 3]);
        Sanctum::actingAs($this->client());

        $this->postJson('/api/momo/pay', [
            'amount' => 14000, 'phone' => '061234567',
            'planification_voyage_id' => $depart->id, 'passagers' => $this->passagers(2),
        ])->assertOk()
          ->assertJsonPath('status', 'PAID')
          ->assertJsonCount(2, 'tickets')
          ->assertJsonPath('tickets.0.numeroBus', 'BZ-123-AA');

        $this->assertSame(1, $depart->fresh()->places_disponibles);
    }

    public function test_api_refuse_un_montant_incorrect_mtn_et_airtel(): void
    {
        config(['momo.simulation' => true]);
        $depart = $this->creerDepart();
        Sanctum::actingAs($this->client());

        $this->postJson('/api/momo/pay', [
            'amount' => 100, 'phone' => '061234567',
            'planification_voyage_id' => $depart->id, 'passagers' => $this->passagers(1),
        ])->assertStatus(422);

        $this->postJson('/api/airtel/pay', [
            'amount' => 100, 'phone' => '051234567',
            'planification_voyage_id' => $depart->id, 'passagers' => $this->passagers(1),
        ])->assertStatus(422);

        $this->assertSame(0, Ticket::count());
    }

    public function test_api_refuse_quand_le_depart_est_complet(): void
    {
        config(['momo.simulation' => true]);
        $depart = $this->creerDepart(['nombre_tickets_total' => 1]);
        Sanctum::actingAs($this->client());

        $payload = [
            'amount' => 7000, 'phone' => '061234567',
            'planification_voyage_id' => $depart->id, 'passagers' => $this->passagers(1),
        ];
        $this->postJson('/api/momo/pay', $payload)->assertOk();
        $this->postJson('/api/momo/pay', $payload)->assertStatus(409)->assertJsonPath('message', 'Ce départ est complet.');

        $this->assertSame(1, Ticket::count());
    }

    public function test_api_mtn_reel_en_echec_libere_les_places(): void
    {
        $this->desactiverSimulation();
        config([
            'momo.collection.api_user' => 'user', 'momo.collection.api_key' => 'key',
            'momo.collection.primary_key' => 'primary',
        ]);
        Http::fake([
            '*token*'       => Http::response(['access_token' => 'jeton'], 200),
            '*requesttopay*' => Http::response(['message' => 'refusé'], 500),
        ]);
        $depart = $this->creerDepart(['nombre_tickets_total' => 2]);
        Sanctum::actingAs($this->client());

        $this->postJson('/api/momo/pay', [
            'amount' => 7000, 'phone' => '061234567',
            'planification_voyage_id' => $depart->id, 'passagers' => $this->passagers(1),
        ])->assertStatus(500);

        $this->assertSame('Echoue', Ticket::value('statut_paiement'));
        $this->assertSame(2, $depart->fresh()->places_disponibles);
        $this->assertSame('FAILED', DB::table('paiements_mtn_momo')->value('status'));
    }

    public function test_api_airtel_reel_laisse_les_tickets_en_attente(): void
    {
        $this->desactiverSimulation();
        config(['airtel.client_id' => 'id', 'airtel.client_secret' => 'secret', 'airtel.simulation' => false]);
        Http::fake([
            '*airtel.africa/auth/*'     => Http::response(['access_token' => 'jeton'], 200),
            '*airtel.africa/merchant/*' => Http::response(['status' => ['success' => true]], 200),
        ]);
        $depart = $this->creerDepart(['nombre_tickets_total' => 2]);
        Sanctum::actingAs($this->client());

        $this->postJson('/api/airtel/pay', [
            'amount' => 7000, 'phone' => '051234567',
            'planification_voyage_id' => $depart->id, 'passagers' => $this->passagers(1),
        ])->assertOk()->assertJsonPath('status', 'PENDING');

        // Avant : Airtel marquait le ticket « Paye » dès la demande
        $this->assertSame('En_Attente', Ticket::value('statut_paiement'));
        $this->assertSame('PENDING', DB::table('paiements_airtel')->value('status'));
        $this->assertSame(1, $depart->fresh()->places_disponibles);
    }

    protected function tearDown(): void
    {
        // Les variables d'environnement survivent d'un test à l'autre : on les retire
        foreach (['SIMULATION_MODE', 'MTN_SIMULATION', 'AIRTEL_SIMULATION'] as $v) {
            putenv($v);
            unset($_ENV[$v], $_SERVER[$v]);
        }
        parent::tearDown();
    }

    private function desactiverSimulation(): void
    {
        foreach (['SIMULATION_MODE', 'MTN_SIMULATION', 'AIRTEL_SIMULATION'] as $v) {
            putenv("{$v}=false");
            $_ENV[$v] = $_SERVER[$v] = 'false';
        }
        config(['momo.simulation' => false]);
    }
}
