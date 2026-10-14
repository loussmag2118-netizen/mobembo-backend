<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Libère les places des réservations non payées dont le délai est dépassé.
// En plus de cette tâche planifiée, l'expiration est aussi appliquée à
// chaque réservation et à chaque chargement des départs par l'application.
Artisan::command('reservations:expirer', function (\App\Services\ReservationService $reservations) {
    $n = $reservations->expirerReservations();
    $this->info("{$n} réservation(s) expirée(s).");
})->purpose('Expire les réservations non payées et libère leurs places');

\Illuminate\Support\Facades\Schedule::command('reservations:expirer')->everyMinute()->withoutOverlapping();
