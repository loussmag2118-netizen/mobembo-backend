<?php

namespace Tests\Unit;

use App\Services\TwilioVerifyService;
use Tests\TestCase;

/** Personnalisation du SMS du code de vérification. */
class TwilioMessageTest extends TestCase
{
    public function test_le_sms_est_en_francais_sans_option_refusee_par_twilio(): void
    {
        config(['services.twilio.sid' => null, 'services.twilio.verify_template_sid' => null, 'services.twilio.verify_nom' => null]);

        // Pas de nom personnalisé par défaut : le compte Twilio le refuse (60204)
        $this->assertSame(['locale' => 'fr'], (new TwilioVerifyService)->optionsSms());
    }

    public function test_le_nom_personnalise_n_est_envoye_que_s_il_est_configure(): void
    {
        config(['services.twilio.sid' => null, 'services.twilio.verify_nom' => 'Mobembo Line']);

        $this->assertSame('Mobembo Line', (new TwilioVerifyService)->optionsSms()['customFriendlyName']);
    }

    public function test_un_modele_personnalise_est_utilise_s_il_est_configure(): void
    {
        config(['services.twilio.sid' => null, 'services.twilio.verify_template_sid' => 'HJ123']);

        $this->assertSame('HJ123', (new TwilioVerifyService)->optionsSms()['templateSid']);
    }
}
