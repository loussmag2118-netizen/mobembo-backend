@extends('layouts.admin')

@section('title', 'Vol ' . $vol->numero_vol)
@section('header_icon', 'fa-plane-departure')
@section('header_title', 'Vol ' . $vol->numero_vol)
@section('header_subtitle', $vol->ville_depart . ' → ' . $vol->ville_arrivee . ' · ' . $vol->date_depart?->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH:mm'))

@section('content')
@php
    $total = $vol->places_totales ?? $vol->places_disponibles;
    $taux = $total > 0 ? round($occupees / $total * 100) : 0;
@endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('canadian.vols.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-slate-900"><i class="fa-solid fa-arrow-left"></i> Tous les vols</a>
        <div class="flex gap-2">
            @include('admin.canadian.partials.statut-vol', ['statut' => $vol->statut])
            <a href="{{ route('canadian.vols.edit', $vol->id) }}" class="inline-flex items-center gap-2 bg-[#124231] hover:bg-[#0c2f23] text-white text-sm font-bold py-2 px-4 rounded-xl"><i class="fa-solid fa-pen"></i> Modifier</a>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <div>
                <p class="text-4xl font-black text-slate-900 font-mono">{{ $vol->ville_depart_iata ?: '—' }}</p>
                <p class="font-bold text-slate-700">{{ $vol->ville_depart }}</p>
                <p class="text-sm text-slate-500">{{ $vol->date_depart?->format('d/m/Y · H:i') }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $vol->type_vol === 'escale' ? 'Avec escale' : 'Vol direct' }} · {{ $vol->duree_formatee }}</p>
                <div class="flex items-center gap-2 my-2"><span class="flex-1 h-px bg-slate-200"></span><i class="fa-solid fa-plane text-slate-400"></i><span class="flex-1 h-px bg-slate-200"></span></div>
                @if($vol->escales)<p class="text-xs text-slate-500">via {{ implode(', ', $vol->escales) }}</p>@endif
                <p class="text-xs text-slate-400">{{ $vol->avion ?: 'Avion non précisé' }}</p>
            </div>
            <div class="md:text-right">
                <p class="text-4xl font-black text-slate-900 font-mono">{{ $vol->ville_arrivee_iata ?: '—' }}</p>
                <p class="font-bold text-slate-700">{{ $vol->ville_arrivee }}</p>
                <p class="text-sm text-slate-500">{{ $vol->date_arrivee?->format('d/m/Y · H:i') }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.canadian.partials.stat', ['titre' => 'Prix', 'valeur' => number_format($vol->prix, 0, ',', ' ') . ' F', 'icone' => 'fa-tag', 'teinte' => 'slate'])
        @include('admin.canadian.partials.stat', ['titre' => 'Places réservées', 'valeur' => $occupees . ' / ' . $total, 'icone' => 'fa-users', 'teinte' => 'sky'])
        @include('admin.canadian.partials.stat', ['titre' => 'Places restantes', 'valeur' => $vol->places_disponibles, 'icone' => 'fa-chair', 'teinte' => 'emerald'])
        @include('admin.canadian.partials.stat', ['titre' => 'Remplissage', 'valeur' => $taux . ' %', 'icone' => 'fa-chart-simple', 'teinte' => 'amber'])
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 sm:px-6 border-b border-slate-100">
            <h2 class="text-base font-black text-slate-900">Réservations sur ce vol ({{ $reservations->total() }})</h2>
        </div>
        @if($reservations->isEmpty())
            @include('admin.canadian.partials.vide', ['icone' => 'fa-ticket', 'titre' => 'Aucune réservation', 'texte' => 'Les réservations faites depuis l\'application apparaîtront ici.'])
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-6">Référence</th><th class="py-3 px-4">Client</th><th class="py-3 px-4">Places</th><th class="py-3 px-4">Montant</th><th class="py-3 px-4">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($reservations as $r)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3 px-6"><a href="{{ route('canadian.reservations.show', $r->id) }}" class="font-mono font-bold text-slate-900 hover:underline">{{ $r->reference }}</a>@if($r->code_pnr)<p class="text-xs text-slate-400 font-mono">PNR {{ $r->code_pnr }}</p>@endif</td>
                                <td class="py-3 px-4"><p class="font-semibold text-slate-800">{{ $r->nom_passager ?: ($r->canadianUser?->name ?? '—') }}</p><p class="text-xs text-slate-400">{{ $r->telephone }}</p></td>
                                <td class="py-3 px-4 font-bold">{{ $r->nombre_places }}</td>
                                <td class="py-3 px-4 font-bold whitespace-nowrap">{{ number_format($r->montant_total, 0, ',', ' ') }} F</td>
                                <td class="py-3 px-4">@include('admin.canadian.partials.statut-reservation', ['statut' => $r->statut])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($reservations->hasPages())<div class="px-6 py-4 border-t border-slate-100">{{ $reservations->links() }}</div>@endif
        @endif
    </div>
</div>
@endsection
