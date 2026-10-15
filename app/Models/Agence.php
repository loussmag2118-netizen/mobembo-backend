<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    protected $table = 'agences';
    protected $fillable = ['nom_agence', 'statut', 'age_max_enfant', 'date_partenariat', 'superadmin_id'];

    protected $casts = ['age_max_enfant' => 'integer'];

    public function superAdmin()
    {
        return $this->belongsTo(SuperAdmin::class, 'superadmin_id');
    }

    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'agence_id');
    }

    /**
     * Récupère le compte Administrateur de cette agence.
     */
    public function admin()
    {
        return $this->hasOne(Utilisateur::class, 'agence_id')->where('role', 'Admin');
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

    /**
     * Logo affiché par l'application mobile (fichier embarqué dans l'app).
     * Logo Mobembo Line par défaut pour une nouvelle agence.
     */
    public function getLogoAppAttribute(): string
    {
        return [
            'Océan du Nord'   => 'assets/images/oceandunord.png',
            'Nzoko Transport' => 'assets/images/Nzoko.jpg',
            'Stelimac'        => 'assets/images/Stelimac.jpg',
            'Transbony'       => 'assets/images/transbony.png',
        ][$this->nom_agence] ?? 'assets/images/Logo.png';
    }
}
