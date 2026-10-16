<?php

namespace App\Models;

use App\Models\Concerns\AppartientACompagnie;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes, AppartientACompagnie;

    protected $table = 'notifications';

    protected $fillable = [
        'agence_id',
        'canadian_user_id',
        'utilisateur_id',
        'titre',
        'message',
        'type',
        'lu',
    ];

    protected $casts = [
        'lu'         => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ─────────────────────────────────────────────
    // Relations
    // ─────────────────────────────────────────────
    public function canadianUser()
    {
        return $this->belongsTo(CanadianUser::class, 'canadian_user_id');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────
    public function scopeNonLues($query)
    {
        return $query->where('lu', false);
    }
}