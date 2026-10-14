<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Réservation refusée pour une raison métier (plus assez de places,
 * départ annulé ou passé, montant incorrect…). Le message est destiné
 * au client ; le code HTTP accompagne la réponse de l'API.
 */
class ReservationImpossibleException extends RuntimeException
{
    public function __construct(string $message, private int $statutHttp = 422)
    {
        parent::__construct($message);
    }

    public function statutHttp(): int
    {
        return $this->statutHttp;
    }
}
