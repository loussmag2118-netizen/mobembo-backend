<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClasseTarifaire extends Model
{
    use HasFactory;

    protected $table = 'classes_tarifaires';

    protected $fillable = [
        'code',
        'nom',
        'multiplicateur',
        'description',
        'ordre',
        'actif',
    ];

    protected $casts = [
        'multiplicateur' => 'decimal:2',
        'ordre'          => 'integer',
        'actif'          => 'boolean',
    ];

    public function scopeActives($query)
    {
        return $query->where('actif', true)->orderBy('ordre');
    }
}