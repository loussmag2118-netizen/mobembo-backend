<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flotte extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'flottes';

    protected $fillable = [
        'nom_appareil',
        'modele',
        'capacite_places',
        'annee',
        'statut',
    ];
}