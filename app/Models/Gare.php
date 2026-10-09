<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gare extends Model
{
    protected $table = 'gares';

    protected $fillable = [
        'agence_id',
        'nom_gare',
        'ville',
        'quartier',
    ];

    /**
     * L'agence à laquelle appartient cette gare.
     */
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    /**
     * Agents rattachés à cette gare.
     */
    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'gare_id'); // ⬅️ Permet de lister les agents d'une gare
    }

    /**
     * Réservations/Tickets enregistrés au départ de cette gare.
     */
    public function reservationsDepart()
    {
        return $this->hasMany(Reservation::class, 'gare_depart_id');
    }

    /**
     * Réservations/Tickets ayant pour destination cette gare.
     */
    public function reservationsArrivee()
    {
        return $this->hasMany(Reservation::class, 'gare_arrivee_id');
    }

    /**
     * Trajets qui partent de cette gare.
     */
    public function trajetsDepart()
    {
        return $this->hasMany(Trajet::class, 'gare_depart_id');
    }

    /**
     * Trajets qui arrivent à cette gare.
     */
    public function trajetsArrivee()
    {
        return $this->hasMany(Trajet::class, 'gare_arrivee_id');
    }

    /**
     * Tickets dont la destination est cette gare.
     */
    public function ticketsArrivee()
    {
        return $this->hasMany(Ticket::class, 'gare_arrivee_id');
    }
}
