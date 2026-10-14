<?php

namespace App\Observers;

use App\Support\Audit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Trace dans audit_logs les modifications faites par un compte connecté à
 * l'administration (admin, agent, super-admin). Les opérations automatiques
 * (ex. recalcul des places) ne sont pas tracées ici.
 */
class AuditObserver
{
    /** Champs jamais recopiés dans le journal */
    private const MASQUES = ['mot_de_passe', 'password', 'remember_token', 'code_acces_agent'];

    public function created(Model $modele): void
    {
        $this->tracer('cree', $modele, $this->filtrer($modele->getAttributes()));
    }

    public function updated(Model $modele): void
    {
        $changements = $this->filtrer($modele->getChanges());
        unset($changements['updated_at']);
        if ($changements === []) {
            return;
        }

        $avant = array_intersect_key($this->filtrer($modele->getOriginal()), $changements);
        $this->tracer('modifie', $modele, ['avant' => $avant, 'apres' => $changements]);
    }

    public function deleted(Model $modele): void
    {
        $this->tracer('supprime', $modele, []);
    }

    private function tracer(string $verbe, Model $modele, array $details): void
    {
        if (! Auth::guard('web')->check() && ! Auth::guard('superadmin')->check()) {
            return;
        }

        $type = strtolower(class_basename($modele));
        Audit::log("admin.{$type}.{$verbe}", $modele, $details);
    }

    private function filtrer(array $attributs): array
    {
        foreach (self::MASQUES as $champ) {
            if (array_key_exists($champ, $attributs)) {
                $attributs[$champ] = '***';
            }
        }

        return $attributs;
    }
}
