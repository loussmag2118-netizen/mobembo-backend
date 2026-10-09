<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarnetPassager extends Model
{
    use HasFactory;

    protected $table = 'carnet_passagers';

    protected $fillable = [
        'canadian_user_id',
        'nom',
        'prenom',
        'date_naissance',
        'numero_passeport',
        'nationalite',
        'type',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    // ─────────────────────────────────────────────
    // Relations
    // ─────────────────────────────────────────────
    public function canadianUser()
    {
        return $this->belongsTo(CanadianUser::class, 'canadian_user_id');
    }

    // ─────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────
    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }
}