<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    protected $table = 'agences';
    protected $fillable = ['nom_agence', 'statut', 'date_partenariat', 'superadmin_id'];

    public function superAdmin()
    {
        return $this->belongsTo(SuperAdmin::class, 'superadmin_id');
    }

    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'agence_id');
    }

    /**
     * Récupère uniquement les utilisateurs qui ont le rôle d'agent pour cette agence.
     */
    public function agents()
    {
        return $this->hasMany(Utilisateur::class, 'agence_id')->where('role', 'Agent');
    }

    public function gares()
    {
        return $this->hasMany(Gare::class, 'agence_id');
    }

    public function bus()
    {
        return $this->hasMany(Bus::class, 'agence_id');
    }

    public function trajets()
    {
        return $this->hasMany(Trajet::class, 'agence_id');
    }
}
