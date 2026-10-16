<?php

namespace App\Models;

use App\Models\Concerns\AppartientACompagnie;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    use HasFactory, SoftDeletes, AppartientACompagnie;

    protected $table = 'extras';

    protected $fillable = [
        'agence_id',
        'code',
        'nom',
        'description',
        'prix',
        'categorie',
        'icone',
        'ordre',
        'actif',
    ];

    protected $casts = [
        'prix'  => 'decimal:2',
        'ordre' => 'integer',
        'actif' => 'boolean',
    ];

    // ─────────────────────────────────────────────
    // Relations
    // ─────────────────────────────────────────────
    public function reservations()
    {
        return $this->belongsToMany(
            Reservation::class,
            'reservation_extras',
            'extra_id',
            'reservation_id'
        )->withPivot('quantite', 'prix_unitaire', 'prix_total')
         ->withTimestamps();
    }

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────
    public function scopeActifs($query)
    {
        return $query->where('actif', true)->orderBy('ordre');
    }

    public function scopeCategorie($query, string $categorie)
    {
        return $query->where('categorie', $categorie);
    }
}