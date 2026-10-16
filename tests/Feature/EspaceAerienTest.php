<?php

namespace Tests\Feature;

use App\Models\CanadianUser;
use App\Models\SuperAdmin;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Transport aérien : inscription mobile, accès réservé aux administrateurs
 * Canadian, et suivi des inscrits par le super administrateur.
 */
class EspaceAerienTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_un_client_s_inscrit_sur_l_application_aerienne(): void
    {
        $this->postJson('/api/canadian/register', [
            'nom' => 'MOUKALA', 'prenom' => 'Grâce', 'telephone' => '061112233', 'mot_de_passe' => 'Secret2026',
        ])->assertCreated()->assertJsonStructure(['token', 'user']);

        $compte = CanadianUser::where('telephone', '061112233')->firstOrFail();
        $this->assertSame('MOUKALA', $compte->nom);
        $this->assertFalse($compte->est_admin);
    }

    public function test_un_client_aerien_n_entre_pas_dans_l_administration(): void
    {
        CanadianUser::create([
            'name' => 'Client', 'email' => 'client@canadian.local', 'telephone' => '061112244',
            'password' => Hash::make('Secret2026'),
        ]);

        $this->post('/login', ['identifiant' => '061112244', 'password' => 'Secret2026'])
            ->assertSessionHasErrors('identifiant');
        $this->assertGuest('canadian');

        // Même avec une session ouverte, les pages refusent un non-admin
        $this->actingAs(CanadianUser::first(), 'canadian')
            ->get('/canadian/admin')->assertRedirect(route('unified.login'));
    }

    public function test_le_super_admin_cree_un_administrateur_qui_peut_se_connecter(): void
    {
        $this->actingAs($this->superAdmin(), 'superadmin');

        $this->post('/superadmin/aerien/admins', [
            'nom' => 'NGOMA', 'prenom' => 'Paul', 'email' => 'paul@canadian.cg', 'telephone' => '061112255',
        ])->assertSessionHasNoErrors()->assertSessionHas('mot_de_passe_temporaire');

        $admin = CanadianUser::where('email', 'paul@canadian.cg')->firstOrFail();
        $this->assertTrue($admin->est_admin);
        $motDePasse = session('mot_de_passe_temporaire')['mot_de_passe'];

        $this->get('/superadmin/aerien')->assertOk()->assertSee('paul@canadian.cg');

        auth('superadmin')->logout();
        $this->post('/login', ['identifiant' => 'paul@canadian.cg', 'password' => $motDePasse])
            ->assertRedirect(route('canadian.admin'));
    }

    public function test_le_dernier_administrateur_ne_peut_pas_etre_retire(): void
    {
        $admin = new CanadianUser(['name' => 'Seul Admin', 'email' => 'seul@canadian.cg', 'telephone' => '061112266', 'password' => Hash::make('x')]);
        $admin->est_admin = true;
        $admin->save();

        $this->actingAs($this->superAdmin(), 'superadmin')
            ->delete("/superadmin/aerien/admins/{$admin->id}")
            ->assertSessionHasErrors('admin');
        $this->assertNotNull($admin->fresh());
    }

    public function test_le_super_admin_voit_les_inscrits_bus_et_aerien(): void
    {
        Utilisateur::create([
            'nom' => 'BOUKAKA', 'prenom' => 'Jean', 'telephone' => '051234567', 'role' => 'Client',
            'mot_de_passe' => Hash::make('Secret2026'), 'compte_confirme' => true,
        ]);
        CanadianUser::create([
            'name' => 'Aïcha MBOU', 'nom' => 'MBOU', 'prenom' => 'Aïcha', 'email' => '061112277@canadian.local',
            'telephone' => '061112277', 'password' => Hash::make('x'),
        ]);

        $this->actingAs($this->superAdmin(), 'superadmin');

        $this->get('/superadmin/utilisateurs')->assertOk()
            ->assertSee('BOUKAKA')->assertSee('Airtel')->assertDontSee('MBOU');
        $this->get('/superadmin/utilisateurs?espace=aerien')->assertOk()
            ->assertSee('MBOU')->assertDontSee('BOUKAKA');
        $this->get('/superadmin/utilisateurs?q=0512&statut=confirme')->assertOk()->assertSee('BOUKAKA');
    }
}
