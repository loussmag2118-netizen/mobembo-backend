{{-- Tableau d'entrées du journal d'audit ($entrees, $nomsAgences) --}}
@php
    $libelles = [
        'connexion.reussie'          => ['Connexion', 'bg-emerald-50 text-emerald-700'],
        'connexion.echouee'          => ['Connexion refusée', 'bg-rose-50 text-rose-700'],
        'paiement.initie'            => ['Paiement lancé', 'bg-sky-50 text-sky-700'],
        'paiement.confirme'          => ['Paiement confirmé', 'bg-emerald-50 text-emerald-700'],
        'paiement.echoue'            => ['Paiement échoué', 'bg-slate-100 text-slate-600'],
        'paiement.test'              => ['Paiement de test', 'bg-orange-50 text-orange-700'],
        'paiement.a_rembourser'      => ['À rembourser', 'bg-rose-50 text-rose-700'],
        'billet.rembourse'           => ['Billet remboursé', 'bg-sky-50 text-sky-700'],
        'reservation.annulee_client' => ['Réservation annulée', 'bg-slate-100 text-slate-600'],
    ];
@endphp
<div class="overflow-x-auto">
    <table class="w-full text-left">
        <thead>
            <tr class="text-xs font-black uppercase tracking-wider text-slate-500 border-b border-slate-100">
                <th class="py-3 px-4">Date</th>
                <th class="py-3 px-4">Action</th>
                <th class="py-3 px-4">Agence</th>
                <th class="py-3 px-4">Auteur</th>
                <th class="py-3 px-4">Détails</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($entrees as $e)
                @php
                    [$libelle, $classe] = $libelles[$e->action] ?? [ucfirst(str_replace(['admin.', '.', '_'], ['', ' · ', ' '], $e->action)), 'bg-slate-100 text-slate-700'];
                    $details = $e->details ? json_decode($e->details, true) : [];
                @endphp
                <tr class="text-sm">
                    <td class="py-3 px-4 whitespace-nowrap text-slate-500 font-semibold">{{ \Illuminate\Support\Carbon::parse($e->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="py-3 px-4"><span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $classe }}">{{ $libelle }}</span></td>
                    <td class="py-3 px-4 font-semibold text-slate-700">{{ $e->agence_id ? ($nomsAgences[$e->agence_id] ?? '#' . $e->agence_id) : '—' }}</td>
                    <td class="py-3 px-4 text-slate-600">{{ $e->acteur_type === 'systeme' ? 'Système' : ucfirst($e->acteur_type) . ($e->acteur_id ? ' #' . $e->acteur_id : '') }}</td>
                    <td class="py-3 px-4 text-xs text-slate-500 max-w-md">
                        @foreach(array_slice((array) $details, 0, 4, true) as $cle => $valeur)
                            <span class="inline-block mr-2"><b>{{ $cle }}</b> : {{ is_scalar($valeur) ? \Illuminate\Support\Str::limit((string) $valeur, 40) : \Illuminate\Support\Str::limit(json_encode($valeur, JSON_UNESCAPED_UNICODE), 60) }}</span>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-10 text-center text-slate-400 font-semibold">Aucune entrée pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
