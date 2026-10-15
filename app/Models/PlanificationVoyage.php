<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PlanificationVoyage extends Model
{
    use HasFactory;

    protected $table = 'planification_voyages';

    protected $fillable = [
        'agence_id',
        'trajet_id',
        'bus_id',
        'vague_depart_id',
        'date_voyage',
        'nombre_tickets_total',
        'places_disponibles',
        'prix', // 👈 Ajouté ici pour autoriser l'enregistrement
        'prix_enfant',   // null = même prix que l'adulte
        'statut_voyage',
    ];

    protected $casts = [
        'agence_id'            => 'integer',
        'trajet_id'            => 'integer',
        'bus_id'               => 'integer',
        'vague_depart_id'      => 'integer',
        'date_voyage'          => 'date',
        'nombre_tickets_total' => 'integer',
        'places_disponibles'   => 'integer',
        'prix'                 => 'decimal:2', // 👈 Ajouté ici pour caster proprement en décimal
    ];

    // ══════════════════════════════════════════════════════════════
    // CONSTANTES
    // ══════════════════════════════════════════════════════════════

    public const STATUTS = [
        'Programmation' => 'Programmation',
        'En cours'      => 'En cours',
        'Terminé'       => 'Terminé',
        'Annulé'        => 'Annulé',
    ];

    // ══════════════════════════════════════════════════════════════
    // RELATIONS
    // ══════════════════════════════════════════════════════════════

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function trajet()
    {
        return $this->belongsTo(Trajet::class, 'trajet_id');
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }

    public function vagueDepart()
    {
        return $this->belongsTo(VagueDepart::class, 'vague_depart_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'planification_voyage_id');
    }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    public function scopePourAgence($query, $agenceId)
    {
        return $query->where('agence_id', $agenceId);
    }

    public function scopeNonAnnulees($query)
    {
        return $query->where('statut_voyage', '!=', 'Annulé');
    }

    public function scopeAVenir($query)
    {
        return $query->where('date_voyage', '>=', now()->format('Y-m-d'));
    }

    public function scopeStatut($query, $statut)
    {
        return $query->where('statut_voyage', $statut);
    }

    // ══════════════════════════════════════════════════════════════
    // ACCESSEURS
    // ══════════════════════════════════════════════════════════════

    public function getDateVoyageCarbonAttribute(): ?Carbon
    {
        if (!$this->date_voyage) return null;

        if ($this->date_voyage instanceof Carbon) {
            return $this->date_voyage;
        }

        try {
            return Carbon::parse($this->date_voyage);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getPlacesVenduesAttribute(): int
    {
        return $this->tickets ? $this->tickets->count() : 0;
    }

    public function getPlacesTotalesAttribute(): int
    {
        if ($this->nombre_tickets_total !== null) {
            return (int) $this->nombre_tickets_total;
        }
        if ($this->bus) {
            return (int) ($this->bus->capacite_totale ?? $this->bus->places ?? 0);
        }
        return (int) ($this->places_disponibles ?? 0) + $this->places_vendues;
    }

    public function getPlacesRestantesAttribute(): int
    {
        return (int) ($this->places_disponibles ?? 0);
    }

    public function getEstCompletAttribute(): bool
    {
        return $this->places_restantes <= 0;
    }

    public function getEstPasseAttribute(): bool
    {
        $date = $this->date_voyage_carbon;
        return $date ? $date->isPast() : false;
    }

    public function getStatutBadgeAttribute(): string
    {
        $badges = [
            'Programmation' => ['bg-blue-50 text-blue-700',        '📅 Programmation'],
            'En cours'      => ['bg-amber-50 text-amber-700',      '🚀 En cours'],
            'Terminé'       => ['bg-emerald-50 text-emerald-700',  '✅ Terminé'],
            'Annulé'        => ['bg-red-50 text-red-700',          '❌ Annulé'],
        ];

        [$class, $label] = $badges[$this->statut_voyage] ?? ['bg-slate-100 text-slate-700', '• Inconnu'];

        return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold '.$class.'">'.$label.'</span>';
    }

    public function getLibelleCompletAttribute(): string
    {
        $trajet = $this->trajet
            ? ($this->trajet->gareDepart->nom_gare ?? '?')
                . ' → '
                . ($this->trajet->gareArrivee->nom_gare ?? '?')
            : '?';

        $date = $this->date_voyage_carbon
            ? $this->date_voyage_carbon->format('d/m/Y')
            : '—';

        $heure = $this->vagueDepart
            ? substr((string) ($this->vagueDepart->heure_depart ?? ''), 0, 5)
            : '—';

        return $trajet . ' · ' . $date . ' · ' . $heure;
    }
}
