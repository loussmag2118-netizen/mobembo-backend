<?php

namespace App\Models\Concerns;

use App\Models\Agence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Donnée aérienne rattachée à sa compagnie (agence de type « aerien »).
 * Chaque requête de l'administration se limite à la compagnie connectée.
 */
trait AppartientACompagnie
{
    public function partenaire(): BelongsTo
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function scopeDeLaCompagnie(Builder $query, ?int $agenceId): Builder
    {
        return $query->where($this->getTable() . '.agence_id', $agenceId);
    }
}
