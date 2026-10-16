<?php

namespace Tests\Feature;

use App\Http\Controllers\OtpController;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Tant que les codes Airtel ne sont pas ouverts, un numéro 05 est refusé
 * avec un message clair, sans compte créé ni envoi Twilio.
 */
class AirtelIndisponibleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('À lancer sur MySQL/MariaDB.');
        }
        config(['services.twilio.airtel_actif' => false]);
    }

    public function test_inscription_airtel_refusee_avec_le_message(): void
    {
        $this->postJson('/api/register', ['nom' => 'BOUKAKA', 'telephone' => '051234567', 'mot_de_passe' => 'Secret2026'])
            ->assertStatus(422)->assertJson(['message' => OtpController::MESSAGE_AIRTEL, 'code_erreur' => 'AIRTEL_INDISPONIBLE']);
        $this->assertFalse(Utilisateur::where('telephone', '051234567')->exists());
    }

    public function test_aucun_code_envoye_vers_airtel(): void
    {
        $this->postJson('/api/send-otp', ['telephone' => '051234567'])
            ->assertStatus(422)->assertJson(['code_erreur' => 'AIRTEL_INDISPONIBLE']);
    }

    public function test_airtel_reouvert_par_le_reglage(): void
    {
        config(['services.twilio.airtel_actif' => true]);
        $this->assertFalse(OtpController::airtelIndisponible('051234567'));
        $this->assertFalse(OtpController::airtelIndisponible('061234567'));
    }
}
