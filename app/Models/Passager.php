<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passager extends Model
{
    use HasFactory;

    protected $table = 'passagers';

    protected $fillable = [
        'reservation_id',
        'type',              // adulte | enfant | bebe
        'nom',
        'prenom',
        'date_naissance',
        'numero_passeport',
        'nationalite',
        'telephone',
        'email',
        'sauvegarde_carnet',
    ];

    protected $casts = [
        'date_naissance'    => 'date',
        'sauvegarde_carnet' => 'boolean',
    ];

    // ─────────────────────────────────────────────
    // Relations
    // ─────────────────────────────────────────────
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    // ─────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────
    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'adulte' => 'Adulte',
            'enfant' => 'Enfant',
            'bebe'   => 'Bébé',
            default  => 'Adulte',
        };
    }
}