@extends('layouts.admin')

@section('content')
<div class="space-y-7" x-data="{ searchQuery: '' }">

    {{-- EN-TÊTE AVEC TITRE ET ACTIONS --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="w-13 h-13 p-3 rounded-2xl bg-amber-100 text-amber-600 border border-amber-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-map-location-dot"></i>
                </span>
                <span>Agences & Stations</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Points de départ, d'arrivée et escales desservis par votre agence.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.gares.trashed') }}"
               class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 px-4 py-3 rounded-2xl text-sm font-bold transition-all shadow-sm border border-slate-200/80">
                <i class="fa-solid fa-trash-can text-slate-400"></i>
                <span>Corbeille</span>
            </a>

            <a href="{{ route('admin.gares.create') }}"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white px-5 py-3 rounded-2xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98]">
                <i class="fa-solid fa-plus text-amber-400 text-base"></i>
                <span>Ajouter une agence</span>
            </a>
        </div>
    </div>

    {{-- CONTENEUR TABLEAU AVEC RECHERCHE INSTANTANÉE --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="p-6 sm:px-8 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="relative w-full sm:w-96">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400"></i>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Rechercher nom d'agence, ville..." 
                       class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none transition-all">
            </div>

            <span class="text-sm font-bold text-slate-500 bg-slate-100 px-4 py-2.5 rounded-2xl">
                <i class="fa-solid fa-location-dot text-amber-500 mr-1.5"></i>
                {{ $gares->total() }} agence(s) enregistrée(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-black uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-8">Nom de l'agence / Terminal</th>
                        <th class="py-4 px-8">Ville / Localité</th>
                        <th class="py-4 px-8 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                @forelse($gares as $g)
                    <tr x-show="searchQuery === '' || '{{ strtolower($g->nom_gare) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($g->ville) }}'.includes(searchQuery.toLowerCase())"
                        class="hover:bg-amber-50/30 transition-colors group">
                        
                        {{-- NOM DE LA GARE --}}
                        <td class="py-5 px-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0 border border-amber-100 group-hover:bg-amber-100 transition-colors">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div>
                                    <span class="font-extrabold text-slate-900 text-base">
                                        {{ $g->nom_gare }}
                                    </span>
                                    <div class="text-xs text-slate-400 mt-0.5 font-semibold">Agence ID #{{ $g->id }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- VILLE --}}
                        <td class="py-5 px-8">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                                <i class="fa-solid fa-city text-xs"></i>
                                {{ $g->ville }}
                            </span>
                        </td>

                        {{-- ACTIONS --}}
                        <td class="py-5 px-8 text-right">
                            <div class="flex justify-end items-center gap-2">
                                <a href="{{ route('admin.gares.edit', $g->id) }}"
                                   class="inline-flex items-center gap-2 text-sm font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition-colors border border-indigo-100">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Modifier</span>
                                </a>

                                <form action="{{ route('admin.gares.destroy', $g->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Voulez-vous envoyer cette agence dans la corbeille ?')">
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
                        <td colspan="3" class="py-20 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-20 h-20 rounded-3xl bg-slate-100 flex items-center justify-center">
                                    <i class="fa-solid fa-location-dot text-4xl text-slate-300"></i>
                                </div>
                                <div>
                                    <p class="text-base font-bold text-slate-600">Aucune agence enregistrée pour le moment.</p>
                                    <p class="text-sm text-slate-400 mt-1">Commencez par ajouter votre première agence.</p>
                                </div>
                                <a href="{{ route('admin.gares.create') }}"
                                   class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-2xl text-sm font-bold shadow-md">
                                    <i class="fa-solid fa-plus text-amber-400"></i>
                                    Créer votre première agence
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($gares, 'links'))
            <div class="p-5 border-t border-slate-100">
                {{ $gares->links() }}
            </div>
        @endif
    </div>

</div>
@endsection