@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Rapports & Statistiques</h1>
            <p class="text-sm text-slate-500 mt-1">
                Analysez les performances de Canadian Airways
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('canadian.rapports.generer') }}"
               class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-[#C62828] to-[#8E0000] hover:from-[#E53935] hover:to-[#C62828] text-white font-semibold rounded-xl shadow-lg shadow-red-500/20 transition">
                <i class="fa-solid fa-chart-line mr-2"></i> Générer un rapport
            </a>
            <a href="{{ route('canadian.rapports.export') }}"
               class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold rounded-xl shadow-sm transition">
                <i class="fa-solid fa-download mr-2"></i> Exporter
            </a>
        </div>
    </div>

    {{-- Message succès --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Message erreur --}}
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Filtres de période --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <form method="GET" action="{{ route('canadian.rapports.index') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Date de début</label>
                <input type="date" name="date_debut" value="{{ request('date_debut') }}"
                       class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#C62828] focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Date de fin</label>
                <input type="date" name="date_fin" value="{{ request('date_fin') }}"
                       class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#C62828] focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Type</label>
                <select name="type"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                    <option value="global" {{ request('type') == 'global' ? 'selected' : '' }}>Global</option>
                    <option value="vols" {{ request('type') == 'vols' ? 'selected' : '' }}>Vols</option>
                    <option value="ventes" {{ request('type') == 'ventes' ? 'selected' : '' }}>Ventes</option>
                    <option value="clients" {{ request('type') == 'clients' ? 'selected' : '' }}>Clients</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="w-full bg-[#C62828] hover:bg-[#8E0000] text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    <i class="fa-solid fa-filter mr-1"></i> Appliquer
                </button>
            </div>
        </form>
    </div>

    {{-- KPI principaux --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-6 text-white shadow-lg shadow-emerald-500/20">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg">
                    <i class="fa-solid fa-dollar-sign text-lg"></i>
                </div>
                <i class="fa-solid fa-arrow-trend-up text-white/40"></i>
            </div>
            <p class="text-xs uppercase tracking-wider text-white/80">Chiffre d'affaires</p>
            <p class="text-2xl font-black mt-1">{{ number_format($chiffreAffaires ?? 0, 0, ',', ' ') }}</p>
            <p class="text-xs text-white/70 mt-1">FCFA</p>
        </div>

        <div class="bg-gradient-to-br from-[#C62828] to-[#8E0000] rounded-2xl p-6 text-white shadow-lg shadow-red-500/20">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg">
                    <i class="fa-solid fa-ticket text-lg"></i>
                </div>
                <i class="fa-solid fa-plane text-white/40"></i>
            </div>
            <p class="text-xs uppercase tracking-wider text-white/80">Réservations</p>
            <p class="text-2xl font-black mt-1">{{ $totalReservations ?? 0 }}</p>
            <p class="text-xs text-white/70 mt-1">Billets vendus</p>
        </div>

        <div class="bg-gradient-to-br from-sky-500 to-sky-700 rounded-2xl p-6 text-white shadow-lg shadow-sky-500/20">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
                <i class="fa-solid fa-user-plus text-white/40"></i>
            </div>
            <p class="text-xs uppercase tracking-wider text-white/80">Clients</p>
            <p class="text-2xl font-black mt-1">{{ $totalClients ?? 0 }}</p>
            <p class="text-xs text-white/70 mt-1">Inscrits</p>
        </div>

        <div class="bg-gradient-to-br from-amber-500 to-amber-700 rounded-2xl p-6 text-white shadow-lg shadow-amber-500/20">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg">
                    <i class="fa-solid fa-chart-simple text-lg"></i>
                </div>
                <i class="fa-solid fa-percent text-white/40"></i>
            </div>
            <p class="text-xs uppercase tracking-wider text-white/80">Taux de remplissage</p>
            <p class="text-2xl font-black mt-1">{{ round($tauxRemplissage ?? 0) }}%</p>
            <p class="text-xs text-white/70 mt-1">Moyen</p>
        </div>
    </div>

    {{-- Top vols + Répartition --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Vols les plus rentables --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-trophy text-amber-500"></i>
                    Vols les plus rentables
                </h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($topVols as $vol)
                    <div class="p-5 flex items-center gap-4 hover:bg-slate-50 transition">
                        <div class="w-10 h-10 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center font-bold text-sm">
                            {{ $loop->iteration }}
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-slate-900 text-sm">
                                {{ $vol->numero_vol ?? '—' }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ $vol->aeroport_depart ?? '' }} → {{ $vol->aeroport_arrivee ?? '' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-emerald-600">
                                {{ number_format($vol->revenus ?? 0, 0, ',', ' ') }} FCFA
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ $vol->reservations_count ?? 0 }} billets
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 text-sm">
                        Aucune donnée disponible
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Répartition par statut --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-[#C62828]"></i>
                    Répartition des réservations
                </h2>
            </div>
            <div class="p-6 space-y-4">
                @php
                    $total = ($confirmees ?? 0) + ($enAttente ?? 0) + ($annulees ?? 0) + ($remboursees ?? 0);
                @endphp

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-700">Confirmées</span>
                        <span class="text-sm font-bold text-emerald-600">{{ $confirmees ?? 0 }}</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-600"
                             style="width: {{ $total > 0 ? (($confirmees ?? 0) / $total) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-700">En attente</span>
                        <span class="text-sm font-bold text-amber-600">{{ $enAttente ?? 0 }}</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-amber-500 to-amber-600"
                             style="width: {{ $total > 0 ? (($enAttente ?? 0) / $total) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-700">Annulées</span>
                        <span class="text-sm font-bold text-[#C62828]">{{ $annulees ?? 0 }}</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#C62828] to-[#8E0000]"
                             style="width: {{ $total > 0 ? (($annulees ?? 0) / $total) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-700">Remboursées</span>
                        <span class="text-sm font-bold text-slate-600">{{ $remboursees ?? 0 }}</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-slate-400 to-slate-600"
                             style="width: {{ $total > 0 ? (($remboursees ?? 0) / $total) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection