<?php

namespace App\Models;

use App\Models\Concerns\AppartientACompagnie;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Vol extends Model
{
    use HasFactory, SoftDeletes, AppartientACompagnie;

    protected $table = 'vols';

    protected $fillable = [
        'agence_id',
        'numero_vol',
        'compagnie',
        'avion',                  // ⭐ AJOUTÉ
        'origine',
        'destination',
        'ville_depart',
        'ville_arrivee',
        'ville_depart_iata',
        'ville_arrivee_iata',
        'date_depart',
        'date_arrivee',
        'prix',
        'places_disponibles',
        'places_totales',
        'flotte_id',
        'statut',
        'type_vol',
        'duree_minutes',
        'inclusions',
        'escales',
    ];

    protected $casts = [
        'date_depart'        => 'datetime',
        'date_arrivee'       => 'datetime',
        'prix'               => 'decimal:2',
        'places_disponibles' => 'integer',
        'places_totales'     => 'integer',
        'duree_minutes'      => 'integer',
        'inclusions'         => 'array',
        'escales'            => 'array',
    ];

    public function appareil()
    {
        return $this->belongsTo(Flotte::class, 'flotte_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'vol_id');
    }

    public function getDureeFormateeAttribute(): string
    {
        if ($this->duree_minutes) {
            $h = intdiv($this->duree_minutes, 60);
            $m = $this->duree_minutes % 60;
            return sprintf('%dh%02d', $h, $m);
        }

        if ($this->date_depart && $this->date_arrivee) {
            $diff = $this->date_depart->diff($this->date_arrivee);
            $h    = $diff->h + ($diff->d * 24);
            return sprintf('%dh%02d', $h, $diff->i);
        }

        return '';
    }

    public function getDateFormateeAttribute(): string
    {
        return $this->date_depart
            ? $this->date_depart->locale('fr')->isoFormat('dddd D MMMM YYYY')
            : '';
    }

    public function getEstDisponibleAttribute(): bool
    {
        return $this->places_disponibles > 0
            && in_array($this->statut, ['programme', 'en_vol']);
    }

    public function getEstDirectAttribute(): bool
    {
        return ($this->type_vol ?? 'direct') === 'direct';
    }

    /** Vols proposés aux clients : programmés, à venir, compagnie aérienne active. */
    public function scopeEnVente($query)
    {
        return $query->where('statut', 'programme')
            ->where('date_depart', '>', now())
            ->whereHas('partenaire', fn ($a) => $a->where('type_transport', Agence::AERIEN)->where('statut', 'Actif'));
    }

    public function scopeDisponibles($query)
    {
        return $query->whereIn('statut', ['programme', 'en_vol'])
                     ->where('places_disponibles', '>', 0);
    }
}