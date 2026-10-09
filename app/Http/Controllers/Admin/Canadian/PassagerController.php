<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use App\Models\Passager;
use Illuminate\Http\Request;

class PassagerController extends Controller
{
    public function index(Request $request)
    {
        $query = Passager::with('reservation.vol');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nom', 'like', "%{$q}%")
                    ->orWhere('prenom', 'like', "%{$q}%")
                    ->orWhere('numero_passeport', 'like', "%{$q}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $passagers = $query->latest()->paginate(20);

        return view('admin.canadian.passagers.index', compact('passagers'));
    }

    public function show($id)
    {
        $passager = Passager::with('reservation.vol')->findOrFail($id);
        return view('admin.canadian.passagers.show', compact('passager'));
    }
}