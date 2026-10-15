@extends('layouts.admin')

@section('content')
<div class="space-y-7" x-data="{ searchQuery: '', filterStatut: 'all' }">

    {{-- EN-TÊTE AVEC TITRE ET ACTIONS --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-blue-100 text-blue-600 border border-blue-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-bus"></i>
                </span>
                <span>Gestion de la Flotte des Bus</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Suivez l'état, la capacité et l'immatriculation de vos véhicules.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.bus.trashed') }}"
               class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 px-4 py-3 rounded-2xl text-sm font-bold transition-all shadow-sm border border-slate-200/80">
                <i class="fa-solid fa-trash-can text-slate-400"></i>
                <span>Corbeille</span>
            </a>

            <a href="{{ route('admin.bus.create') }}"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white px-5 py-3 rounded-2xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98]">
                <i class="fa-solid fa-plus text-amber-400 text-base"></i>
                <span>Ajouter un bus</span>
            </a>
        </div>
    </div>

    {{-- STATISTIQUES RAPIDES --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <div class="text-2xl font-black text-slate-900">{{ $bus->total() }}</div>
            <div class="text-sm font-bold text-slate-500 mt-1">Total flotte</div>
        </div>
        <div class="bg-emerald-50 rounded-2xl p-5 border border-emerald-100 shadow-sm">
            <div class="text-2xl font-black text-emerald-700">{{ $parStatut['Disponible'] ?? 0 }}</div>
            <div class="text-sm font-bold text-emerald-600 mt-1">Disponibles</div>
        </div>
        <div class="bg-amber-50 rounded-2xl p-5 border border-amber-100 shadow-sm">
            <div class="text-2xl font-black text-amber-700">{{ $parStatut['Maintenance'] ?? 0 }}</div>
            <div class="text-sm font-bold text-amber-600 mt-1">En maintenance</div>
        </div>
        <div class="bg-red-50 rounded-2xl p-5 border border-red-100 shadow-sm">
            <div class="text-2xl font-black text-red-700">{{ $parStatut['Indisponible'] ?? 0 }}</div>
            <div class="text-sm font-bold text-red-600 mt-1">Indisponibles</div>
        </div>
    </div>

    {{-- CARTE CONTENEUR DU TABLEAU --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        {{-- BARRE DE RECHERCHE ET FILTRES --}}
        <div class="p-6 sm:px-8 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            
            <div class="relative w-full md:w-96">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400"></i>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Rechercher immatriculation, modèle..." 
                       class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all">
            </div>

            {{-- Filtres par statut --}}
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" 
                        @click="filterStatut = 'all'" 
                        :class="filterStatut === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold transition-colors">
                    Tous ({{ $bus->total() }})
                </button>
                <button type="button" 
                        @click="filterStatut = 'Disponible'" 
                        :class="filterStatut === 'Disponible' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold transition-colors">
                    <i class="fa-solid fa-circle-check mr-1"></i> Disponibles
                </button>
                <button type="button" 
                        @click="filterStatut = 'Maintenance'" 
                        :class="filterStatut === 'Maintenance' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold transition-colors">
                    <i class="fa-solid fa-wrench mr-1"></i> Maintenance
                </button>
                <button type="button" 
                        @click="filterStatut = 'Indisponible'" 
                        :class="filterStatut === 'Indisponible' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100'"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold transition-colors">
                    <i class="fa-solid fa-ban mr-1"></i> Indisponibles
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-black uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-8">Immatriculation</th>
                        <th class="py-4 px-8">Modèle / Marque</th>
                        <th class="py-4 px-8">Capacité</th>
                        <th class="py-4 px-8">Statut Opérationnel</th>
                        <th class="py-4 px-8 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                @forelse($bus as $b)
                    @php
                        $statut = $b->statut ?? 'Disponible';
                        $badges = [
                            'Disponible'   => ['bg-emerald-50 text-emerald-700 border-emerald-200/80', 'bg-emerald-500'],
                            'Maintenance'  => ['bg-amber-50 text-amber-700 border-amber-200/80',    'bg-amber-500'],
                            'Indisponible' => ['bg-red-50 text-red-700 border-red-200/80',        'bg-red-500'],
                        ];
                        [$badgeClass, $dotClass] = $badges[$statut] ?? ['bg-slate-100 text-slate-700 border-slate-200', 'bg-slate-400'];
                    @endphp
                    <tr x-show="(searchQuery === '' || '{{ strtolower($b->immatriculation) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($b->modele ?? '') }}'.includes(searchQuery.toLowerCase())) && (filterStatut === 'all' || filterStatut === '{{ $statut }}')"
                        class="hover:bg-blue-50/30 transition-colors group">
                        
                        {{-- IMMATRICULATION --}}
                        <td class="py-5 px-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center text-xl shrink-0 group-hover:bg-blue-100 transition-colors border border-blue-100">
                                    <i class="fa-solid fa-bus-simple"></i>
                                </div>
                                <div>
                                    <span class="font-extrabold text-slate-900 text-base tracking-wide font-mono">
                                        {{ $b->immatriculation }}
                                    </span>
                                    <div class="text-xs text-slate-400 mt-0.5 font-semibold">Bus ID #{{ $b->id }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- MODÈLE --}}
                        <td class="py-5 px-8 text-slate-700 text-sm font-semibold">
                            {{ $b->modele ?? 'Modèle standard' }}
                        </td>

                        {{-- CAPACITÉ --}}
                        <td class="py-5 px-8">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-slate-100 text-slate-800">
                                <i class="fa-solid fa-users text-slate-500 text-xs"></i>
                                {{ $b->capacite_totale ?? $b->places ?? '—' }} places
                            </span>
                        </td>

                        {{-- STATUT --}}
                        <td class="py-5 px-8">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold border {{ $badgeClass }}">
                                <span class="w-2.5 h-2.5 rounded-full {{ $dotClass }}"></span>
                                {{ $statut }}
                            </span>
                        </td>

                        {{-- ACTIONS --}}
                        <td class="py-5 px-8 text-right">
                            <div class="flex justify-end items-center gap-2">
                                <a href="{{ route('admin.bus.edit', $b->id) }}"
                                   class="inline-flex items-center gap-2 text-sm font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition-colors border border-indigo-100">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Modifier</span>
                                </a>

                                <form action="{{ route('admin.bus.destroy', $b->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Voulez-vous envoyer ce bus dans la corbeille ?')">
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
                        <td colspan="5" class="py-20 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-20 h-20 rounded-3xl bg-slate-100 flex items-center justify-center">
                                    <i class="fa-solid fa-bus text-4xl text-slate-300"></i>
                                </div>
                                <div>
                                    <p class="text-base font-bold text-slate-600">Aucun bus enregistré.</p>
                                    <p class="text-sm text-slate-400 mt-1">Ajoutez votre premier véhicule pour commencer.</p>
                                </div>
                                <a href="{{ route('admin.bus.create') }}"
                                   class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-2xl text-sm font-bold shadow-md">
                                    <i class="fa-solid fa-plus text-amber-400"></i>
                                    Ajouter un premier bus
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($bus, 'links'))
            <div class="p-5 border-t border-slate-100">
                {{ $bus->links() }}
            </div>
        @endif
    </div>

</div>
@endsection