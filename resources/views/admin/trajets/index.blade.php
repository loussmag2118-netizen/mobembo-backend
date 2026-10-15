@extends('layouts.admin')

@section('content')
<div class="space-y-7" x-data="{ searchQuery: '' }">

    {{-- EN-TÊTE AVEC TITRE ET ACTIONS --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-teal-100 text-teal-600 border border-teal-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-route"></i>
                </span>
                <span>Lignes & Trajets de Voyage</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Configurez les gares de départ, d'arrivée, les vagues et les bus affectés.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.trajets.trashed') }}"
               class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 px-4 py-3 rounded-2xl text-sm font-bold transition-all shadow-sm border border-slate-200/80">
                <i class="fa-solid fa-trash-can text-slate-400"></i>
                <span>Corbeille</span>
            </a>

            <a href="{{ route('admin.trajets.create') }}"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white px-5 py-3 rounded-2xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98]">
                <i class="fa-solid fa-plus text-amber-400 text-base"></i>
                <span>Ajouter un trajet</span>
            </a>
        </div>
    </div>

    {{-- TABLEAU AVEC RECHERCHE INSTANTANÉE --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="p-6 sm:px-8 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="relative w-full sm:w-96">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400"></i>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Rechercher une ville, une gare..." 
                       class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none transition-all">
            </div>

            <span class="text-sm font-bold text-slate-500 bg-slate-100 px-4 py-2.5 rounded-2xl">
                <i class="fa-solid fa-route text-teal-500 mr-1.5"></i>
                {{ $trajets->total() }} ligne(s) configurée(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-black uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-8">Ligne & Gares</th>
                        <th class="py-4 px-8">Bus Assigné</th>
                        <th class="py-4 px-8">Horaires de Départ</th>
                        <th class="py-4 px-8 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                @forelse($trajets as $t)
                    @php
                        $nomLigne = ($t->gareDepart->nom_gare ?? $t->ville_depart ?? '') . ' ' . ($t->gareArrivee->nom_gare ?? $t->ville_arrivee ?? '');
                    @endphp
                    <tr x-show="searchQuery === '' || '{{ strtolower($nomLigne) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($t->bus->immatriculation ?? '') }}'.includes(searchQuery.toLowerCase())"
                        class="hover:bg-teal-50/20 transition-colors group">
                        
                        {{-- TRAJET --}}
                        <td class="py-5 px-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl shrink-0 border border-teal-100 group-hover:bg-teal-100 transition-colors">
                                    <i class="fa-solid fa-route"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 font-extrabold text-slate-900 text-base">
                                        <span>{{ $t->gareDepart->nom_gare ?? 'Gare Départ' }}</span>
                                        <i class="fa-solid fa-arrow-right text-sm text-teal-500"></i>
                                        <span>{{ $t->gareArrivee->nom_gare ?? 'Gare Arrivée' }}</span>
                                    </div>
                                    <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-1 font-semibold">
                                        <i class="fa-solid fa-location-dot text-xs"></i>
                                        <span>{{ $t->ville_depart ?? $t->gareDepart->ville ?? '?' }} → {{ $t->ville_arrivee ?? $t->gareArrivee->ville ?? '?' }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- BUS ASSIGNÉ --}}
                        <td class="py-5 px-8">
                            @if($t->bus)
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-slate-100 text-slate-800">
                                    <i class="fa-solid fa-bus text-slate-500 text-xs"></i>
                                    {{ $t->bus->immatriculation }}
                                </span>
                                @if($t->bus->modele)
                                    <div class="text-xs text-slate-400 mt-1 font-semibold">{{ $t->bus->modele }}</div>
                                @endif
                            @else
                                <span class="text-sm text-slate-400 italic">Aucun bus assigné</span>
                            @endif
                        </td>

                        {{-- HORAIRES --}}
                        <td class="py-5 px-8">
                            @if($t->vagues && $t->vagues->count() > 0)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($t->vagues as $v)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-bold bg-purple-50 text-purple-700 border border-purple-200/60 font-mono">
                                            <i class="fa-regular fa-clock text-xs"></i>
                                            {{ substr($v->heure_depart, 0, 5) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-sm text-slate-400 italic">Aucune vague</span>
                            @endif
                        </td>

                        {{-- ACTIONS --}}
                        <td class="py-5 px-8 text-right">
                            <div class="flex justify-end items-center gap-2">
                                <a href="{{ route('admin.trajets.edit', $t->id) }}"
                                   class="inline-flex items-center gap-2 text-sm font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition-colors border border-indigo-100">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Modifier</span>
                                </a>

                                <form action="{{ route('admin.trajets.destroy', $t->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Voulez-vous envoyer ce trajet dans la corbeille ?')">
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
                        <td colspan="4" class="py-20 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-20 h-20 rounded-3xl bg-slate-100 flex items-center justify-center">
                                    <i class="fa-solid fa-route text-4xl text-slate-300"></i>
                                </div>
                                <div>
                                    <p class="text-base font-bold text-slate-600">Aucun trajet configuré pour le moment.</p>
                                    <p class="text-sm text-slate-400 mt-1">Créez votre première ligne de voyage.</p>
                                </div>
                                <a href="{{ route('admin.trajets.create') }}"
                                   class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-2xl text-sm font-bold shadow-md">
                                    <i class="fa-solid fa-plus text-amber-400"></i>
                                    Créer votre premier trajet
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($trajets, 'links') && $trajets->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $trajets->links() }}
            </div>
        @endif
    </div>

</div>
@endsection