@extends('layouts.admin')

@section('title', 'Rapports')
@section('header_icon', 'fa-chart-pie')
@section('header_title', 'Rapports')
@section('header_subtitle', 'Activité du ' . $rapport['du']->format('d/m/Y') . ' au ' . $rapport['au']->format('d/m/Y'))

@section('content')
@php $max = max(1, (float) $rapport['par_jour']->max()); @endphp
<div class="space-y-6">
    <form method="GET" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 flex flex-col sm:flex-row sm:items-end gap-3">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Du</label>
            <input type="date" name="du" value="{{ $rapport['du']->format('Y-m-d') }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Au</label>
            <input type="date" name="au" value="{{ $rapport['au']->format('Y-m-d') }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
        </div>
        <button class="bg-[#124231] hover:bg-[#0c2f23] text-white text-sm font-bold py-2.5 px-5 rounded-xl">Afficher</button>
        <a href="{{ route('canadian.rapports.export', ['du' => $rapport['du']->format('Y-m-d'), 'au' => $rapport['au']->format('Y-m-d')]) }}" class="sm:ml-auto inline-flex items-center justify-center gap-2 text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 py-2.5 px-5 rounded-xl"><i class="fa-solid fa-file-csv"></i> Exporter les vols (Excel)</a>
    </form>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.canadian.partials.stat', ['titre' => 'Chiffre d\'affaires', 'valeur' => number_format($rapport['chiffre'], 0, ',', ' ') . ' F', 'icone' => 'fa-sack-dollar', 'teinte' => 'emerald', 'note' => $rapport['paiements'] . ' paiement(s) confirmé(s)'])
        @include('admin.canadian.partials.stat', ['titre' => 'Places vendues', 'valeur' => $rapport['places'], 'icone' => 'fa-chair', 'teinte' => 'sky', 'note' => 'sur ' . $rapport['capacite'] . ' en vente'])
        @include('admin.canadian.partials.stat', ['titre' => 'Remplissage', 'valeur' => $rapport['remplissage'] . ' %', 'icone' => 'fa-chart-simple', 'teinte' => 'amber', 'note' => $rapport['vols'] . ' vol(s) dans la période'])
        @include('admin.canadian.partials.stat', ['titre' => 'Clients', 'valeur' => $rapport['clients'], 'icone' => 'fa-users', 'teinte' => 'slate', 'note' => $rapport['reservations'] . ' réservation(s) créée(s)'])
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
            <h2 class="text-base font-black text-slate-900 mb-4">Encaissements par jour</h2>
            @if($rapport['par_jour']->isEmpty())
                <p class="text-sm text-slate-400 py-10 text-center">Aucun encaissement sur la période.</p>
            @else
                <div class="space-y-2">
                    @foreach($rapport['par_jour'] as $jour => $total)
                        <div class="flex items-center gap-3 text-xs">
                            <span class="w-20 text-slate-500 font-semibold">{{ \Carbon\Carbon::parse($jour)->format('d/m') }}</span>
                            <div class="flex-1 h-5 bg-slate-100 rounded-lg overflow-hidden"><div class="h-full bg-[#124231] rounded-lg" style="width: {{ round($total / $max * 100) }}%"></div></div>
                            <span class="w-28 text-right font-bold text-slate-700">{{ number_format($total, 0, ',', ' ') }} F</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
            <h2 class="text-base font-black text-slate-900 mb-4">Réservations par statut</h2>
            @forelse($rapport['par_statut'] as $statut => $n)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                    @include('admin.canadian.partials.statut-reservation', ['statut' => $statut])
                    <span class="font-black text-slate-900">{{ $n }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">Aucune réservation sur la période.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 sm:px-6 border-b border-slate-100"><h2 class="text-base font-black text-slate-900">Meilleurs vols de la période</h2></div>
        @if($rapport['top_vols']->isEmpty())
            @include('admin.canadian.partials.vide', ['icone' => 'fa-plane', 'titre' => 'Aucun vol', 'texte' => 'Aucun vol programmé sur cette période.'])
        @else
            <table class="w-full text-left text-sm">
                <thead><tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500"><th class="py-3 px-6">Vol</th><th class="py-3 px-4">Départ</th><th class="py-3 px-4">Places vendues</th><th class="py-3 px-6 text-right">Recette</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rapport['top_vols'] as $v)
                        <tr>
                            <td class="py-3 px-6"><a href="{{ route('canadian.vols.show', $v->id) }}" class="font-mono font-bold hover:underline">{{ $v->numero_vol }}</a><p class="text-xs text-slate-400">{{ $v->ville_depart }} → {{ $v->ville_arrivee }}</p></td>
                            <td class="py-3 px-4">{{ $v->date_depart?->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 font-bold">{{ (int) $v->places_vendues }} / {{ $v->places_totales ?? $v->places_disponibles }}</td>
                            <td class="py-3 px-6 text-right font-black">{{ number_format((float) $v->recette, 0, ',', ' ') }} F</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
