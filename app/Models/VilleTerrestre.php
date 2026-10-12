<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Ville du transport terrestre — référentiel commun à toutes les agences,
 * géré par le Super Admin. Les gares y sont rattachées ; trajets et agents
 * héritent de la ville de leur gare.
 */
class VilleTerrestre extends Model
{
    protected $table = 'villes_terrestres';

    protected $fillable = [
        'nom',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // ================================================================
    // RELATIONS
    // ================================================================

    public function gares()
    {
        return $this->hasMany(Gare::class, 'ville_id');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    public function scopeActives($query)
    {
        return $query->where('actif', true);
    }

    // ================================================================
    // SYNCHRONISATION
    // ================================================================

    /**
     * Recopie le nom de la ville dans les colonnes dénormalisées
     * (gares.ville, trajets.ville_depart / ville_arrivee) après un renommage.
     */
    public function synchroniserNom(): void
    {
        $gareIds = $this->gares()->pluck('id');

        DB::transaction(function () use ($gareIds) {
            Gare::whereIn('id', $gareIds)->update(['ville' => $this->nom]);
            Trajet::whereIn('gare_depart_id', $gareIds)->update(['ville_depart' => $this->nom]);
            Trajet::whereIn('gare_arrivee_id', $gareIds)->update(['ville_arrivee' => $this->nom]);
        });
    }
}
