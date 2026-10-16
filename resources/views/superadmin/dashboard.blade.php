@extends('layouts.admin')

@section('title', 'Tableau de bord national')
@section('header_icon', 'fa-gauge-high')
@section('header_title', 'Tableau de bord national')
@section('header_subtitle', "Activité de toutes les agences du réseau Mobembo Line")

@section('content')
<div class="space-y-7">

    {{-- Indicateurs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Agences actives', $stats['agences_actives'] . ' / ' . $stats['agences'], 'fa-building-shield', 'emerald'],
            ['Départs à venir', $stats['departs_a_venir'], 'fa-calendar-check', 'sky'],
            ['Billets vendus', number_format($stats['billets'], 0, ',', ' ') . " (aujourd'hui : " . $stats['billets_jour'] . ')', 'fa-ticket', 'indigo'],
            ["Chiffre d'affaires", number_format($stats['chiffre'], 0, ',', ' ') . ' FCFA', 'fa-coins', 'amber'],
        ] as [$titre, $valeur, $icone, $couleur])
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $titre }}</p>
                    <span class="w-9 h-9 rounded-xl bg-{{ $couleur }}-50 text-{{ $couleur }}-600 flex items-center justify-center"><i class="fa-solid {{ $icone }}"></i></span>
                </div>
                <p class="mt-3 text-xl sm:text-2xl font-black text-slate-900">{{ $valeur }}</p>
            </div>
        @endforeach
    </div>

    @if($stats['billets_test'] > 0 || $stats['a_rembourser'] > 0)
        <div class="flex flex-col gap-2">
            @if($stats['billets_test'] > 0)
                <div class="flex items-center gap-3 rounded-2xl bg-orange-50 border border-orange-200 px-4 py-3 text-sm text-orange-800 font-semibold">
                    <i class="fa-solid fa-flask"></i>
                    {{ $stats['billets_test'] }} billet(s) de TEST émis (paiements simulés), exclus des ventes et du chiffre d'affaires.
                </div>
            @endif
            @if($stats['a_rembourser'] > 0)
                <div class="flex items-center gap-3 rounded-2xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800 font-semibold">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    {{ $stats['a_rembourser'] }} paiement(s) reçu(s) après expiration de la réservation : remboursement à effectuer (voir le journal).
                </div>
            @endif
        </div>
    @endif

    {{-- Agences --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-lg font-black text-slate-900">Activité par agence</h2>
            <a href="{{ route('superadmin.agences.index') }}" class="text-sm font-bold text-emerald-700 hover:underline">Gérer les agences</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-xs font-black uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <th class="py-3 px-6">Partenaire</th>
                        <th class="py-3 px-4">Statut</th>
                        <th class="py-3 px-4 text-right">Agents</th>
                        <th class="py-3 px-4 text-right">Bus</th>
                        <th class="py-3 px-4 text-right">Départs à venir</th>
                        <th class="py-3 px-4 text-right">Billets vendus</th>
                        <th class="py-3 px-6 text-right">Chiffre d'affaires</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($parAgence as $ligne)
                        <tr class="text-sm hover:bg-slate-50/60">
                            <td class="py-3.5 px-6">
                                <a href="{{ route('superadmin.agences.show', $ligne->agence) }}" class="font-extrabold text-slate-900 hover:text-emerald-700">{{ $ligne->agence->nom_agence }}</a>
                                <div class="text-xs text-slate-400 font-semibold">
                                    @if($ligne->agence->estAerien())
                                        <span class="text-sky-700"><i class="fa-solid fa-plane text-[10px]"></i> Compagnie aérienne</span> · départs = vols, billets = places payées
                                    @else
                                        <i class="fa-solid fa-bus text-[10px]"></i> Enfant ≤ {{ $ligne->agence->age_max_enfant }} ans{{ $ligne->agence->choix_siege ? ' · choix du siège' : '' }}
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $ligne->agence->statut === 'Actif' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $ligne->agence->statut }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-slate-700">{{ $ligne->agents }}</td>
                            <td class="py-3.5 px-4 text-right font-bold text-slate-700">{{ $ligne->bus }}</td>
                            <td class="py-3.5 px-4 text-right font-bold text-slate-700">{{ $ligne->departs_a_venir }}</td>
                            <td class="py-3.5 px-4 text-right font-bold text-slate-700">{{ number_format($ligne->billets, 0, ',', ' ') }}</td>
                            <td class="py-3.5 px-6 text-right font-black text-slate-900">{{ number_format($ligne->chiffre, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Dernières actions --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-lg font-black text-slate-900">Dernières actions sensibles</h2>
            <a href="{{ route('superadmin.journal') }}" class="text-sm font-bold text-emerald-700 hover:underline">Tout le journal</a>
        </div>
        @include('superadmin.partials.journal-table', ['entrees' => $journal, 'nomsAgences' => $nomsAgences])
    </div>
</div>
@endsection
