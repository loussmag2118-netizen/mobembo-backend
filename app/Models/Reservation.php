<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reservations';

    protected $fillable = [
        'reference',
        'code_pnr',
        'utilisateur_id',
        'canadian_user_id',
        'vol_id',
        'vol_retour_id',
        'gare_depart_id',  // ⬅️ Ajouté pour tracer la gare de départ
        'gare_arrivee_id', // ⬅️ Ajouté pour tracer la gare d'arrivée
        'type_reservation',
        'classe',
        'nombre_places',
        'telephone',
        'contact_telephone',
        'contact_email',
        'prix_billet',
        'prix_extras',
        'montant_total',
        'statut',
        'date_paiement',
        'methode_paiement',
        'reference_paiement',
        'code_reservation',
        'mode_paiement',
        'qr_code',
        'billet_pdf_url',
    ];

    protected $casts = [
        'montant_total'    => 'decimal:2',
        'prix_billet'      => 'decimal:2',
        'prix_extras'      => 'decimal:2',
        'nombre_places'    => 'integer',
        'utilisateur_id'   => 'integer',
        'canadian_user_id' => 'integer',
        'vol_id'           => 'integer',
        'vol_retour_id'    => 'integer',
        'gare_depart_id'   => 'integer',
        'gare_arrivee_id'  => 'integer',
        'date_paiement'    => 'datetime',
    ];

    // ─────────────────────────────────────────────
    // Relations
    // ─────────────────────────────────────────────
    public function vol()
    {
        return $this->belongsTo(Vol::class, 'vol_id');
    }

    public function volRetour()
    {
        return $this->belongsTo(Vol::class, 'vol_retour_id');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    public function canadianUser()
    {
        return $this->belongsTo(CanadianUser::class, 'canadian_user_id');
    }

    public function gareDepart()
    {
        return $this->belongsTo(Gare::class, 'gare_depart_id'); // ⬅️ Relation gare de départ
    }

    public function gareArrivee()
    {
        return $this->belongsTo(Gare::class, 'gare_arrivee_id'); // ⬅️ Relation gare d'arrivée
    }

    public function passagers()
    {
        return $this->hasMany(Passager::class, 'reservation_id');
    }

    public function extras()
    {
        return $this->belongsToMany(
            Extra::class,
            'reservation_extras',
            'reservation_id',
            'extra_id'
        )->withPivot('quantite', 'prix_unitaire', 'prix_total')
         ->withTimestamps();
    }

    public function reservationExtras()
    {
        return $this->hasMany(ReservationExtra::class, 'reservation_id');
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────
    public function recalculerTotal(): void
    {
        $this->prix_extras   = $this->reservationExtras()->sum('prix_total');
        $this->montant_total = $this->prix_billet + $this->prix_extras;
        $this->save();
    }
}
