<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class CanadianUser extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'canadian_users';

    protected $fillable = [
        'name',
        'nom',
        'prenom',
        'email',
        'telephone',
        'password',
        'compte_confirme',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'compte_confirme'   => 'boolean',
        'est_admin'         => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    // ─────────────────────────────────────────────
    // Relations
    // ─────────────────────────────────────────────
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'canadian_user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'canadian_user_id');
    }

    public function carnetPassagers()
    {
        return $this->hasMany(CarnetPassager::class, 'canadian_user_id');
    }

    // ─────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────
    public function getNomCompletAttribute(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
    }
}