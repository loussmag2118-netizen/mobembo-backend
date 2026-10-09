<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paiement extends Model
{
    use SoftDeletes;

    protected $table = 'paiements';

    protected $fillable = [
        'reservation_id',
        'user_id',
        'reference',
        'montant',
        'devise',
        'mode_paiement',
        'telephone_paiement',
        'statut',
        'date_paiement',
        'details',
    ];

    protected $casts = [
        'montant'       => 'decimal:2',
        'date_paiement' => 'datetime',
        'details'       => 'array',
    ];

    // -----------------------------------------------------
    // Helpers
    // -----------------------------------------------------
    public function isReussi(): bool    { return $this->statut === 'Reussi'; }
    public function isEnAttente(): bool { return $this->statut === 'En_Attente'; }
    public function isEchoue(): bool    { return $this->statut === 'Echoue'; }
}