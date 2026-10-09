<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VagueDepart extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vague_departs';

    protected $fillable = [
        'agence_id',
        'trajet_id',        // ⭐ AJOUTÉ
        'heure_depart',
        'nom_vague',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONS
    // ============================================================

    /**
     * Relation : Une vague de départ appartient à une agence.
     */
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    /**
     * ⭐ NOUVELLE RELATION : Une vague appartient à un trajet.
     */
    public function trajet()
    {
        return $this->belongsTo(Trajet::class, 'trajet_id');
    }

    /**
     * Relation : Une vague de départ peut être liée à plusieurs planifications de voyages.
     */
    public function planificationVoyages()
    {
        return $this->hasMany(PlanificationVoyage::class, 'vague_depart_id');
    }
}