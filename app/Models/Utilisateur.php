<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $table      = 'utilisateurs';
    protected $primaryKey = 'id';

    protected $fillable = [
        'agence_id',
        'gare_id', // ⬅️ Ajouté pour lier l'agent à sa gare spécifique
        'nom',
        'prenom',
        'email',
        'telephone',
        'role',
        'mot_de_passe',
        'code_acces_agent',
        'statut',
        'compte_confirme',
    ];

    protected $hidden = [
        'mot_de_passe',
        'remember_token',
        'code_acces_agent',
    ];

    protected $casts = [
        'compte_confirme' => 'boolean',
    ];

    // ------------------------------------------------------------------
    // Authentification (Laravel utilise "mot_de_passe" au lieu de "password")
    // ------------------------------------------------------------------
    public function getAuthPassword()       { return $this->mot_de_passe; }
    public function getAuthPasswordName()   { return 'mot_de_passe'; }
    public function getAuthIdentifierName() { return 'id'; }

    // ------------------------------------------------------------------
    // Relations
    // ------------------------------------------------------------------
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'utilisateur_id');
    }

    public function gare()
    {
        return $this->belongsTo(Gare::class, 'gare_id'); // ⬅️ Relation vers la gare de l'agent
    }

    // ------------------------------------------------------------------
    // Rôles
    // ------------------------------------------------------------------
    public function isAdmin()      { return $this->role === 'Admin'; }
    public function isAgent()      { return $this->role === 'Agent'; }
    public function isClient()     { return $this->role === 'Client'; }
    public function isSuperAdmin() { return $this->role === 'SuperAdmin'; }

    // ------------------------------------------------------------------
    // Helpers pour la logique de blocage
    // ------------------------------------------------------------------

    /**
     * Vérifie si le compte utilisateur est actif.
     */
    public function isCompteActif(): bool
    {
        return !$this->statut || strtolower($this->statut) === 'actif';
    }

    /**
     * Vérifie si l'agence de rattachement est active.
     * Renvoie true si l'utilisateur n'a pas d'agence (ex : SuperAdmin).
     */
    public function isAgenceActive(): bool
    {
        if (!$this->agence_id) {
            return true;
        }

        // Si la relation n'est pas chargée, on la charge à la volée
        $agence = $this->relationLoaded('agence')
            ? $this->agence
            : $this->agence()->first();

        return $agence && $agence->statut === 'Actif';
    }

    /**
     * Vérifie si l'utilisateur peut accéder à l'application.
     * Combine les deux vérifications : compte + agence.
     */
    public function peutAcceder(): bool
    {
        return $this->isCompteActif() && $this->isAgenceActive();
    }
}
