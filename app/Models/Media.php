<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Image stockée en base (logo et photos des agences), servie par
 * GET /medias/{id}. Le contenu binaire n'est chargé que pour l'affichage.
 */
class Media extends Model
{
    protected $table = 'medias';

    protected $fillable = ['agence_id', 'type', 'ordre', 'mime', 'taille', 'contenu'];

    protected $hidden = ['contenu'];

    /** Exclut le contenu binaire des requêtes par défaut. */
    protected static function booted(): void
    {
        static::addGlobalScope('sans_contenu', function (Builder $q) {
            $q->select(['id', 'agence_id', 'type', 'ordre', 'mime', 'taille', 'created_at', 'updated_at']);
        });
    }

    public function url(): string
    {
        // ?v= : change à chaque nouvelle image, le cache du téléphone se met à jour
        return url('/medias/' . $this->id) . '?v=' . ($this->updated_at?->timestamp ?? 0);
    }
}
