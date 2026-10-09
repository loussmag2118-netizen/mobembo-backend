<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    use HasFactory;

    protected $table = 'villes';

    protected $fillable = [
        'nom',
        'code_iata',
        'pays',
        'drapeau',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // ─────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────
    public function getLabelAttribute(): string
    {
        return "{$this->nom} ({$this->code_iata})";
    }

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────
    public function scopeActives($query)
    {
        return $query->where('actif', true);
    }
}