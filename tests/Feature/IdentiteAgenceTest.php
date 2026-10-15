<?php

namespace Tests\Feature;

use App\Models\Agence;
use App\Models\Bus;
use App\Models\Media;
use App\Models\SuperAdmin;
use App\Support\Couleurs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\DonneesBus;
use Tests\TestCase;

/**
 * Identité visuelle des partenaires (couleurs, logo) et pagination de
 * l'administration.
 */
class IdentiteAgenceTest extends TestCase
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

    public function test_le_partenaire_est_inscrit_avec_ses_couleurs_et_son_logo(): void
    {
        $this->actingAs($this->superAdmin(), 'superadmin');

        $this->post('/superadmin/agences', [
            'nom_agence' => 'Congo Express', 'date_partenariat' => now()->toDateString(),
            'admin_telephone' => '061234511', 'admin_nom' => 'MBEMBA', 'admin_prenom' => 'Paul', 'admin_password' => 'Secret2026!',
            'couleur' => '#6a1b9a', 'couleur_secondaire' => '#ffc107',
            'logo' => UploadedFile::fake()->image('logo.png', 300, 300),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $agence = Agence::where('nom_agence', 'Congo Express')->firstOrFail();
        $this->assertSame('#6A1B9A', $agence->couleur);
        $this->assertSame('#FFC107', $agence->couleur_secondaire);
        $this->assertNotNull($agence->logo_media_id);

        // L'application reçoit les deux couleurs et le logo
        $vitrine = collect($this->getJson('/api/agences')->json('agences'))->firstWhere('id', $agence->id)['vitrine'];
        $this->assertSame('#FFC107', $vitrine['couleur_secondaire']);
        $this->assertNotNull($vitrine['logo_url']);
    }

    public function test_une_couleur_invalide_est_refusee(): void
    {
        $this->actingAs($this->superAdmin(), 'superadmin');

        $this->post('/superadmin/agences', [
            'nom_agence' => 'Agence X', 'date_partenariat' => now()->toDateString(),
            'admin_telephone' => '061234512', 'admin_nom' => 'A', 'admin_prenom' => 'B', 'admin_password' => 'Secret2026!',
            'couleur' => 'rouge',
        ])->assertSessionHasErrors('couleur');
        $this->assertFalse(Agence::where('nom_agence', 'Agence X')->exists());
    }

    public function test_le_super_admin_change_l_identite_d_une_agence(): void
    {
        $depart = $this->creerDepart();
        $this->actingAs($this->superAdmin(), 'superadmin');

        $this->post("/superadmin/agences/{$depart->agence_id}/identite", [
            'couleur' => '#0D47A1', 'couleur_secondaire' => '#FF9800',
            'logo' => UploadedFile::fake()->image('l.png', 200, 200),
        ])->assertSessionHasNoErrors();
        $this->post("/superadmin/agences/{$depart->agence_id}/identite", [
            'couleur' => '#0D47A1', 'logo' => UploadedFile::fake()->image('l2.png', 200, 200),
        ])->assertSessionHasNoErrors();

        $agence = Agence::findOrFail($depart->agence_id);
        $this->assertSame('#0D47A1', $agence->couleur);
        $this->assertSame(1, Media::where('agence_id', $agence->id)->where('type', 'logo')->count(), 'L\'ancien logo est remplacé.');
    }

    public function test_l_administration_de_l_agence_prend_ses_couleurs_et_son_logo(): void
    {
        $depart = $this->creerDepart();
        DB::table('agences')->where('id', $depart->agence_id)->update(['couleur' => '#F9A825', 'couleur_secondaire' => '#1B5E20']);
        $agence = Agence::findOrFail($depart->agence_id);
        $agence->remplacerLogo(UploadedFile::fake()->image('logo.png', 100, 100));

        $this->actingAs($this->personnel('Admin', $agence->id), 'web');
        $page = $this->get('/admin/bus')->assertOk();

        $fond = $agence->fresh()->identite()['fond'];
        $page->assertSee("--agence-primaire: {$fond}", false)
             ->assertSee($agence->fresh()->logoMedia->url(), false);
        // Jaune trop clair pour un texte blanc : foncé automatiquement
        $this->assertNotSame('#F9A825', $fond);
        $this->assertGreaterThanOrEqual(3.6, Couleurs::contrasteBlanc($fond));
    }

    public function test_les_listes_de_l_administration_sont_paginees_par_5(): void
    {
        $depart = $this->creerDepart();
        foreach (range(1, 6) as $i) {
            Bus::create(['agence_id' => $depart->agence_id, 'immatriculation' => "BZ-00{$i}-PG", 'modele' => 'Test', 'capacite_totale' => 30, 'statut' => 'Disponible']);
        }
        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');

        // 7 bus (1 du départ + 6) : 5 en page 1, 2 en page 2, compteurs sur le total
        $this->get('/admin/bus')->assertOk()->assertSee('sur <span class="font-extrabold text-slate-800 dark:text-slate-100">7</span>', false);
        $this->assertCount(2, $this->get('/admin/bus?page=2')->assertOk()->viewData('bus'));

        foreach (['/admin/agents', '/admin/trajets', '/admin/gares', '/admin/vagues', '/admin/voyageurs', '/admin/planifications', '/admin/historique'] as $page) {
            $this->get($page)->assertOk();
        }

        $this->actingAs($this->superAdmin(), 'superadmin');
        foreach (['/superadmin/agences', '/superadmin/villes', '/superadmin/journal', "/superadmin/agences/{$depart->agence_id}"] as $page) {
            $this->get($page)->assertOk();
        }
    }
}
