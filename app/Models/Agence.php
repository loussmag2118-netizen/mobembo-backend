<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    protected $table = 'agences';
    protected $fillable = ['nom_agence', 'slogan', 'couleur', 'logo_media_id', 'statut', 'age_max_enfant', 'choix_siege', 'date_partenariat', 'superadmin_id'];

    protected $casts = ['age_max_enfant' => 'integer', 'choix_siege' => 'boolean'];

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

    // ==================================================================
    // VITRINE (logo, photos, slogan, couleur) gérée par l'agence
    // ==================================================================

    public function logoMedia()
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }

    public function photos()
    {
        return $this->hasMany(Media::class, 'agence_id')->where('type', 'photo')->orderBy('ordre')->orderBy('id');
    }

    /**
     * Logo à afficher sur le web (page publique du billet) : logo envoyé par
     * l'agence, sinon logo connu des agences historiques, sinon Mobembo.
     */
    public function logoPublicUrl(): string
    {
        if ($this->logoMedia) {
            return $this->logoMedia->url();
        }
        $fichier = 'images/agences/' . \Illuminate\Support\Str::slug($this->nom_agence) . '.jpg';

        return file_exists(public_path($fichier)) ? asset($fichier) : asset('favicon-192.png');
    }

    /**
     * Vitrine envoyée à l'application. Les valeurs nulles laissent
     * l'application utiliser ses visuels par défaut.
     */
    public function vitrine(): array
    {
        $this->loadMissing(['logoMedia', 'photos']);

        return [
            'logo_url' => $this->logoMedia?->url(),
            'couleur'  => $this->couleur,
            'slogan'   => $this->slogan,
            'photos'   => $this->photos->map(fn (Media $m) => $m->url())->values()->all(),
        ];
    }
}
