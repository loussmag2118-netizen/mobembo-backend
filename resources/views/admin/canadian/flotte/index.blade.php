@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Flotte Air Canadian</h1>
            <p class="text-sm text-slate-500 mt-1">
                Gérez les aéronefs de la compagnie
            </p>
        </div>
        <a href="{{ route('canadian.flotte.create') }}"
           class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-[#C62828] to-[#8E0000] hover:from-[#E53935] hover:to-[#C62828] text-white font-semibold rounded-xl shadow-lg shadow-red-500/20 transition">
            <i class="fa-solid fa-plus mr-2"></i> Ajouter un aéronef
        </a>
    </div>

    {{-- Messages --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Statistiques --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-sky-100 text-sky-600 rounded-lg">
                    <i class="fa-solid fa-plane"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total</p>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $flottes->count() }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Actifs</p>
            </div>
            <p class="text-2xl font-black text-slate-900">
                {{ $flottes->where('statut', 'Actif')->count() }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                    <i class="fa-solid fa-wrench"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Maintenance</p>
            </div>
            <p class="text-2xl font-black text-slate-900">
                {{ $flottes->where('statut', 'Maintenance')->count() }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-slate-100 text-slate-600 rounded-lg">
                    <i class="fa-solid fa-chair"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Places</p>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $flottes->sum('capacite_places') }}</p>
        </div>
    </div>

    {{-- Grille des aéronefs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($flottes as $avion)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg transition overflow-hidden group">

                {{-- Bannière --}}
                <div class="h-32 bg-gradient-to-br from-[#C62828] to-[#8E0000] relative overflow-hidden">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <i class="fa-solid fa-plane text-6xl text-white/20 group-hover:scale-110 transition-transform duration-500"></i>
                    </div>
                    <div class="absolute top-3 right-3">
                        @php
                            $statuts = [
                                'Actif'       => 'bg-emerald-500 text-white',
                                'Maintenance' => 'bg-amber-500 text-white',
                                'Inactif'     => 'bg-slate-500 text-white',
                            ];
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $statuts[$avion->statut] ?? 'bg-slate-500 text-white' }}">
                            {{ $avion->statut ?? 'Actif' }}
                        </span>
                    </div>
                </div>

                {{-- Contenu --}}
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-slate-900 text-lg">
                                {{ $avion->modele ?? 'Modèle inconnu' }}
                            </h3>
                            <p class="text-xs text-slate-500">
                                {{ $avion->nom_appareil ?? 'Air Canadian' }}
                            </p>
                        </div>
                        <div class="p-2 bg-red-50 text-[#C62828] rounded-lg">
                            <i class="fa-solid fa-plane"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100">
                        <div>
                            <p class="text-xs text-slate-500">Capacité</p>
                            <p class="font-bold text-slate-900">{{ $avion->capacite_places ?? 0 }} places</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Année</p>
                            <p class="font-bold text-slate-900">{{ $avion->annee ?? '—' }}</p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-100">
                        <a href="{{ route('canadian.flotte.show', $avion->id) }}"
                           class="flex-1 text-center px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                            <i class="fa-solid fa-eye mr-1"></i> Voir
                        </a>
                        <a href="{{ route('canadian.flotte.edit', $avion->id) }}"
                           class="flex-1 text-center px-3 py-2 bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-semibold rounded-lg transition">
                            <i class="fa-solid fa-pen mr-1"></i> Modifier
                        </a>
                        <form action="{{ route('canadian.flotte.destroy', $avion->id) }}" method="POST"
                              class="flex-1" onsubmit="return confirm('Supprimer cet aéronef ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full px-3 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold rounded-lg transition">
                                <i class="fa-solid fa-trash mr-1"></i> Suppr.
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 shadow-sm p-16 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-4">
                    <i class="fa-solid fa-plane text-2xl text-slate-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700 mb-2">Aucun aéronef</h3>
                <p class="text-sm text-slate-500 mb-4">
                    Commencez par ajouter votre premier aéronef à la flotte
                </p>
                <a href="{{ route('canadian.flotte.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-[#C62828] hover:bg-[#8E0000] text-white text-sm font-semibold rounded-lg transition">
                    <i class="fa-solid fa-plus mr-2"></i> Ajouter un aéronef
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection