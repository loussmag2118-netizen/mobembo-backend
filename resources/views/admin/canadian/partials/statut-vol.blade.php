@php
    $styles = [
        'programme' => ['Programmé', 'bg-emerald-50 text-emerald-700'],
        'en_vol'    => ['En vol', 'bg-sky-50 text-sky-700'],
        'atterri'   => ['Atterri', 'bg-slate-100 text-slate-600'],
        'annule'    => ['Annulé', 'bg-rose-50 text-rose-700'],
    ];
    [$libelle, $classe] = $styles[$statut] ?? [$statut, 'bg-slate-100 text-slate-600'];
@endphp
<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold {{ $classe }}">{{ $libelle }}</span>
