@extends('layouts.admin')

@section('title', 'Villes desservies')
@section('header_icon', 'fa-city')
@section('header_title', 'Villes du Réseau Terrestre')
@section('header_subtitle', 'Référentiel commun : les agences rattachent leurs gares à ces villes')

@section('content')
<div class="space-y-6" x-data="{ searchQuery: '', editId: null }">

    @if($errors->any())
        <div class="p-5 bg-red-50 border border-red-200 text-red-700 rounded-2xl shadow-sm">
            <div class="font-bold mb-2 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                <span>Erreur lors de l'enregistrement de la ville :</span>
            </div>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 1. STATISTIQUES --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="card-hover bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Villes enregistrées</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-city"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-slate-900">{{ $stats['villes'] }}</p>
            <p class="text-xs text-slate-400 mt-2">Dans le référentiel national</p>
        </div>

        <div class="card-hover bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Villes actives</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-emerald-600">{{ $stats['actives'] }}</p>
            <p class="text-xs text-slate-400 mt-2">Proposées aux agences</p>
        </div>

        <div class="card-hover bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Gares rattachées</span>
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-slate-900">{{ $stats['gares'] }}</p>
            <p class="text-xs text-slate-400 mt-2">Toutes agences confondues</p>
        </div>
    </div>

    {{-- 2. AJOUT D'UNE VILLE --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-7">
        <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-plus-circle text-emerald-600"></i>
            <span>Nouvelle ville</span>
        </h2>
        <p class="text-xs text-slate-500 mt-0.5 mb-5">
            Une fois ajoutée, la ville apparaît dans le formulaire « Gare » de toutes les agences.
        </p>

        <form action="{{ route('superadmin.villes.store') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <input type="text" name="nom" value="{{ old('nom') }}" required maxlength="100" placeholder="Ex: Sibiti"
                   class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
            <button type="submit"
                    class="bg-[#124231] hover:bg-[#0c2f23] text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>Ajouter la ville</span>
            </button>
        </form>
    </div>

    {{-- 3. LISTE DES VILLES --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 sm:px-8 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-slate-700"></i>
                    <span>Liste des villes ({{ $stats['villes'] }})</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Une ville désactivée n'est plus proposée pour les nouvelles gares ; les gares existantes continuent de fonctionner.</p>
            </div>

            <div class="relative w-full sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" x-model="searchQuery" placeholder="Rechercher une ville..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-6">Ville</th>
                        <th class="py-3.5 px-6">Gares</th>
                        <th class="py-3.5 px-6">Agences présentes</th>
                        <th class="py-3.5 px-6">Statut</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($villes as $ville)
                        <tr x-show="searchQuery === '' || @js(mb_strtolower($ville->nom)).includes(searchQuery.toLowerCase())"
                            class="hover:bg-slate-50/80 transition-colors">

                            {{-- Nom (avec renommage en ligne) --}}
                            <td class="py-4 px-6">
                                <div x-show="editId !== {{ $ville->id }}" class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-emerald-700 text-white flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-city text-xs"></i>
                                    </div>
                                    <span class="font-extrabold text-slate-900">{{ $ville->nom }}</span>
                                </div>
                                <form x-show="editId === {{ $ville->id }}" style="display: none;"
                                      action="{{ route('superadmin.villes.update', $ville) }}" method="POST" class="flex items-center gap-2"
                                      onsubmit="return confirm('Renommer cette ville ? Les gares et trajets de toutes les agences seront mis à jour.');">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="nom" value="{{ $ville->nom }}" required maxlength="100"
                                           class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-sm font-bold focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#124231] text-white text-xs font-bold">OK</button>
                                    <button type="button" @click="editId = null" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-bold">Annuler</button>
                                </form>
                            </td>

                            {{-- Gares --}}
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                    <i class="fa-solid fa-location-dot text-[10px]"></i>
                                    {{ $ville->gares_count }} {{ $ville->gares_count > 1 ? 'gares' : 'gare' }}
                                </span>
                            </td>

                            {{-- Agences --}}
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700">
                                    <i class="fa-solid fa-building text-[10px]"></i>
                                    {{ $ville->agences_count }} {{ $ville->agences_count > 1 ? 'agences' : 'agence' }}
                                </span>
                            </td>

                            {{-- Statut --}}
                            <td class="py-4 px-6">
                                @if($ville->actif)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                        Désactivée
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <button type="button" @click="editId = {{ $ville->id }}"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all">
                                    <i class="fa-solid fa-pen text-[11px]"></i>
                                    <span>Renommer</span>
                                </button>

                                <form action="{{ route('superadmin.villes.toggle', $ville) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-1.5 rounded-xl transition-all {{ $ville->actif ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}">
                                        <i class="fa-solid {{ $ville->actif ? 'fa-eye-slash' : 'fa-eye' }} text-[11px]"></i>
                                        <span>{{ $ville->actif ? 'Désactiver' : 'Activer' }}</span>
                                    </button>
                                </form>

                                @if($ville->gares_count === 0)
                                    <form action="{{ route('superadmin.villes.destroy', $ville) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Supprimer définitivement cette ville ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-1.5 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-all">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                            <span>Supprimer</span>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-city text-4xl mb-3 text-slate-300"></i>
                                <p class="text-sm font-semibold text-slate-600">Aucune ville enregistrée.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($villes, 'links') && $villes->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $villes->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
