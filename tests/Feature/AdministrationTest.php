<?php

namespace Tests\Feature;

use App\Exceptions\ReservationImpossibleException;
use App\Models\SuperAdmin;
use App\Models\Utilisateur;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\Support\DonneesBus;
use Tests\TestCase;

/**
 * Espace super-admin (vue nationale, agences, admins) et sécurité des
 * comptes de l'administration d'agence.
 */
class AdministrationTest extends TestCase
{
    use RefreshDatabase, DonneesBus;

    protected function setUp(): void
    {
        parent::setUp();
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('À lancer sur MySQL/MariaDB.');
        }
    }

    private function superAdmin(): SuperAdmin
    {
        $sa = new SuperAdmin(['nom' => 'National', 'email' => uniqid('sa') . '@mobembo.cg', 'mot_de_passe' => Hash::make('x')]);
        $sa->save();

        return $sa;
    }

    public function test_les_pages_du_super_admin_sont_reservees_au_super_admin(): void
    {
        $depart = $this->creerDepart();

        $this->get('/superadmin/tableau-de-bord')->assertRedirect();   // invité → connexion
        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');
        $this->get('/superadmin/tableau-de-bord')->assertRedirect();   // un admin d'agence n'y accède pas

        $this->actingAs($this->superAdmin(), 'superadmin');
        foreach (['/superadmin/tableau-de-bord', '/superadmin/journal', '/superadmin/agences', '/superadmin/villes', "/superadmin/agences/{$depart->agence_id}"] as $page) {
            $this->get($page)->assertOk()->assertSee("Journal d'audit", false);   // menu commun du layout
        }
    }

    public function test_le_tableau_de_bord_exclut_les_billets_de_test(): void
    {
        $depart = $this->creerDepart();
        $service = app(ReservationService::class);
        $service->reserver($depart->id, $this->passagers(1), 'MTN', 'REF-VRAI', payeImmediatement: true);
        $service->reserver($depart->id, $this->passagers(1), 'MTN', 'SIM-TEST01', payeImmediatement: true);

        $this->actingAs($this->superAdmin(), 'superadmin');
        $this->get('/superadmin/tableau-de-bord')->assertOk()
            ->assertSee('7 000 FCFA')          // une seule vente réelle
            ->assertSee('1 billet(s) de TEST', false);
    }

    public function test_creation_d_agence_avec_son_admin(): void
    {
        $this->actingAs($this->superAdmin(), 'superadmin');

        $this->post('/superadmin/agences', [
            'nom_agence' => 'Nouvelle Agence', 'date_partenariat' => now()->toDateString(),
            'admin_telephone' => '061234500', 'admin_nom' => 'MBEMBA', 'admin_prenom' => 'Paul', 'admin_password' => 'Secret2026!',
        ])->assertRedirect();

        $agence = DB::table('agences')->where('nom_agence', 'Nouvelle Agence')->first();
        $admin = Utilisateur::where('telephone', '061234500')->first();
        $this->assertSame((int) $agence->id, (int) $admin->agence_id);
        $this->assertSame('Admin', $admin->role);
        $this->assertSame('actif', $admin->statut);
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'superadmin.agence.creee')->count());
    }

    public function test_reglages_et_comptes_admin_d_une_agence(): void
    {
        $depart = $this->creerDepart();
        $agenceId = $depart->agence_id;
        $this->actingAs($this->superAdmin(), 'superadmin');

        // Réglages
        $nom = DB::table('agences')->where('id', $agenceId)->value('nom_agence');
        $this->put("/superadmin/agences/{$agenceId}", [
            'nom_agence' => $nom, 'date_partenariat' => '2026-01-15', 'age_max_enfant' => 10, 'choix_siege' => 1,
        ])->assertRedirect();
        $agence = DB::table('agences')->where('id', $agenceId)->first();
        $this->assertSame(10, (int) $agence->age_max_enfant);
        $this->assertTrue((bool) $agence->choix_siege);

        // Nouvel admin : mot de passe temporaire affiché une fois et valide
        $r = $this->post("/superadmin/agences/{$agenceId}/admins", ['nom' => 'NGOMA', 'prenom' => 'Ruth', 'telephone' => '051234567']);
        $r->assertSessionHas('mot_de_passe_temporaire');
        $mdp = session('mot_de_passe_temporaire')['mot_de_passe'];
        $admin = Utilisateur::where('telephone', '051234567')->first();
        $this->assertTrue(Hash::check($mdp, $admin->mot_de_passe));

        // Réinitialisation
        $this->post("/superadmin/agences/{$agenceId}/admins/{$admin->id}/mot-de-passe")->assertSessionHas('mot_de_passe_temporaire');
        $this->assertFalse(Hash::check($mdp, $admin->fresh()->mot_de_passe));

        // Suspension
        $this->patch("/superadmin/agences/{$agenceId}/admins/{$admin->id}/statut")->assertRedirect();
        $this->assertSame('suspendu', $admin->fresh()->statut);

        // Un admin d'une autre agence ne peut pas être géré via cette agence
        $autre = $this->creerDepart();
        $autreAdmin = $this->personnel('Admin', $autre->agence_id);
        $this->post("/superadmin/agences/{$agenceId}/admins/{$autreAdmin->id}/mot-de-passe")->assertNotFound();

        $this->assertGreaterThanOrEqual(4, DB::table('audit_logs')->where('action', 'like', 'superadmin.%')->count());
    }

    public function test_une_agence_bloquee_ne_vend_plus_et_ses_agents_ne_scannent_plus(): void
    {
        $depart = $this->creerDepart();
        $agent = $this->personnel('Agent', $depart->agence_id);
        $this->actingAs($this->superAdmin(), 'superadmin');
        $this->patch("/superadmin/agences/{$depart->agence_id}/toggle-status")->assertRedirect();
        $this->assertSame('Bloqué', DB::table('agences')->where('id', $depart->agence_id)->value('statut'));

        try {
            app(ReservationService::class)->reserver($depart->id, $this->passagers(1), 'MTN', 'R');
            $this->fail('Vente acceptée pour une agence bloquée.');
        } catch (ReservationImpossibleException $e) {
            $this->assertSame(403, $e->statutHttp());
        }

        Sanctum::actingAs($agent);
        $this->getJson('/api/agent/voyageurs')->assertForbidden();
    }

    public function test_un_agent_suspendu_ne_scanne_plus(): void
    {
        $depart = $this->creerDepart();
        $agent = $this->personnel('Agent', $depart->agence_id);
        $agent->update(['statut' => 'suspendu']);

        Sanctum::actingAs($agent);
        $this->getJson('/api/agent/voyageurs')->assertForbidden();
        $this->getJson('/api/agent/ticket/TKT-X')->assertForbidden();
    }

    public function test_les_agents_recoivent_un_mot_de_passe_unique_et_l_admin_peut_le_renouveler(): void
    {
        $depart = $this->creerDepart();
        $gare = DB::table('gares')->where('agence_id', $depart->agence_id)->value('id');
        $admin = $this->personnel('Admin', $depart->agence_id);
        $this->actingAs($admin, 'web');

        $this->post('/admin/agents', ['nom' => 'BOUKA', 'prenom' => 'Eli', 'telephone' => '069876543', 'gare_id' => $gare])
            ->assertSessionHas('mot_de_passe_temporaire');
        $mdp = session('mot_de_passe_temporaire')['mot_de_passe'];
        $agent = Utilisateur::where('telephone', '069876543')->first();

        $this->assertNotSame('Mobembo2026', $mdp);
        $this->assertFalse(Hash::check('Mobembo2026', $agent->mot_de_passe), 'Plus de mot de passe commun.');
        $this->assertTrue(Hash::check($mdp, $agent->mot_de_passe));

        $this->post("/admin/agents/{$agent->id}/mot-de-passe")->assertSessionHas('mot_de_passe_temporaire');
        $this->assertFalse(Hash::check($mdp, $agent->fresh()->mot_de_passe));

        // Un admin d'une autre agence ne peut pas toucher cet agent
        $autre = $this->creerDepart();
        $this->actingAs($this->personnel('Admin', $autre->agence_id), 'web');
        $this->post("/admin/agents/{$agent->id}/mot-de-passe")->assertForbidden();
    }
}
