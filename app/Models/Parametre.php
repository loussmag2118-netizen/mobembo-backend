<?php

namespace App\Models;

use App\Models\Concerns\AppartientACompagnie;
use Illuminate\Database\Eloquent\Model;

class Parametre extends Model
{
    use AppartientACompagnie;

    protected $table = 'parametres';

    protected $fillable = [
        'agence_id',
        'cle',
        'valeur',
    ];
}