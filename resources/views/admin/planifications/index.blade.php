@extends('layouts.admin')

@section('content')
<div class="space-y-7" x-data="{ searchQuery: '', filterStatut: 'all' }">

    {{-- EN-TÊTE AVEC TITRE ET ACTIONS --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-indigo-100 text-indigo-600 border border-indigo-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-calendar-check"></i>
                </span>
                <span>Planifications des Voyages</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Programmez les départs, gérez les jauges de places et suivez le remplissage en direct.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.planifications.create') }}"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white px-5 py-3 rounded-2xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98]">
                <i class="fa-solid fa-plus text-amber-400 text-base"></i>
                <span>Planifier un voyage</span>
            </a>
        </div>
    </div>

    {{-- Réglage du tarif enfant de l'agence (admin) --}}
    @if(auth()->user()?->role === 'Admin')
        <form action="{{ route('admin.agence.age_enfant') }}" method="POST"
              class="flex flex-col sm:flex-row sm:items-center gap-3 bg-white rounded-2xl border border-slate-200/80 px-5 py-4">
            @csrf
            @method('PATCH')
            <div class="flex items-center gap-3 flex-1">
                <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fa-solid fa-child"></i></span>
                <div>
                    <p class="text-sm font-extrabold text-slate-900">Tarif enfant</p>
                    <p class="text-xs text-slate-500 font-semibold">Âge maximum pour bénéficier du prix enfant défini sur chaque départ.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="number" name="age_max_enfant" min="1" max="17" required
                       value="{{ old('age_max_enfant', auth()->user()->agence?->age_max_enfant ?? 12) }}"
                       class="w-20 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 text-center focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <span class="text-sm font-bold text-slate-600">ans</span>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700">Enregistrer</button>
            </div>
        </form>
        @error('age_max_enfant')
            <p class="text-xs font-bold text-rose-600 -mt-4">{{ $message }}</p>
        @enderror
        <form action="{{ route('admin.agence.assurance_bebe') }}" method="POST"
              class="flex flex-col sm:flex-row sm:items-center gap-3 bg-white rounded-2xl border border-slate-200/80 px-5 py-4">
            @csrf
            @method('PATCH')
            <div class="flex items-center gap-3 flex-1">
                <span class="w-10 h-10 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center"><i class="fa-solid fa-baby"></i></span>
                <div>
                    <p class="text-sm font-extrabold text-slate-900">Assurance bébé</p>
                    <p class="text-xs text-slate-500 font-semibold">Payée pour chaque bébé de 0 à 3 ans voyageant sur les genoux d'un adulte (sans siège). À partir de 4 ans, l'enfant paie son billet.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="number" name="assurance_bebe" min="0" max="100000" step="100" required
                       value="{{ old('assurance_bebe', auth()->user()->agence?->assurance_bebe ?? 5000) }}"
                       class="w-28 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 text-center focus:ring-2 focus:ring-pink-500 focus:outline-none">
                <span class="text-sm font-bold text-slate-600">FCFA</span>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-pink-600 text-white text-sm font-bold hover:bg-pink-700">Enregistrer</button>
            </div>
        </form>
        @error('assurance_bebe')
            <p class="text-xs font-bold text-rose-600 -mt-4">{{ $message }}</p>
        @enderror
        <form action="{{ route('admin.agence.choix_siege') }}" method="POST"
              class="flex flex-col sm:flex-row sm:items-center gap-3 bg-white rounded-2xl border border-slate-200/80 px-5 py-4">
            @csrf
            @method('PATCH')
            @php $choixSiege = (bool) auth()->user()->agence?->choix_siege; @endphp
            <div class="flex items-center gap-3 flex-1">
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-couch"></i></span>
                <div>
                    <p class="text-sm font-extrabold text-slate-900">Choix du siège par le client</p>
                    <p class="text-xs text-slate-500 font-semibold">
                        {{ $choixSiege ? 'Activé : le client choisit son siège sur le plan du bus.' : 'Désactivé : un siège libre est attribué automatiquement.' }}
                    </p>
                </div>
            </div>
            <input type="hidden" name="choix_siege" value="{{ $choixSiege ? 0 : 1 }}">
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl text-sm font-bold {{ $choixSiege ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                {{ $choixSiege ? 'Désactiver' : 'Activer' }}
            </button>
        </form>
    @endif

    {{-- CONTENEUR TABLEAU AVEC RECHERCHE ET FILTRES --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        {{-- BARRE DE RECHERCHE ET FILTRES RAPIDES --}}
        <div class="p-6 sm:px-8 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            
            <div class="relative w-full md:w-96">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400"></i>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Rechercher trajet, bus, date..." 
                       class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
            </div>

            {{-- Filtres statut --}}
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" 
                        @click="filterStatut = 'all'" 
                        :class="filterStatut === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold transition-colors">
                    Tous ({{ $planifications->total() }})
                </button>
                <button type="button" 
                        @click="filterStatut = 'Programmation'" 
                        :class="filterStatut === 'Programmation' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold transition-colors">
                    <i class="fa-solid fa-calendar mr-1"></i> Programmés
                </button>
                <button type="button" 
                        @click="filterStatut = 'En cours'" 
                        :class="filterStatut === 'En cours' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold transition-colors">
                    <i class="fa-solid fa-rocket mr-1"></i> En cours
                </button>
                <button type="button" 
                        @click="filterStatut = 'Terminé'" 
                        :class="filterStatut === 'Terminé' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold transition-colors">
                    <i class="fa-solid fa-circle-check mr-1"></i> Terminés
                </button>
                <button type="button" 
                        @click="filterStatut = 'Annulé'" 
                        :class="filterStatut === 'Annulé' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100'"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold transition-colors">
                    <i class="fa-solid fa-ban mr-1"></i> Annulés
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-black uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-8">Ligne & Trajet</th>
                        <th class="py-4 px-8">Bus Affecté</th>
                        <th class="py-4 px-8">Horaire</th>
                        <th class="py-4 px-8">Date de Départ</th>
                        <th class="py-4 px-8">Remplissage</th>
                        <th class="py-4 px-8">Statut</th>
                        <th class="py-4 px-8 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                @forelse($planifications as $p)
                    @php
                        $trajetStr = ($p->trajet->gareDepart->nom_gare ?? '') . ' ' . ($p->trajet->gareArrivee->nom_gare ?? '') . ' ' . ($p->trajet->ville_depart ?? '') . ' ' . ($p->trajet->ville_arrivee ?? '');
                        $busStr = $p->bus->immatriculation ?? '';
                        $dateStr = $p->date_voyage ? \Carbon\Carbon::parse($p->date_voyage)->format('d/m/Y') : '';
                        $statut = $p->statut_voyage ?? 'Programmation';

                        $vendus = $p->tickets ? $p->tickets->count() : 0;
                        $restantes = (int) ($p->places_disponibles ?? 0);
                        $totalPlaces = $vendus + $restantes;
                        $taux = $totalPlaces > 0 ? round(($vendus / $totalPlaces) * 100) : 0;

                        $badges = [
                            'Programmation' => ['bg-blue-50 text-blue-700 border-blue-200/60', 'bg-blue-500'],
                            'En cours'      => ['bg-amber-50 text-amber-700 border-amber-200/60', 'bg-amber-500'],
                            'Terminé'       => ['bg-emerald-50 text-emerald-700 border-emerald-200/60', 'bg-emerald-500'],
                            'Annulé'        => ['bg-red-50 text-red-700 border-red-200/60', 'bg-red-500'],
                        ];
                        [$badgeClass, $dotClass] = $badges[$statut] ?? ['bg-slate-100 text-slate-700 border-slate-200', 'bg-slate-400'];
                    @endphp
                    <tr x-show="(searchQuery === '' || '{{ strtolower($trajetStr) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($busStr) }}'.includes(searchQuery.toLowerCase()) || '{{ $dateStr }}'.includes(searchQuery)) && (filterStatut === 'all' || filterStatut === '{{ $statut }}')"
                        class="hover:bg-indigo-50/20 transition-colors">
                        
                        {{-- TRAJET --}}
                        <td class="py-5 px-8">
                            <div class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                                <span>{{ $p->trajet->gareDepart->nom_gare ?? 'Départ' }}</span>
                                <i class="fa-solid fa-arrow-right text-sm text-indigo-500"></i>
                                <span>{{ $p->trajet->gareArrivee->nom_gare ?? 'Arrivée' }}</span>
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5 font-semibold">
                                {{ $p->trajet->ville_depart ?? '?' }} → {{ $p->trajet->ville_arrivee ?? '?' }}
                            </div>
                        </td>

                        {{-- BUS --}}
                        <td class="py-5 px-8">
                            @if($p->bus)
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-slate-100 text-slate-800">
                                    <i class="fa-solid fa-bus text-slate-500 text-xs"></i>
                                    {{ $p->bus->immatriculation }}
                                </span>
                                @if($p->bus->modele)
                                    <div class="text-xs text-slate-400 mt-0.5 font-semibold">{{ $p->bus->modele }}</div>
                                @endif
                            @else
                                <span class="text-sm text-slate-400 italic">Non assigné</span>
                            @endif
                        </td>

                        {{-- HORAIRE --}}
                        <td class="py-5 px-8">
                            @if($p->vagueDepart)
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-bold bg-purple-50 text-purple-700 border border-purple-200/60 font-mono">
                                    <i class="fa-regular fa-clock text-xs"></i>
                                    {{ substr($p->vagueDepart->heure_depart, 0, 5) }}
                                </span>
                            @else
                                <span class="text-sm text-slate-400">—</span>
                            @endif
                        </td>

                        {{-- DATE --}}
                        <td class="py-5 px-8 text-sm font-semibold text-slate-700">
                            <i class="fa-regular fa-calendar text-slate-400 mr-1.5"></i>
                            {{ $dateStr ?: '—' }}
                        </td>

                        {{-- REMPLISSAGE / PLACES --}}
                        <td class="py-5 px-8">
                            <div class="w-36">
                                <div class="flex justify-between text-xs mb-1.5">
                                    <span class="font-bold text-slate-800 text-sm">{{ $vendus }}/{{ $totalPlaces }}</span>
                                    <span class="font-bold text-slate-500">{{ $taux }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $taux >= 90 ? 'bg-red-500' : ($taux >= 60 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                         style="width: {{ min(100, $taux) }}%;"></div>
                                </div>
                                <div class="text-xs text-slate-400 mt-1 font-semibold">
                                    {{ $restantes }} place(s) dispo
                                </div>
                            </div>
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
                                <a href="{{ route('admin.planifications.show', $p->id) }}"
                                   class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-xl transition-colors border border-slate-200">
                                    <i class="fa-solid fa-eye"></i>
                                    <span>Détails</span>
                                </a>

                                <a href="{{ route('admin.planifications.edit', $p->id) }}"
                                   class="inline-flex items-center gap-2 text-sm font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition-colors border border-indigo-100">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Modifier</span>
                                </a>

                                <form action="{{ route('admin.planifications.destroy', $p->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Voulez-vous supprimer cette planification ?')">
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
                        <td colspan="7" class="py-20 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-20 h-20 rounded-3xl bg-slate-100 flex items-center justify-center">
                                    <i class="fa-solid fa-calendar-xmark text-4xl text-slate-300"></i>
                                </div>
                                <div>
                                    <p class="text-base font-bold text-slate-600">Aucune planification de voyage enregistrée.</p>
                                    <p class="text-sm text-slate-400 mt-1">Commencez par créer votre premier départ planifié.</p>
                                </div>
                                <a href="{{ route('admin.planifications.create') }}"
                                   class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-2xl text-sm font-bold shadow-md">
                                    <i class="fa-solid fa-plus text-amber-400"></i>
                                    Planifier un premier voyage
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($planifications, 'links'))
            <div class="p-5 border-t border-slate-100">
                {{ $planifications->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
