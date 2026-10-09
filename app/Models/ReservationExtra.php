<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationExtra extends Model
{
    use HasFactory;

    protected $table = 'reservation_extras';

    protected $fillable = [
        'reservation_id',
        'extra_id',
        'quantite',
        'prix_unitaire',
        'prix_total',
    ];

    protected $casts = [
        'quantite'      => 'integer',
        'prix_unitaire' => 'decimal:2',
        'prix_total'    => 'decimal:2',
    ];

    // ─────────────────────────────────────────────
    // Relations
    // ─────────────────────────────────────────────
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function extra()
    {
        return $this->belongsTo(Extra::class, 'extra_id');
    }
}