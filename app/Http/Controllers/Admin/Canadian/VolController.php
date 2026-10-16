<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Models\Flotte;
use App\Models\Reservation;
use App\Models\Ville;
use App\Models\Vol;
use App\Services\ReservationVolService;
use App\Support\Audit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VolController extends EspaceAerienController
{
    public const STATUTS = [
        'programme' => 'Programmé',
        'en_vol'    => 'En vol',
        'atterri'   => 'Atterri',
        'annule'    => 'Annulé',
    ];

    public function __construct(private ReservationVolService $reservations)
    {
    }

    public function index(Request $request)
    {
        $this->reservations->expirer();

        $vols = Vol::deLaCompagnie($this->compagnieId())
            ->when($request->filled('q'), function ($q) use ($request) {
                $t = $request->q;
                $q->where(fn ($w) => $w->where('numero_vol', 'like', "%{$t}%")
                    ->orWhere('ville_depart', 'like', "%{$t}%")
                    ->orWhere('ville_arrivee', 'like', "%{$t}%"));
            })
            ->when(array_key_exists((string) $request->statut, self::STATUTS), fn ($q) => $q->where('statut', $request->statut))
            ->orderByDesc('date_depart')
            ->paginate(5)->withQueryString();

        // Chiffres de tous les vols de la compagnie (pas seulement de la page)
        $parStatut = Vol::deLaCompagnie($this->compagnieId())
            ->selectRaw('statut, COUNT(*) AS n')->groupBy('statut')->pluck('n', 'statut');

        return view('admin.canadian.vols.index', compact('vols', 'parStatut'));
    }

    public function create()
    {
        return view('admin.canadian.vols.create', $this->donneesFormulaire(new Vol([
            'statut' => 'programme', 'type_vol' => 'direct',
        ])));
    }

    public function store(Request $request)
    {
        $donnees = $this->valider($request);
        $donnees['agence_id'] = $this->compagnieId();
        $donnees['places_disponibles'] = $donnees['places_totales'];

        $vol = Vol::create($donnees);
        Audit::log('vol.cree', $vol, ['numero' => $vol->numero_vol], $this->compagnieId());

        return redirect()->route('canadian.vols.index')->with('success', "Vol {$vol->numero_vol} créé.");
    }

    public function show($id)
    {
        $this->reservations->expirer();
        $vol = $this->vol($id);
        $reservations = Reservation::with('canadianUser')
            ->where('vol_id', $vol->id)
            ->latest()
            ->paginate(5)->withQueryString();
        $occupees = $this->reservations->placesOccupees($vol);

        return view('admin.canadian.vols.show', compact('vol', 'reservations', 'occupees'));
    }

    public function edit($id)
    {
        return view('admin.canadian.vols.edit', $this->donneesFormulaire($this->vol($id)));
    }

    public function update(Request $request, $id)
    {
        $vol = $this->vol($id);
        $donnees = $this->valider($request, $vol);

        $occupees = $this->reservations->placesOccupees($vol);
        if ($donnees['places_totales'] < $occupees) {
            return back()->withInput()->withErrors(['places_totales' => "{$occupees} place(s) sont déjà réservées sur ce vol : la capacité ne peut pas être inférieure."]);
        }

        $avant = $vol->only(['date_depart', 'statut', 'prix', 'places_totales']);
        DB::transaction(function () use ($vol, $donnees) {
            $vol->update($donnees);
            $this->reservations->recalculerPlaces($vol->fresh());
        });

        if ($donnees['statut'] === 'annule' && $avant['statut'] !== 'annule' && $occupees > 0) {
            Audit::log('vol.annule_avec_reservations', $vol, ['places' => $occupees], $this->compagnieId());
        }
        Audit::log('vol.modifie', $vol, ['avant' => $avant], $this->compagnieId());

        return redirect()->route('canadian.vols.index')->with('success', "Vol {$vol->numero_vol} modifié.");
    }

    public function destroy($id)
    {
        $vol = $this->vol($id);
        if (Reservation::where('vol_id', $vol->id)->whereIn('statut', Reservation::STATUTS_ACTIFS)->exists()) {
            return back()->with('error', 'Ce vol a des réservations en cours ou payées : annulez-le (statut « Annulé ») au lieu de le supprimer.');
        }

        $vol->delete();
        Audit::log('vol.supprime', $vol, ['numero' => $vol->numero_vol], $this->compagnieId());

        return redirect()->route('canadian.vols.index')->with('success', 'Vol supprimé.');
    }

    public function dupliquer($id)
    {
        $source = $this->vol($id);
        $copie = $source->replicate(['places_disponibles']);
        $copie->numero_vol = $this->numeroLibre($source->numero_vol);
        $copie->date_depart = $source->date_depart?->copy()->addWeek();
        $copie->date_arrivee = $source->date_arrivee?->copy()->addWeek();
        $copie->statut = 'programme';
        $copie->places_disponibles = $copie->places_totales ?? $source->places_disponibles;
        $copie->save();

        return redirect()->route('canadian.vols.edit', $copie->id)
            ->with('success', "Vol dupliqué une semaine plus tard ({$copie->numero_vol}) : vérifiez puis enregistrez.");
    }

    // ------------------------------------------------------------------

    private function vol($id): Vol
    {
        return Vol::deLaCompagnie($this->compagnieId())->findOrFail($id);
    }

    private function donneesFormulaire(Vol $vol): array
    {
        return [
            'vol'      => $vol,
            'aeroports'=> Ville::where('actif', true)->orderBy('nom')->get(),
            'avions'   => Flotte::deLaCompagnie($this->compagnieId())->where('statut', 'Actif')->orderBy('modele')->get(),
            'statuts'  => self::STATUTS,
        ];
    }

    private function valider(Request $request, ?Vol $vol = null): array
    {
        $agenceId = $this->compagnieId();

        $request->merge(['numero_vol' => strtoupper(preg_replace('/\s+/', '', (string) $request->numero_vol))]);

        $v = $request->validate([
            'numero_vol'       => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9]{2}-?\d{1,4}[A-Z]?$/',
                Rule::unique('vols', 'numero_vol')->where('agence_id', $agenceId)->whereNull('deleted_at')->ignore($vol?->id)],
            'aeroport_depart'  => 'required|integer|exists:villes,id',
            'aeroport_arrivee' => 'required|integer|exists:villes,id|different:aeroport_depart',
            'flotte_id'        => ['nullable', 'integer', Rule::exists('flottes', 'id')->where('agence_id', $agenceId)],
            'date_depart'      => 'required|date',
            'heure_depart'     => 'required|date_format:H:i',
            'date_arrivee'     => 'required|date',
            'heure_arrivee'    => 'required|date_format:H:i',
            'prix'             => 'required|numeric|min:1|max:10000000',
            'places_totales'   => 'required|integer|min:1|max:900',
            'statut'           => ['required', Rule::in(array_keys(self::STATUTS))],
            'type_vol'         => 'required|in:direct,escale',
            'escales'          => 'nullable|string|max:255',
        ], [
            'numero_vol.regex'            => 'Numéro de vol attendu : code de la compagnie puis chiffres (ex. CA101 ou CA-101).',
            'numero_vol.unique'           => 'Ce numéro de vol existe déjà dans votre compagnie.',
            'aeroport_arrivee.different'  => 'L\'aéroport d\'arrivée doit être différent de celui de départ.',
            'prix.min'                    => 'Le prix doit être d\'au moins 1 FCFA.',
            'places_totales.min'          => 'Le vol doit proposer au moins une place.',
        ]);

        $depart  = Carbon::parse($v['date_depart'] . ' ' . $v['heure_depart']);
        $arrivee = Carbon::parse($v['date_arrivee'] . ' ' . $v['heure_arrivee']);
        if ($arrivee->lte($depart)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['date_arrivee' => 'L\'arrivée doit être après le départ.']);
        }
        if (! $vol && $depart->isPast()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['date_depart' => 'Le départ d\'un nouveau vol doit être dans le futur.']);
        }

        $avion = isset($v['flotte_id']) ? Flotte::find($v['flotte_id']) : null;
        if ($avion && $v['places_totales'] > $avion->capacite_places) {
            throw \Illuminate\Validation\ValidationException::withMessages(['places_totales' => "Cet avion a {$avion->capacite_places} sièges : la capacité mise en vente ne peut pas dépasser ce nombre."]);
        }

        $aDepart  = Ville::find($v['aeroport_depart']);
        $aArrivee = Ville::find($v['aeroport_arrivee']);

        return [
            'numero_vol'         => $v['numero_vol'],
            'compagnie'          => $this->compagnie()->nom_agence,
            'flotte_id'          => $avion?->id,
            'avion'              => $avion ? trim(($avion->modele ?: $avion->nom_appareil) . ($avion->immatriculation ? ' (' . $avion->immatriculation . ')' : '')) : null,
            'origine'            => 'Aéroport de ' . $aDepart->nom,
            'destination'        => 'Aéroport de ' . $aArrivee->nom,
            'ville_depart'       => $aDepart->nom,
            'ville_arrivee'      => $aArrivee->nom,
            'ville_depart_iata'  => $aDepart->code_iata,
            'ville_arrivee_iata' => $aArrivee->code_iata,
            'date_depart'        => $depart,
            'date_arrivee'       => $arrivee,
            'duree_minutes'      => (int) $depart->diffInMinutes($arrivee),
            'prix'               => $v['prix'],
            'places_totales'     => (int) $v['places_totales'],
            'statut'             => $v['statut'],
            'type_vol'           => $v['type_vol'],
            'escales'            => $v['type_vol'] === 'escale' && filled($v['escales'] ?? null)
                ? array_values(array_filter(array_map('trim', explode(',', $v['escales']))))
                : [],
        ];
    }

    private function numeroLibre(string $numero): string
    {
        $base = preg_replace('/[A-Z]$/', '', $numero);
        foreach (range('B', 'Z') as $lettre) {
            if (! Vol::deLaCompagnie($this->compagnieId())->where('numero_vol', $base . $lettre)->exists()) {
                return $base . $lettre;
            }
        }

        return $base . random_int(10, 99);
    }
}
