<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use Illuminate\Support\Facades\Auth;

/**
 * Base des écrans d'une compagnie aérienne : chaque requête est limitée
 * à la compagnie de l'administrateur connecté (middleware espace:aerien).
 */
abstract class EspaceAerienController extends Controller
{
    protected function compagnie(): Agence
    {
        return Auth::user()->agence;
    }

    protected function compagnieId(): int
    {
        return (int) Auth::user()->agence_id;
    }
}
