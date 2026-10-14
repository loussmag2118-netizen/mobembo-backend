<?php

namespace Tests\Feature;

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
 * Paiement, billets, contrôle, rôles et recherche (phases 2 à 5).
 * À lancer sur MySQL/MariaDB.
 */
class BilletterieBusTest extends TestCase
{
    use RefreshDatabase, DonneesBus;

    private ReservationService $reservations;
    private PaiementService $paiements;

    protected function setUp(): void
    {
        parent::setUp();
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('À lancer sur MySQL/MariaDB.');
        }
        $this->reservations = app(ReservationService::class);
        $this->paiements    = app(PaiementService::class);
    }

    protected function tearDown(): void
    {
        foreach (['SIMULATION_MODE', 'MTN_SIMULATION', 'AIRTEL_SIMULATION'] as $v) {
            putenv($v);
            unset($_ENV[$v], $_SERVER[$v]);
        }
        parent::tearDown();
    }

    /** Statut que renvoie l'API MTN simulée */
    private string $statutMtn = 'PENDING';
    private bool $fauxMtnInstalle = false;

    /** MTN en mode réel ; la réponse de l'API (simulée) suit $statutMtn. */
    private function mtnReel(string $statutOperateur = 'PENDING'): void
    {
        $this->statutMtn = $statutOperateur;
        config([
            'momo.simulation' => false,
            'momo.collection.api_user' => 'u', 'momo.collection.api_key' => 'k', 'momo.collection.primary_key' => 'p',
        ]);
        if ($this->fauxMtnInstalle) {
            return;
        }
        $this->fauxMtnInstalle = true;
        Http::fake(function ($requete) {
            $url = $requete->url();
            if (str_contains($url, 'collection/token')) {
                return Http::response(['access_token' => 'jeton'], 200);
            }
            if (preg_match('#requesttopay/[^/]+$#', $url)) {
                return Http::response(['status' => $this->statutMtn, 'financialTransactionId' => 'FT-1'], 200);
            }
            if (str_ends_with($url, 'requesttopay')) {
                return Http::response(null, 202);
            }

            return Http::response(null, 404);
        });
    }

    private function payerMtn(int $departId, int $passagers = 1, array $entetes = [])
    {
        return $this->withHeaders($entetes)->postJson('/api/momo/pay', [
            'amount' => 7000 * $passagers, 'phone' => '061234567',
            'planification_voyage_id' => $departId, 'passagers' => $this->passagers($passagers),
        ]);
    }

    // ==================================================================
    // Paiement réel : confirmation par l'opérateur
    // ==================================================================

    public function test_paiement_en_attente_ne_fournit_pas_le_contenu_des_billets(): void
    {
        $this->mtnReel('PENDING');
        $depart = $this->creerDepart();
        Sanctum::actingAs($client = $this->client());

        $ref = $this->payerMtn($depart->id)->assertOk()->assertJsonPath('statut', 'EN_ATTENTE')->json('reference_id');

        $this->getJson("/api/paiements/{$ref}/statut")
            ->assertOk()->assertJsonPath('statut', 'EN_ATTENTE')->assertJsonCount(0, 'tickets');
        $this->assertSame('En_Attente', Ticket::value('statut_paiement'));
    }

    public function test_paiement_confirme_par_mtn_rend_les_billets_valables(): void
    {
        $this->mtnReel('PENDING');
        $depart = $this->creerDepart();
        Sanctum::actingAs($this->client());
        $ref = $this->payerMtn($depart->id, 2)->json('reference_id');

        // MTN confirme le paiement
        $this->mtnReel('SUCCESSFUL');
        $this->getJson("/api/paiements/{$ref}/statut")
            ->assertOk()->assertJsonPath('statut', 'PAYE')->assertJsonCount(2, 'tickets');

        $this->assertSame(2, Ticket::where('statut_paiement', 'Paye')->count());
        $this->assertNull(Ticket::value('expire_le'));
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'paiement.confirme')->count());

        // Idempotent : une seconde confirmation ne change rien
        $this->paiements->confirmer($ref);
        $this->paiements->actualiser($ref);
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'paiement.confirme')->count());
        $this->assertSame(1, $depart->fresh()->places_disponibles);
    }

    public function test_paiement_refuse_par_mtn_libere_les_places(): void
    {
        $this->mtnReel('PENDING');
        $depart = $this->creerDepart();
        Sanctum::actingAs($this->client());
        $ref = $this->payerMtn($depart->id, 2)->json('reference_id');

        $this->mtnReel('FAILED');
        $this->getJson("/api/paiements/{$ref}/statut")->assertOk()->assertJsonPath('statut', 'ECHOUE');

        $this->assertSame(2, Ticket::where('statut_paiement', 'Echoue')->count());
        $this->assertSame(3, $depart->fresh()->places_disponibles);
    }

    public function test_paiement_expire_sans_validation(): void
    {
        $this->mtnReel('PENDING');
        $depart = $this->creerDepart();
        Sanctum::actingAs($this->client());
        $ref = $this->payerMtn($depart->id)->json('reference_id');

        Ticket::query()->update(['expire_le' => now()->subMinute()]);
        $this->getJson("/api/paiements/{$ref}/statut")->assertOk()->assertJsonPath('statut', 'ECHOUE');
        $this->assertSame(3, $depart->fresh()->places_disponibles);
    }

    public function test_paiement_recu_apres_expiration_reactive_le_billet_s_il_reste_de_la_place(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 1]);
        $this->reservations->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-TARD');
        DB::table('paiements_mtn_momo')->insert([
            'reference_id' => 'REF-TARD', 'amount' => 7000, 'currency' => 'XAF', 'payer_msisdn' => '061234567',
            'status' => 'PENDING', 'environment' => 'sandbox', 'simulation' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        Ticket::query()->update(['expire_le' => now()->subMinute()]);
        $this->reservations->expirerReservations();

        $this->mtnReel('SUCCESSFUL');
        $etat = $this->paiements->actualiser('REF-TARD');

        $this->assertSame('PAYE', $etat['statut']);
        $this->assertSame(0, $depart->fresh()->places_disponibles);
    }

    public function test_paiement_recu_apres_expiration_sans_place_est_signale_a_rembourser(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 1]);
        $this->reservations->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-TARD');
        DB::table('paiements_mtn_momo')->insert([
            'reference_id' => 'REF-TARD', 'amount' => 7000, 'currency' => 'XAF', 'payer_msisdn' => '061234567',
            'status' => 'PENDING', 'environment' => 'sandbox', 'simulation' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        Ticket::query()->update(['expire_le' => now()->subMinute()]);
        $this->reservations->expirerReservations();
        // La place est revendue à quelqu'un d'autre entre-temps
        $this->reservations->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-AUTRE', payeImmediatement: true);

        $this->mtnReel('SUCCESSFUL');
        $etat = $this->paiements->actualiser('REF-TARD');

        $this->assertSame('A_REMBOURSER', $etat['statut']);
        $this->assertSame(1, Ticket::where('statut_paiement', 'Paye')->count(), 'Pas de surréservation.');
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'paiement.a_rembourser')->count());
    }

    public function test_un_client_ne_peut_pas_voir_le_paiement_d_un_autre(): void
    {
        $this->mtnReel('PENDING');
        $depart = $this->creerDepart();
        Sanctum::actingAs($this->client());
        $ref = $this->payerMtn($depart->id)->json('reference_id');

        Sanctum::actingAs($this->client());
        $this->getJson("/api/paiements/{$ref}/statut")->assertNotFound();
        $this->postJson("/api/paiements/{$ref}/annuler")->assertNotFound();
    }

    public function test_le_client_peut_annuler_une_reservation_en_attente(): void
    {
        $this->mtnReel('PENDING');
        $depart = $this->creerDepart();
        Sanctum::actingAs($this->client());
        $ref = $this->payerMtn($depart->id, 2)->json('reference_id');

        $this->postJson("/api/paiements/{$ref}/annuler")->assertOk()->assertJsonPath('statut', 'ECHOUE');
        $this->assertSame(3, $depart->fresh()->places_disponibles);
    }

    public function test_le_callback_mtn_ne_croit_pas_le_message_sur_parole(): void
    {
        $this->mtnReel('PENDING');
        $depart = $this->creerDepart();
        Sanctum::actingAs($this->client());
        $ref = $this->payerMtn($depart->id)->json('reference_id');

        // Le message prétend un succès, mais l'API MTN dit « en attente »
        $this->withHeaders(['X-Reference-Id' => $ref])
            ->postJson('/api/paiements/mtn/callback', ['status' => 'SUCCESSFUL'])->assertOk();
        $this->assertSame('En_Attente', Ticket::value('statut_paiement'));

        // L'API MTN confirme : le callback valide alors le paiement
        $this->mtnReel('SUCCESSFUL');
        $this->withHeaders(['X-Reference-Id' => $ref])
            ->postJson('/api/paiements/mtn/callback', ['status' => 'SUCCESSFUL'])->assertOk();
        $this->assertSame('Paye', Ticket::value('statut_paiement'));
    }

    public function test_la_meme_demande_envoyee_deux_fois_ne_cree_qu_un_paiement(): void
    {
        $this->mtnReel('PENDING');
        $depart = $this->creerDepart();
        Sanctum::actingAs($this->client());

        $r1 = $this->payerMtn($depart->id, 1, ['Idempotency-Key' => 'cle-123'])->assertOk()->json('reference_id');
        $r2 = $this->payerMtn($depart->id, 1, ['Idempotency-Key' => 'cle-123'])->assertOk()->json('reference_id');

        $this->assertSame($r1, $r2);
        $this->assertSame(1, Ticket::count());
        $this->assertSame(1, DB::table('paiements_mtn_momo')->count());
    }

    // ==================================================================
    // Mode test et production
    // ==================================================================

    public function test_aucune_simulation_en_production_sans_mode_test_explicite(): void
    {
        $this->app['env'] = 'production';
        config(['momo.simulation' => true, 'paiement.mode_test_production' => false,
                'momo.collection.api_user' => '', 'momo.collection.api_key' => '']);
        $depart = $this->creerDepart();
        Sanctum::actingAs($this->client());

        $this->payerMtn($depart->id)->assertStatus(503);
        $this->assertSame(0, Ticket::count());
    }

    public function test_mode_test_en_production_donne_des_billets_marques_test_et_refuses_a_l_embarquement(): void
    {
        $this->app['env'] = 'production';
        config(['momo.simulation' => true, 'paiement.mode_test_production' => true]);
        $depart = $this->creerDepart();
        Sanctum::actingAs($this->client());

        $this->payerMtn($depart->id)->assertOk()->assertJsonPath('simulation', true);
        $ticket = Ticket::first();
        $this->assertTrue(PaiementService::estReferenceTest($ticket->reference_paiement));

        // Mode test retiré : le billet de test ne permet pas d'embarquer
        config(['paiement.mode_test_production' => false]);
        Sanctum::actingAs($this->personnel('Agent', $depart->agence_id));
        $this->postJson('/api/agent/update-presence', ['ticket_id' => $ticket->id, 'present' => true])
            ->assertStatus(409)->assertJsonPath('code', 'BILLET_TEST');
    }

    // ==================================================================
    // Contrôle des billets
    // ==================================================================

    public function test_un_billet_non_paye_est_refuse_a_l_embarquement(): void
    {
        $depart = $this->creerDepart();
        $ticket = $this->reservations->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-NP')->first();
        Sanctum::actingAs($this->personnel('Agent', $depart->agence_id));

        $this->getJson('/api/agent/ticket/' . $ticket->reference_ticket)
            ->assertOk()->assertJsonPath('ticket.valide_pour_embarquement', false);
        $this->postJson('/api/agent/update-presence', ['ticket_id' => $ticket->id, 'present' => true])
            ->assertStatus(409)->assertJsonPath('code', 'NON_PAYE');

        $this->assertSame('En_Attente', $ticket->fresh()->statut_embarquement);
        $this->assertSame(1, DB::table('ticket_validations')->where('resultat', 'NON_PAYE')->count());
    }

    public function test_un_billet_paye_se_valide_par_son_qr_puis_est_signale_deja_utilise(): void
    {
        $depart = $this->creerDepart();
        $ticket = $this->reservations->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-OK', payeImmediatement: true)->first();
        Sanctum::actingAs($this->personnel('Agent', $depart->agence_id));

        $this->getJson('/api/agent/ticket/' . urlencode('MBL:' . $ticket->qr_code_token))
            ->assertOk()->assertJsonPath('ticket.reference', $ticket->reference_ticket)
            ->assertJsonPath('ticket.valide_pour_embarquement', true);

        $this->postJson('/api/agent/update-presence', ['ticket_id' => $ticket->id, 'present' => true])
            ->assertOk()->assertJsonPath('deja_present', false);
        $this->postJson('/api/agent/update-presence', ['ticket_id' => $ticket->id, 'present' => true])
            ->assertOk()->assertJsonPath('deja_present', true);

        $this->assertSame(['DEJA_UTILISE', 'VALIDE'],
            DB::table('ticket_validations')->orderBy('resultat')->pluck('resultat')->all());
    }

    public function test_un_agent_ne_controle_pas_les_billets_d_une_autre_agence_ni_un_client(): void
    {
        $depart = $this->creerDepart();
        $autre  = $this->creerDepart();
        $ticket = $this->reservations->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-X', payeImmediatement: true)->first();

        Sanctum::actingAs($this->personnel('Agent', $autre->agence_id));
        $this->getJson('/api/agent/ticket/' . $ticket->reference_ticket)->assertNotFound();
        $this->postJson('/api/agent/update-presence', ['ticket_id' => $ticket->id, 'present' => true])->assertForbidden();

        Sanctum::actingAs($this->client());
        $this->getJson('/api/agent/voyageurs')->assertForbidden();
        $this->postJson('/api/scan-ticket', ['reference_ticket' => $ticket->reference_ticket])->assertForbidden();
        $this->postJson('/api/update-embarquement', ['reference_ticket' => $ticket->reference_ticket, 'statut_embarquement' => 'Present'])
            ->assertForbidden();
    }

    public function test_les_references_et_jetons_de_billets_sont_uniques(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 40]);
        $this->reservations->reserver($depart->id, $this->passagers(10), 'MTN', 'REF-U1', payeImmediatement: true);
        $this->reservations->reserver($depart->id, $this->passagers(10), 'MTN', 'REF-U2', payeImmediatement: true);

        $this->assertSame(20, Ticket::distinct()->count('reference_ticket'));
        $this->assertSame(20, Ticket::distinct()->count('qr_code_token'));
    }

    // ==================================================================
    // Administration web : rôles
    // ==================================================================

    public function test_un_agent_n_accede_pas_a_la_gestion_de_l_agence(): void
    {
        $depart = $this->creerDepart();
        $agent  = $this->personnel('Agent', $depart->agence_id);
        $admin  = $this->personnel('Admin', $depart->agence_id);

        $this->actingAs($agent, 'web');
        $this->get('/admin/bus')->assertForbidden();
        $this->get('/admin/agents')->assertForbidden();
        $this->get('/admin/planifications/create')->assertForbidden();
        $this->get('/admin/planifications')->assertOk();

        $this->actingAs($admin, 'web');
        $this->get('/admin/bus')->assertOk();
        $this->get('/admin/planifications/create')->assertOk();
    }

    public function test_l_admin_peut_marquer_un_billet_rembourse(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 1]);
        $ticket = $this->reservations->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-R', payeImmediatement: true)->first();
        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');

        $this->post("/admin/voyageurs/{$ticket->id}/rembourser")->assertRedirect();

        $this->assertSame('Rembourse', $ticket->fresh()->statut_paiement);
        $this->assertSame(1, $depart->fresh()->places_disponibles);
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'billet.rembourse')->count());
    }

    public function test_la_connexion_admin_est_limitee_apres_plusieurs_echecs(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['identifiant' => '060000000', 'password' => 'mauvais'])->assertStatus(302);
        }
        $this->post('/login', ['identifiant' => '060000000', 'password' => 'mauvais'])->assertStatus(429);
        $this->assertSame(5, DB::table('audit_logs')->where('action', 'connexion.echouee')->count());
    }

    // ==================================================================
    // Recherche et billets du client
    // ==================================================================

    public function test_la_recherche_ne_renvoie_que_les_departs_reservables(): void
    {
        $date = now()->addDays(3)->toDateString();
        $ok      = $this->creerDepart(['nombre_tickets_total' => 5]);
        $complet = $this->creerDepart(['nombre_tickets_total' => 1, 'places_disponibles' => 0]);
        $annule  = $this->creerDepart(['statut_voyage' => 'Annulé']);
        $bloque  = $this->creerDepart();
        DB::table('agences')->where('id', $bloque->agence_id)->update(['statut' => 'Bloqué']);

        $r = $this->getJson("/api/voyages/recherche?ville_depart=brazzaville&ville_arrivee=Nkayi&date={$date}&passagers=2")
            ->assertOk()->assertJsonPath('nombre', 1);
        $this->assertSame($ok->id, $r->json('voyages.0.planification_id'));
        $this->assertSame(7000.0, (float) $r->json('voyages.0.prix'));
        $this->assertSame('07:30', $r->json('voyages.0.heure_depart'));

        $this->getJson("/api/voyages/recherche?ville_depart=Brazzaville&ville_arrivee=Brazzaville&date={$date}")->assertStatus(422);
        $this->getJson('/api/voyages/recherche?ville_depart=Brazzaville&ville_arrivee=Nkayi&date=' . now()->subDay()->toDateString())->assertStatus(422);
    }

    public function test_mes_billets_ne_donne_le_qr_que_pour_un_billet_paye(): void
    {
        $depart = $this->creerDepart();
        $client = $this->client();
        $this->reservations->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-A', utilisateurId: $client->id);
        $this->reservations->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-B', payeImmediatement: true, utilisateurId: $client->id);
        Sanctum::actingAs($client);

        $billets = collect($this->getJson('/api/mes-billets')->assertOk()->json('billets'))->keyBy('reference_paiement');

        $this->assertNull($billets['REF-A']['qr']);
        $this->assertSame('EN_ATTENTE_PAIEMENT', $billets['REF-A']['statut']);
        $this->assertStringStartsWith('MBL:', $billets['REF-B']['qr']);
        $this->assertSame('CONFIRME', $billets['REF-B']['statut']);

        // Un autre client ne voit rien
        Sanctum::actingAs($this->client());
        $this->getJson('/api/mes-billets')->assertOk()->assertJsonCount(0, 'billets');
    }
}
