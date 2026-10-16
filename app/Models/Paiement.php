<?php

namespace App\Models;

use App\Models\Concerns\AppartientACompagnie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paiement extends Model
{
    use SoftDeletes, AppartientACompagnie;

    protected $table = 'paiements';

    protected $fillable = [
        'agence_id',
        'reservation_id',
        'reference_transaction',
        'nom_client',
        'telephone_client',
        'methode',
        'montant',
        'statut',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    // -----------------------------------------------------
    // Helpers
    // -----------------------------------------------------
    public function isReussi(): bool    { return $this->statut === 'Reussi'; }
    public function isEnAttente(): bool { return $this->statut === 'En_Attente'; }
    public function isEchoue(): bool    { return $this->statut === 'Echoue'; }
}