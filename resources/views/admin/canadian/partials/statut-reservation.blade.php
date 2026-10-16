@php
    $styles = [
        'En_Attente'   => ['En attente de paiement', 'bg-amber-50 text-amber-700'],
        'Paye'         => ['Payée', 'bg-emerald-50 text-emerald-700'],
        'Confirmee'    => ['Embarquement confirmé', 'bg-sky-50 text-sky-700'],
        'Annulee'      => ['Annulée', 'bg-slate-100 text-slate-600'],
        'Expiree'      => ['Expirée', 'bg-slate-100 text-slate-500'],
        'Echouee'      => ['Paiement échoué', 'bg-rose-50 text-rose-700'],
        'A_Rembourser' => ['À rembourser', 'bg-orange-50 text-orange-700'],
        'Remboursee'   => ['Remboursée', 'bg-violet-50 text-violet-700'],
    ];
    [$libelle, $classe] = $styles[$statut] ?? [$statut, 'bg-slate-100 text-slate-600'];
@endphp
<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap {{ $classe }}">{{ $libelle }}</span>
