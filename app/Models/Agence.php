<?php

namespace App\Models;

use App\Support\Couleurs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class Agence extends Model
{
    protected $table = 'agences';

    // Type de partenaire : compagnie de bus ou compagnie aérienne
    public const BUS    = 'bus';
    public const AERIEN = 'aerien';
    protected $fillable = ['nom_agence', 'type_transport', 'slogan', 'couleur', 'couleur_secondaire', 'logo_media_id', 'statut', 'age_max_enfant', 'choix_siege', 'date_partenariat', 'superadmin_id'];

    protected $casts = ['age_max_enfant' => 'integer', 'choix_siege' => 'boolean'];

    protected $attributes = ['type_transport' => self::BUS];

    public function estAerien(): bool
    {
        return $this->type_transport === self::AERIEN;
    }

    public function scopeDeBus($query)
    {
        return $query->where('type_transport', self::BUS);
    }

    public function scopeAeriennes($query)
    {
        return $query->where('type_transport', self::AERIEN);
    }

    public function vols()
    {
        return $this->hasMany(Vol::class, 'agence_id');
    }

    public function flottes()
    {
        return $this->hasMany(Flotte::class, 'agence_id');
    }

    public function reservationsVols()
    {
        return $this->hasMany(Reservation::class, 'agence_id');
    }

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
            'couleur_secondaire' => $this->couleur_secondaire,
            'slogan'   => $this->slogan,
            'photos'   => $this->photos->map(fn (Media $m) => $m->url())->values()->all(),
        ];
    }

    /** Remplace le logo de l'agence (l'ancien est supprimé). */
    public function remplacerLogo(UploadedFile $fichier): void
    {
        DB::transaction(function () use ($fichier) {
            $ancien = $this->logo_media_id;
            $this->update(['logo_media_id' => Media::depuisFichier($fichier, $this->id, 'logo')->id]);
            if ($ancien) {
                Media::whereKey($ancien)->delete();
            }
        });
    }

    /**
     * Identité visuelle de l'administration de l'agence : ses couleurs
     * (sinon le vert Mobembo) et son logo.
     *  - fond : couleur principale lisible sous un texte blanc ;
     *  - fonce : survol des boutons et dégradés.
     */
    public function identite(): array
    {
        $primaire = $this->couleur ?: '#124231';
        $secondaire = $this->couleur_secondaire ?: ($this->couleur ? Couleurs::nuancer($this->couleur, -0.45) : '#10B981');
        $fond = Couleurs::pourTexteBlanc($primaire);

        return [
            'primaire'   => $primaire,
            'secondaire' => $secondaire,
            'fond'       => $fond,
            'fonce'      => Couleurs::nuancer($fond, 0.3),
            'fond_rvb'   => Couleurs::rvbTexte($fond),
            // Accent lisible sur le fond : la secondaire, éclaircie si elle est sombre
            'accent'     => Couleurs::contrasteBlanc($secondaire) > 3 ? Couleurs::nuancer($secondaire, -0.6) : $secondaire,
            'logo'       => $this->logoMedia?->url(),
        ];
    }
}
