<?php

namespace Tests\Unit;

use App\Services\TwilioVerifyService;
use Tests\TestCase;

/** Personnalisation du SMS du code de vérification. */
class TwilioMessageTest extends TestCase
{
    public function test_le_sms_est_en_francais_au_nom_de_mobembo_line(): void
    {
        config(['services.twilio.sid' => null, 'services.twilio.verify_template_sid' => null]);

        $this->assertSame(['locale' => 'fr', 'customFriendlyName' => 'Mobembo Line'], (new TwilioVerifyService)->optionsSms());
    }

    public function test_un_modele_personnalise_est_utilise_s_il_est_configure(): void
    {
        config(['services.twilio.sid' => null, 'services.twilio.verify_template_sid' => 'HJ123']);

        $this->assertSame('HJ123', (new TwilioVerifyService)->optionsSms()['templateSid']);
    }
}
