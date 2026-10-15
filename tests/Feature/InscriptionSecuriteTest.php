<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use App\Services\TwilioVerifyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\DonneesBus;
use Tests\TestCase;

/**
 * Inscription, confirmation du numéro et réinitialisation du mot de passe.
 */
class InscriptionSecuriteTest extends TestCase
{
    use RefreshDatabase, DonneesBus;

    protected function setUp(): void
    {
        parent::setUp();
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('À lancer sur MySQL/MariaDB.');
        }
        // Twilio simulé : le code « 123456 » est le bon
        $this->mock(TwilioVerifyService::class, function ($m) {
            $m->shouldReceive('sendCode')->andReturn(['success' => true, 'status' => 'pending']);
            $m->shouldReceive('checkCode')->andReturnUsing(fn ($tel, $code) => ['success' => $code === '123456', 'status' => $code === '123456' ? 'approved' : 'pending']);
        });
    }

    private function inscrire(array $surcharge = [])
    {
        return $this->postJson('/api/register', array_merge([
            'nom' => 'MBEMBA', 'prenom' => 'Paul', 'telephone' => '051234567',
            'mot_de_passe' => 'Secret2026', 'mot_de_passe_confirmation' => 'Secret2026',
        ], $surcharge));
    }

    public function test_l_inscription_publique_ne_cree_jamais_un_administrateur(): void
    {
        $depart = $this->creerDepart();

        $this->inscrire(['role' => 'Admin', 'agence_id' => $depart->agence_id])->assertCreated()->assertJsonMissingPath('token');

        $u = Utilisateur::where('telephone', '051234567')->firstOrFail();
        $this->assertSame('Client', $u->role);
        $this->assertNull($u->agence_id);
    }

    public function test_une_confirmation_differente_n_enregistre_pas_le_compte(): void
    {
        $this->inscrire(['mot_de_passe_confirmation' => 'Autre2026'])->assertStatus(422);
        $this->assertFalse(Utilisateur::where('telephone', '051234567')->exists());
    }

    public function test_il_faut_confirmer_son_numero_avant_de_se_connecter(): void
    {
        $this->inscrire()->assertCreated();
        $connexion = ['telephone' => '051234567', 'mot_de_passe' => 'Secret2026'];

        $this->postJson('/api/login', $connexion)->assertStatus(403)->assertJsonPath('code', 'COMPTE_NON_CONFIRME');

        $this->postJson('/api/verify-otp', ['telephone' => '051234567', 'code' => '000000'])->assertStatus(400);
        // Code bon : compte confirmé et client connecté directement
        $this->postJson('/api/verify-otp', ['telephone' => '051234567', 'code' => '123456'])->assertOk()->assertJsonStructure(['token']);

        $this->postJson('/api/login', $connexion)->assertOk()->assertJsonStructure(['token']);
    }

    public function test_une_inscription_non_confirmee_ne_bloque_pas_le_numero(): void
    {
        $this->inscrire()->assertCreated();
        // L'utilisateur recommence avec un autre mot de passe : accepté
        $this->inscrire(['mot_de_passe' => 'Nouveau2026', 'mot_de_passe_confirmation' => 'Nouveau2026'])->assertCreated();
        $this->assertSame(1, Utilisateur::where('telephone', '051234567')->count());

        // Une fois confirmé, le numéro est pris
        $this->postJson('/api/verify-otp', ['telephone' => '051234567', 'code' => '123456'])->assertOk();
        $this->inscrire()->assertStatus(422);
        $this->assertTrue(Hash::check('Nouveau2026', Utilisateur::where('telephone', '051234567')->value('mot_de_passe')));
    }

    public function test_le_numero_d_un_agent_ne_peut_pas_etre_repris(): void
    {
        $depart = $this->creerDepart();
        $agent = $this->personnel('Agent', $depart->agence_id);

        $this->inscrire(['telephone' => $agent->telephone])->assertStatus(422);
        $this->assertSame('Agent', $agent->fresh()->role);
    }

    public function test_changer_un_mot_de_passe_exige_le_code_recu(): void
    {
        $depart = $this->creerDepart();
        $admin = $this->personnel('Admin', $depart->agence_id);
        $admin->update(['telephone' => '055555555']);
        $tel = $admin->telephone;

        // Sans preuve : refusé
        $this->postJson('/api/reset-password', ['telephone' => $tel, 'nouveau_mot_de_passe' => 'Pirate2026'])->assertStatus(422);
        $this->postJson('/api/reset-password', ['telephone' => $tel, 'jeton' => 'invente', 'nouveau_mot_de_passe' => 'Pirate2026'])->assertStatus(403);
        $this->assertTrue(Hash::check('Secret123', $admin->fresh()->mot_de_passe));

        // Avec le code reçu : le jeton permet le changement, une seule fois
        $jeton = $this->postJson('/api/verify-otp', ['telephone' => $tel, 'code' => '123456', 'type_usage' => 'RESET_PASSWORD'])
            ->assertOk()->json('jeton_reinitialisation');
        $this->postJson('/api/reset-password', ['telephone' => $tel, 'jeton' => $jeton, 'mot_de_passe' => 'Nouveau2026'])->assertOk();
        $this->assertTrue(Hash::check('Nouveau2026', $admin->fresh()->mot_de_passe));
        $this->postJson('/api/reset-password', ['telephone' => $tel, 'jeton' => $jeton, 'nouveau_mot_de_passe' => 'Encore2026'])->assertStatus(403);
    }

    public function test_les_anciens_comptes_non_confirmes_ne_sont_pas_bloques(): void
    {
        $u = $this->client();
        DB::table('utilisateurs')->where('id', $u->id)->update(['compte_confirme' => false, 'created_at' => '2026-10-01 10:00:00']);

        $this->postJson('/api/login', ['telephone' => $u->telephone, 'mot_de_passe' => 'Secret123'])->assertOk();
    }
}
