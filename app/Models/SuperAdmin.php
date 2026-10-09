<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SuperAdmin extends Authenticatable
{
    use Notifiable;

    protected $table = 'super_admins';

    protected $fillable = ['nom', 'telephone', 'email', 'mot_de_passe'];

    protected $hidden = ['mot_de_passe'];

    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }
}