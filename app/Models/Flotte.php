<?php

namespace App\Models;

use App\Models\Concerns\AppartientACompagnie;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flotte extends Model
{
    use HasFactory, SoftDeletes, AppartientACompagnie;

    protected $table = 'flottes';

    protected $fillable = [
        'agence_id',
        'nom_appareil',
        'modele',
        'immatriculation',
        'capacite_places',
        'annee',
        'statut',
    ];

    public function vols()
    {
        return $this->hasMany(Vol::class, 'flotte_id');
    }
}
