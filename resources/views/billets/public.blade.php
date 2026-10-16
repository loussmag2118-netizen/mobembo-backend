<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $ticket ? 'Billet ' . $ticket->reference_ticket . ' — ' . ($ticket->agence->nom_agence ?? 'Mobembo Line') : 'Billet introuvable' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-slate-100 flex items-start sm:items-center justify-center p-4 sm:p-8">
@if(! $ticket)
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl p-8 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-2xl"><i class="fa-solid fa-ticket"></i></div>
        <h1 class="mt-4 text-xl font-extrabold text-slate-900">Billet introuvable</h1>
        <p class="mt-2 text-sm text-slate-500">Ce QR code ne correspond à aucun billet Mobembo Line.</p>
    </div>
@else
    @php
        $agence  = $ticket->agence;
        $couleur = $agence?->couleur ?: '#1B5E20';
        $voyage  = $ticket->planificationVoyage;
        $trajet  = $voyage?->trajet;
        $heure   = $ticket->horaire ? substr($ticket->horaire, 0, 5) : ($voyage?->vagueDepart?->heure_depart ? substr($voyage->vagueDepart->heure_depart, 0, 5) : null);
        [$titreStatut, $texteStatut, $teinte] = $statut;
        $classes = [
            'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'orange'  => 'bg-orange-50 text-orange-700 border-orange-200',
            'amber'   => 'bg-amber-50 text-amber-700 border-amber-200',
            'slate'   => 'bg-slate-100 text-slate-700 border-slate-200',
            'rose'    => 'bg-rose-50 text-rose-700 border-rose-200',
        ][$teinte];
        $icone = ['emerald' => 'fa-circle-check', 'orange' => 'fa-flask', 'amber' => 'fa-hourglass-half', 'slate' => 'fa-circle-info', 'rose' => 'fa-circle-xmark'][$teinte];
    @endphp
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
            {{-- En-tête aux couleurs de l'agence --}}
            <div class="relative px-6 pt-8 pb-16 text-center text-white" style="background: {{ $couleur }}">
                <p class="text-xs font-bold uppercase tracking-[0.2em] opacity-80">Billet de bus</p>
                <h1 class="mt-1 text-2xl font-extrabold">{{ $agence->nom_agence ?? 'Mobembo Line' }}</h1>
            </div>
            <div class="-mt-12 flex justify-center">
                <div class="w-24 h-24 rounded-3xl bg-white border-4 border-white shadow-lg overflow-hidden">
                    <img src="{{ $agence ? $agence->logoPublicUrl() : asset('favicon-192.png') }}" alt="Logo {{ $agence->nom_agence ?? '' }}" class="w-full h-full object-cover">
                </div>
            </div>

            <div class="px-6 pt-5 pb-7">
                {{-- Statut --}}
                <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 {{ $classes }}">
                    <i class="fa-solid {{ $icone }} text-xl"></i>
                    <div>
                        <p class="font-extrabold">{{ $titreStatut }}</p>
                        <p class="text-xs font-semibold opacity-80">{{ $texteStatut }}</p>
                    </div>
                </div>

                {{-- Trajet --}}
                <div class="mt-6 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Départ</p>
                        <p class="text-lg font-extrabold text-slate-900">{{ $ticket->ville_depart ?? $trajet?->ville_depart }}</p>
                        <p class="text-xs text-slate-500 font-semibold">{{ $trajet?->gareDepart?->nom_gare }}</p>
                    </div>
                    <i class="fa-solid fa-arrow-right-long text-slate-300 text-xl"></i>
                    <div class="text-right">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Arrivée</p>
                        <p class="text-lg font-extrabold text-slate-900">{{ $ticket->ville_arrivee ?? $trajet?->ville_arrivee }}</p>
                        <p class="text-xs text-slate-500 font-semibold">{{ $trajet?->gareArrivee?->nom_gare }}</p>
                    </div>
                </div>

                {{-- Détails --}}
                <dl class="mt-6 grid grid-cols-2 gap-x-4 gap-y-4 rounded-2xl bg-slate-50 p-4">
                    <div><dt class="text-xs font-bold text-slate-400">Date</dt><dd class="font-extrabold text-slate-900">{{ $voyage?->date_voyage_carbon?->translatedFormat('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400">Heure de départ</dt><dd class="font-extrabold text-slate-900">{{ $heure ?? '—' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400">Passager</dt><dd class="font-extrabold text-slate-900">{{ $passager }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400">Catégorie</dt><dd class="font-extrabold text-slate-900">{{ ($ticket->categorie_passager ?? 'Adulte') === 'Enfant' ? 'Enfant (' . $ticket->age_passager . ' ans)' : 'Adulte' }}</dd></div>
                    @if($ticket->bebe_nom)
                        <div class="col-span-2 rounded-xl bg-pink-50 px-3 py-2"><dt class="text-xs font-bold text-pink-400">Bébé sur les genoux (assurance payée)</dt><dd class="font-extrabold text-slate-900">{{ \Illuminate\Support\Str::after($ticket->libelleBebe(), 'Bébé : ') }}</dd></div>
                    @endif
                    @if($ticket->numero_siege)
                        <div><dt class="text-xs font-bold text-slate-400">Siège</dt><dd class="font-extrabold text-slate-900">{{ $ticket->numero_siege }}</dd></div>
                    @endif
                    @if($ticket->numero_bus)
                        <div><dt class="text-xs font-bold text-slate-400">Bus</dt><dd class="font-extrabold text-slate-900">{{ $ticket->numero_bus }}</dd></div>
                    @endif
                    <div class="col-span-2"><dt class="text-xs font-bold text-slate-400">Référence</dt><dd class="font-extrabold text-slate-900 tracking-wide">{{ $ticket->reference_ticket }}</dd></div>
                </dl>

                <p class="mt-5 text-center text-xs text-slate-400 font-semibold">
                    L'embarquement est validé par l'agent de {{ $agence->nom_agence ?? "l'agence" }}.
                </p>
            </div>
        </div>
        <p class="mt-4 text-center text-xs text-slate-400 font-semibold">Mobembo Line — vente et achat de tickets</p>
    </div>
@endif
</body>
</html>
