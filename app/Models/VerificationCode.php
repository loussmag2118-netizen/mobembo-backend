<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    protected $table = 'verification_codes';
    protected $fillable = ['identifiant', 'canal', 'type_usage', 'code', 'expire_at', 'tentatives', 'utilise', 'created_at'];
    public $timestamps = false;

    protected $casts = [
        'expire_at' => 'datetime',
        'utilise' => 'boolean',
    ];
}