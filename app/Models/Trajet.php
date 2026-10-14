<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trajet extends Model
{
    protected $table = 'trajets';

    protected $fillable = [
        'agence_id',
        'ville_depart',
        'gare_depart_id',
        'ville_arrivee',
        'gare_arrivee_id',
        'bus_id',        // ⭐ NOUVEAU
        // Informations au client (facultatives)
        'duree_minutes',
        'bagages_autorises',
        'conditions_voyage',
        // 'prix' retiré — le prix se définit dans les planifications
    ];

    protected $casts = [
        'agence_id'       => 'integer',
        'gare_depart_id'  => 'integer',
        'gare_arrivee_id' => 'integer',
        'bus_id'          => 'integer',
        // 'prix' => 'decimal:2' retiré
    ];

    // ================================================================
    // RELATIONS
    // ================================================================

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function gareDepart()
    {
        return $this->belongsTo(Gare::class, 'gare_depart_id');
    }

    public function gareArrivee()
    {
        return $this->belongsTo(Gare::class, 'gare_arrivee_id');
    }

    /**
     * ⭐ Bus assigné au trajet.
     */
    public function bus()
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }

    /**
     * Vagues (horaires) du trajet.
     */
    public function vagues()
    {
        return $this->hasMany(VagueDepart::class, 'trajet_id');
    }

    /**
     * Planifications liées à ce trajet.
     */
    public function planifications()
    {
        return $this->hasMany(PlanificationVoyage::class, 'trajet_id');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    public function scopePourAgence($query, $agenceId)
    {
        return $query->where('agence_id', $agenceId);
    }

    // ================================================================
    // ACCESSORS
    // ================================================================

    /**
     * Nom lisible du trajet : "Bacongo → Tié-Tié"
     */
    public function getNomAttribute(): string
    {
        return ($this->gareDepart->nom_gare ?? '?')
             . ' → '
             . ($this->gareArrivee->nom_gare ?? '?');
    }

    /**
     * Liste des horaires au format "08:00, 10:00, 14:00"
     */
    public function getHorairesFormatesAttribute(): string
    {
        if (!$this->vagues || $this->vagues->isEmpty()) {
            return '—';
        }

        return $this->vagues
            ->pluck('heure_depart')
            ->map(fn($h) => substr($h, 0, 5))
            ->implode(', ');
    }

    /**
     * Indique si le bus assigné est disponible.
     */
    public function getBusEstDisponibleAttribute(): bool
    {
        return $this->bus && $this->bus->statut === 'Disponible';
    }
}