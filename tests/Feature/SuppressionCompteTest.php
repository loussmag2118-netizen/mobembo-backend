<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\Utilisateur;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Support\DonneesBus;
use Tests\TestCase;

/**
 * Suppression de compte (exigence Google Play) et pages publiques.
 */
class SuppressionCompteTest extends TestCase
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

    public function test_le_client_supprime_son_compte_et_garde_ses_billets(): void
    {
        $depart = $this->creerDepart();
        $client = $this->client();
        app(ReservationService::class)->reserver($depart->id, [['nom' => 'A']], 'MTN', 'R-SUP', payeImmediatement: true, utilisateurId: $client->id);
        Sanctum::actingAs($client);

        $this->deleteJson('/api/compte', ['mot_de_passe' => 'Faux123'])->assertStatus(422);
        $this->deleteJson('/api/compte', ['mot_de_passe' => 'Secret123'])->assertOk();

        $this->assertFalse(Utilisateur::whereKey($client->id)->exists());
        $ticket = Ticket::where('reference_paiement', 'R-SUP')->firstOrFail();
        $this->assertNull($ticket->utilisateur_id);
        $this->assertSame('Paye', $ticket->statut_paiement);
    }

    public function test_un_agent_ne_supprime_pas_son_compte_depuis_l_application(): void
    {
        $depart = $this->creerDepart();
        Sanctum::actingAs($agent = $this->personnel('Agent', $depart->agence_id));

        $this->deleteJson('/api/compte', ['mot_de_passe' => 'Secret123'])->assertForbidden();
        $this->assertTrue(Utilisateur::whereKey($agent->id)->exists());
    }

    public function test_suppression_depuis_la_page_web(): void
    {
        $client = $this->client();
        $client->update(['telephone' => '061112233']);

        $this->get('/suppression-compte')->assertOk()->assertSee('Supprimer mon compte');
        $this->post('/suppression-compte', ['espace' => 'bus', 'telephone' => '061112233', 'mot_de_passe' => 'Mauvais1', 'confirmation' => '1'])
            ->assertSessionHasErrors('telephone');
        $this->post('/suppression-compte', ['espace' => 'bus', 'telephone' => '061112233', 'mot_de_passe' => 'Secret123'])
            ->assertSessionHasErrors('confirmation');
        $this->assertTrue(Utilisateur::whereKey($client->id)->exists());

        $this->post('/suppression-compte', ['espace' => 'bus', 'telephone' => '061112233', 'mot_de_passe' => 'Secret123', 'confirmation' => '1'])
            ->assertRedirect('/suppression-compte');
        $this->assertFalse(Utilisateur::whereKey($client->id)->exists());
    }

    public function test_la_politique_de_confidentialite_est_publique(): void
    {
        $this->get('/confidentialite')->assertOk()->assertSee('Politique de confidentialité')->assertSee('suppression-compte');
    }
}
