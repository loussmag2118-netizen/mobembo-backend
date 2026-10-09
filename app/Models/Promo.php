<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Promo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'promos';

    /**
     * ⭐ Colonnes alignées sur la structure réelle de la table.
     */
    protected $fillable = [
        'code',
        'valeur',
        'type',              // ⚠️ dans ta base c'est 'type' (pas 'type_reduction')
        'date_debut',
        'date_fin',
        'utilisation_max',   // ⚠️ dans ta base c'est 'utilisation_max'
        'utilisations_count',// ⚠️ présente en base
        'utilisations',      // ⭐ compteur principal (utilisé par Promo::sum)
        'actif',
    ];

    protected $casts = [
        'valeur'             => 'decimal:2',
        'date_debut'         => 'date',
        'date_fin'           => 'date',
        'utilisation_max'    => 'integer',
        'utilisations_count' => 'integer',
        'utilisations'       => 'integer',
        'actif'              => 'boolean',
        'deleted_at'         => 'datetime',
    ];

    // ══════════════════════════════════════════════════════════════
    // CONSTANTES
    // ══════════════════════════════════════════════════════════════

    public const TYPES = [
        'pourcentage' => 'Pourcentage',
        'fixe'        => 'Montant fixe',
    ];

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    /**
     * Promos actives (actif = true).
     */
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Promos non expirées (date_fin >= aujourd'hui).
     */
    public function scopeValides($query)
    {
        return $query->where('date_fin', '>=', now()->format('Y-m-d'));
    }

    /**
     * Promos actuellement utilisables (actif + pas expiré + pas dépassé le max).
     */
    public function scopeUtilisables($query)
    {
        return $query
            ->where('actif', true)
            ->where('date_debut', '<=', now()->format('Y-m-d'))
            ->where('date_fin',   '>=', now()->format('Y-m-d'))
            ->where(function ($q) {
                $q->whereNull('utilisation_max')
                  ->orWhereColumn('utilisations', '<', 'utilisation_max');
            });
    }

    // ══════════════════════════════════════════════════════════════
    // ACCESSEURS
    // ══════════════════════════════════════════════════════════════

    /**
     * Indique si la promo est active ET dans sa période de validité.
     */
    public function getEstActiveAttribute(): bool
    {
        if (!$this->actif) return false;

        $debut = $this->date_debut ? Carbon::parse($this->date_debut) : null;
        $fin   = $this->date_fin   ? Carbon::parse($this->date_fin)   : null;
        $now   = now();

        if ($debut && $now->lt($debut)) return false;
        if ($fin   && $now->gt($fin))   return false;

        return true;
    }

    /**
     * Indique si la promo a atteint sa limite d'utilisation.
     */
    public function getEstEpuiseeAttribute(): bool
    {
        if (is_null($this->utilisation_max)) return false;
        return $this->utilisations >= $this->utilisation_max;
    }

    /**
     * Nombre d'utilisations restantes (null si pas de limite).
     */
    public function getUtilisationsRestantesAttribute(): ?int
    {
        if (is_null($this->utilisation_max)) return null;
        return max(0, $this->utilisation_max - $this->utilisations);
    }

    /**
     * Badge HTML pour le statut (utile en Blade).
     */
    public function getStatutBadgeAttribute(): string
    {
        if (!$this->actif) {
            return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">⏸ Inactif</span>';
        }

        if ($this->date_fin && Carbon::parse($this->date_fin)->isPast()) {
            return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700">❌ Expiré</span>';
        }

        if ($this->est_epuisee) {
            return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">⚠️ Épuisé</span>';
        }

        return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">✅ Actif</span>';
    }

    /**
     * Réduction calculée sur un montant donné.
     * Ex: 5000 FCFA avec promo 10% → 500 FCFA de réduction.
     */
    public function calculerReduction(float $montant): float
    {
        if (!$this->est_active) return 0;

        if ($this->type === 'pourcentage') {
            return round($montant * ($this->valeur / 100), 2);
        }

        // Type 'fixe' → réduction plafonnée au montant
        return min((float) $this->valeur, $montant);
    }

    /**
     * Applique la réduction à un montant.
     */
    public function appliquerReduction(float $montant): float
    {
        return max(0, $montant - $this->calculerReduction($montant));
    }

    // ══════════════════════════════════════════════════════════════
    // MÉTHODES MÉTIER
    // ══════════════════════════════════════════════════════════════

    /**
     * Incrémente le compteur d'utilisations.
     * À appeler quand un client utilise le code.
     */
    public function incrementerUtilisation(): void
    {
        $this->increment('utilisations');
        $this->increment('utilisations_count'); // au cas où le champ existe encore
    }

    // ══════════════════════════════════════════════════════════════
    // ACCESSEURS DE COMPATIBILITÉ (pour le contrôleur actuel)
    // ══════════════════════════════════════════════════════════════

    /**
     * ⭐ Alias : $promo->type_reduction → $promo->type
     * Évite de casser les vues Blade qui utilisent 'type_reduction'.
     */
    public function getTypeReductionAttribute(): ?string
    {
        return $this->type;
    }

    /**
     * ⭐ Alias : $promo->limite_utilisation → $promo->utilisation_max
     */
    public function getLimiteUtilisationAttribute(): ?int
    {
        return $this->utilisation_max;
    }
}