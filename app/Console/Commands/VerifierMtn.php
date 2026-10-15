<?php

namespace App\Console\Commands;

use App\Services\PaiementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Vérifie les accès MTN MoMo (collection) sans encaisser : demande un jeton
 * puis lit le solde du compte. Aucune clé n'est affichée.
 *
 *   php artisan mtn:verifier
 */
class VerifierMtn extends Command
{
    protected $signature = 'mtn:verifier';

    protected $description = 'Vérifie les accès MTN MoMo (jeton + solde), sans paiement';

    public function handle(PaiementService $paiements): int
    {
        $env = (string) config('momo.collection.target_environment', 'sandbox');
        $urls = config('momo.urls.' . ($env === 'sandbox' ? 'sandbox' : 'production'));

        $this->line("Environnement MTN : {$env}");
        $this->line('Adresse          : ' . $urls['base']);
        $this->line('Simulation       : ' . (filter_var(config('momo.simulation'), FILTER_VALIDATE_BOOLEAN) ? 'OUI (aucun vrai paiement)' : 'non'));
        $this->line('Callback         : ' . (config('momo.callback_url') ?: '(non configuré)'));

        foreach (['api_user' => 'API user', 'api_key' => 'API key', 'primary_key' => 'Clé primaire'] as $cle => $nom) {
            if (blank(config("momo.collection.{$cle}"))) {
                $this->error("{$nom} manquant (MTN_COLLECTION_" . strtoupper($cle) . ').');

                return self::FAILURE;
            }
        }

        $jeton = $paiements->jetonMtn();
        if (! $jeton) {
            $this->error('Jeton refusé par MTN : clés incorrectes, ou clés de production utilisées sur le bac à sable (ou l\'inverse). Détail dans storage/logs.');

            return self::FAILURE;
        }
        $this->info('✔ Jeton obtenu : les clés sont acceptées.');

        $r = Http::timeout(20)->withHeaders([
            'Authorization'             => 'Bearer ' . $jeton,
            'X-Target-Environment'      => $env,
            'Ocp-Apim-Subscription-Key' => config('momo.collection.primary_key'),
        ])->get($urls['base'] . '/collection/v1_0/account/balance');

        if ($r->successful()) {
            $this->info('✔ Environnement « ' . $env . ' » accepté. Solde : ' . $r->json('availableBalance') . ' ' . $r->json('currency'));

            return self::SUCCESS;
        }

        $this->error("✘ Lecture du solde refusée (HTTP {$r->status()}) : " . mb_substr($r->body(), 0, 300));
        $this->line('Si MTN parle d\'environnement inconnu, vérifiez MTN_TARGET_ENV (pour le Congo : mtncongo).');

        return self::FAILURE;
    }
}
