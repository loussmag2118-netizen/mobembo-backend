<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bus';

    protected $fillable = [
        'agence_id',
        'immatriculation',
        'modele',
        'capacite_totale',
        'disposition_sieges', // ex. « 2-2 » : sièges à gauche et à droite du couloir
        'statut', // ⭐ NOUVEAU
    ];

    protected $casts = [
        'capacite_totale' => 'integer',
        'agence_id'       => 'integer',
        'deleted_at'      => 'datetime',
    ];

    /**
     * Valeurs possibles du statut (utile pour les dropdowns).
     */
    public const STATUTS = [
        'Disponible'   => 'Disponible',
        'Maintenance'  => 'Maintenance',
        'Indisponible' => 'Indisponible',
    ];

    // ══════════════════════════════════════════════════════════════
    // RELATIONS
    // ══════════════════════════════════════════════════════════════

    /**
     * Relation : Un bus appartient à une agence.
     */
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    /**
     * Relation : Un bus peut être utilisé dans plusieurs planifications.
     */
    public function planifications()
    {
        return $this->hasMany(PlanificationVoyage::class, 'bus_id');
    }

    /**
     * Relation : Un bus peut être rattaché à plusieurs trajets.
     * ⚠️ À activer SEULEMENT si tu crées la table pivot `bus_trajet`.
     * Décommente la ligne ci-dessous après avoir créé la migration.
     */
    // public function trajets()
    // {
    //     return $this->belongsToMany(Trajet::class, 'bus_trajet', 'bus_id', 'trajet_id')
    //                 ->withTimestamps();
    // }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    /**
     * Scope : uniquement les bus disponibles (statut = Disponible).
     */
    public function scopeDisponibles($query)
    {
        return $query->where('statut', 'Disponible');
    }

    /**
     * Scope : filtrer par agence.
     */
    public function scopePourAgence($query, $agenceId)
    {
        return $query->where('agence_id', $agenceId);
    }

    // ══════════════════════════════════════════════════════════════
    // ACCESSEURS
    // ══════════════════════════════════════════════════════════════

    /**
     * Libellé complet pour affichage : "123-AB-45 — Toyota Coaster (30 places)"
     */
    public function getLibelleCompletAttribute(): string
    {
        $immat  = $this->immatriculation ?? 'Bus #'.$this->id;
        $modele = $this->modele ? ' — '.$this->modele : '';
        $places = $this->capacite_totale ? ' ('.$this->capacite_totale.' places)' : '';

        return $immat.$modele.$places;
    }

    /**
     * Indique si le bus est opérationnel.
     */
    public function getEstDisponibleAttribute(): bool
    {
        return $this->statut === 'Disponible';
    }

    /**
     * Badge HTML de statut (pour Blade).
     */
    public function getStatutBadgeAttribute(): string
    {
        $badges = [
            'Disponible'   => ['bg-emerald-50 text-emerald-700', '✅ Disponible'],
            'Maintenance'  => ['bg-amber-50 text-amber-700',     '🔧 Maintenance'],
            'Indisponible' => ['bg-red-50 text-red-700',         '❌ Indisponible'],
        ];

        [$class, $label] = $badges[$this->statut] ?? ['bg-slate-100 text-slate-700', '• Inconnu'];

        return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold '.$class.'">'.$label.'</span>';
    }

    /** Sièges par rangée à gauche et à droite du couloir, ex. [2, 2]. */
    public function getDispositionAttribute(): array
    {
        if (preg_match('/^([1-3])-([1-3])$/', (string) $this->disposition_sieges, $m)) {
            return [(int) $m[1], (int) $m[2]];
        }

        return [2, 2];
    }
}
