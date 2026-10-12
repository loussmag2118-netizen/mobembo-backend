@extends('layouts.admin')

@section('content')
<div class="space-y-7" x-data="{ searchQuery: '', filterStatut: 'all' }">

    {{-- EN-TÊTE AVEC TITRE ET ACTIONS --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-emerald-100 text-emerald-600 border border-emerald-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-users-gear"></i>
                </span>
                <span>Agents de Guichet & Contrôleurs</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Gérez les comptes opérationnels, codes d'accès et affectations aux gares.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.agents.trashed') }}"
               class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 px-4 py-3 rounded-2xl text-sm font-bold transition-all shadow-sm border border-slate-200/80">
                <i class="fa-solid fa-trash-can text-slate-400"></i>
                <span>Corbeille</span>
            </a>

            <a href="{{ route('admin.agents.create') }}"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white px-5 py-3 rounded-2xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98]">
                <i class="fa-solid fa-user-plus text-emerald-400 text-base"></i>
                <span>Ajouter un agent</span>
            </a>
        </div>
    </div>

    {{-- CONTENEUR TABLEAU AVEC RECHERCHE ET FILTRES --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        {{-- BARRE DE RECHERCHE ET FILTRES RAPIDES --}}
        <div class="p-6 sm:px-8 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            
            <div class="relative w-full md:w-96">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400"></i>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Rechercher par nom, téléphone, gare, ville..." 
                       class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
            </div>

            {{-- Filtres statut --}}
            <div class="flex items-center gap-2">
                <button type="button" 
                        @click="filterStatut = 'all'" 
                        :class="filterStatut === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold transition-colors">
                    Tous ({{ $agents->count() }})
                </button>
                <button type="button" 
                        @click="filterStatut = 'actif'" 
                        :class="filterStatut === 'actif' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold transition-colors">
                    <i class="fa-solid fa-circle-check mr-1"></i> Actifs
                </button>
                <button type="button" 
                        @click="filterStatut = 'inactif'" 
                        :class="filterStatut === 'inactif' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100'"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold transition-colors">
                    <i class="fa-solid fa-ban mr-1"></i> Suspendus
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-black uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-8">Agent</th>
                        <th class="py-4 px-8">Gare / Station Rattachée</th>
                        <th class="py-4 px-8">Téléphone</th>
                        <th class="py-4 px-8">Code d'accès</th>
                        <th class="py-4 px-8">Statut</th>
                        <th class="py-4 px-8 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                @forelse($agents as $agent)
                    @php
                        $nomComplet = ($agent->prenom ?? '') . ' ' . ($agent->nom ?? '');
                        $monogram = strtoupper(substr($agent->nom ?? 'A', 0, 1) . substr($agent->prenom ?? '', 0, 1));
                        $isActif = strtolower($agent->statut ?? '') === 'actif';
                    @endphp
                    <tr x-show="(searchQuery === '' || '{{ strtolower($nomComplet) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($agent->telephone ?? '') }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($agent->gare->nom_gare ?? '') }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($agent->gare->ville ?? '') }}'.includes(searchQuery.toLowerCase())) && (filterStatut === 'all' || (filterStatut === 'actif' && {{ $isActif ? 'true' : 'false' }}) || (filterStatut === 'inactif' && {{ !$isActif ? 'true' : 'false' }}))"
                        class="hover:bg-emerald-50/20 transition-colors group">
                        
                        {{-- Agent --}}
                        <td class="py-5 px-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-100 to-teal-100 text-emerald-700 font-black text-sm flex items-center justify-center shrink-0 border border-emerald-200/60 group-hover:from-emerald-200 transition-all">
                                    {{ $monogram ?: 'AG' }}
                                </div>
                                <div>
                                    <div class="font-extrabold text-slate-900 text-base">{{ $agent->prenom }} {{ $agent->nom }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5 font-semibold">
                                        Inscrit le {{ $agent->created_at ? $agent->created_at->format('d/m/Y') : '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Gare --}}
                        <td class="py-5 px-8">
                            @if($agent->gare)
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                                    <i class="fa-solid fa-map-pin text-xs"></i>
                                    {{ $agent->gare->nom_gare }}
                                </span>
                                <div class="mt-1 pl-1 text-xs font-semibold text-slate-500">
                                    <i class="fa-solid fa-city text-[10px]"></i> {{ $agent->gare->ville }}
                                </div>
                            @else
                                <span class="text-sm text-slate-400 italic">Aucune gare assignée</span>
                            @endif
                        </td>

                        {{-- Téléphone --}}
                        <td class="py-5 px-8 font-mono text-sm font-bold text-slate-700">
                            {{ $agent->telephone }}
                        </td>

                        {{-- Code d'accès --}}
                        <td class="py-5 px-8">
                            @if($agent->code_acces_agent)
                                <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-800 font-mono text-sm font-bold border border-slate-200">
                                    {{ $agent->code_acces_agent }}
                                </span>
                            @else
                                <span class="text-slate-400 text-sm">—</span>
                            @endif
                        </td>

                        {{-- Statut --}}
                        <td class="py-5 px-8">
                            @if($isActif)
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Actif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-red-50 text-red-700 border border-red-200/60">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                    Suspendu
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="py-5 px-8 text-right">
                            <div class="flex justify-end items-center gap-2">
                                <a href="{{ route('admin.agents.historique', $agent) }}"
                                   class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-xl transition-colors border border-slate-200">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    <span>Historique</span>
                                </a>

                                <a href="{{ route('admin.agents.edit', $agent) }}"
                                   class="inline-flex items-center gap-2 text-sm font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition-colors border border-indigo-100">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Modifier</span>
                                </a>

                                <form action="{{ route('admin.agents.destroy', $agent) }}"
                                      method="POST"
                                      onsubmit="return confirm('Voulez-vous vraiment supprimer cet agent ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 text-sm font-bold text-red-600 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-xl transition-colors border border-red-100">
                                        <i class="fa-solid fa-trash-can"></i>
                                        <span>Supprimer</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-20 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-20 h-20 rounded-3xl bg-slate-100 flex items-center justify-center">
                                    <i class="fa-solid fa-users text-4xl text-slate-300"></i>
                                </div>
                                <div>
                                    <p class="text-base font-bold text-slate-600">Aucun agent enregistré pour le moment.</p>
                                    <p class="text-sm text-slate-400 mt-1">Commencez par créer votre équipe de guichetiers.</p>
                                </div>
                                <a href="{{ route('admin.agents.create') }}"
                                   class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-2xl text-sm font-bold shadow-md">
                                    <i class="fa-solid fa-user-plus text-emerald-400"></i>
                                    Ajouter votre premier agent
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
