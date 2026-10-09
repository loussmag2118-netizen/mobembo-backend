<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        // === Liaisons ===
        'planification_voyage_id',
        'agence_id',              // ⬅️ NOUVEAU
        'agent_controleur_id',
        'agent_vendeur_id',       // ⬅️ NOUVEAU
        'utilisateur_id',

        // === Identifiants ===
        'reference_ticket',
        'qr_code_token',

        // === Passager ===
        'nom_passager',
        'prenom_passager',
        'telephone_passager',     // ⬅️ NOUVEAU

        // === Destination figée ===
        'ville_arrivee',          // ⬅️ NOUVEAU
        'quartier',               // ⬅️ NOUVEAU
        'gare_arrivee_id',        // ⬅️ NOUVEAU
        'numero_bus',

        // === Détails ===
        'nombre_places_reservees',
        'montant_total',

        // === Statuts ===
        'statut_paiement',
        'statut_embarquement',

        // === Paiement ===
        'mode_paiement',          // ⬅️ NOUVEAU
        'date_paiement',          // ⬅️ NOUVEAU

        // === Dates ===
        'date_reservation',
    ];

    protected $casts = [
        'date_reservation' => 'datetime',
        'date_paiement'    => 'datetime',
        'montant_total'    => 'decimal:2',
    ];

    // ================================================================
    // RELATIONS
    // ================================================================

    /**
     * La planification du voyage (trajet, date, bus, agence).
     */
    public function planificationVoyage()
    {
        return $this->belongsTo(PlanificationVoyage::class, 'planification_voyage_id');
    }

    /**
     * L'agence propriétaire du ticket.
     */
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    /**
     * L'agent qui a vendu le ticket (null = vente en ligne).
     */
    public function agentVendeur()
    {
        return $this->belongsTo(Utilisateur::class, 'agent_vendeur_id');
    }

    /**
     * L'agent qui a contrôlé/scanné le ticket.
     */
    public function agentControleur()
    {
        return $this->belongsTo(Utilisateur::class, 'agent_controleur_id');
    }

    /**
     * L'utilisateur client (si le ticket est lié à un compte).
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    /**
     * La gare d'arrivée de ce ticket.
     */
    public function gareArrivee()
    {
        return $this->belongsTo(Gare::class, 'gare_arrivee_id');
    }

    // ================================================================
    // SCOPES (bonus — filtres réutilisables)
    // ================================================================

    /**
     * Scope : tickets d'une agence donnée.
     */
    public function scopePourAgence($query, $agenceId)
    {
        return $query->where('agence_id', $agenceId);
    }

    /**
     * Scope : tickets d'un quartier donné.
     */
    public function scopePourQuartier($query, $quartier)
    {
        return $query->where('quartier', $quartier);
    }

    /**
     * Scope : tickets d'une ville d'arrivée.
     */
    public function scopePourVille($query, $ville)
    {
        return $query->where('ville_arrivee', $ville);
    }

    /**
     * Scope : tickets avec un statut d'embarquement donné.
     */
    public function scopeStatut($query, $statut)
    {
        return $query->where('statut_embarquement', $statut);
    }

    // ================================================================
    // ACCESSORS (bonus — attributs virtuels)
    // ================================================================

    /**
     * Nom complet du passager.
     */
    public function getNomCompletAttribute()
    {
        return trim(($this->nom_passager ?? '') . ' ' . ($this->prenom_passager ?? ''));
    }

    /**
     * Le ticket est-il déjà scanné/présent ?
     */
    public function getEstPresentAttribute()
    {
        return $this->statut_embarquement === 'Present';
    }
}