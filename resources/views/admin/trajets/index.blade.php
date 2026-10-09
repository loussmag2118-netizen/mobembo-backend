@extends('layouts.admin')

@section('content')

{{-- ============================================================ --}}
{{-- EN-TÊTE                                                      --}}
{{-- ============================================================ --}}
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">
            Gestion des trajets
        </h1>
        <p class="text-slate-500 text-sm mt-1">
            {{ $trajets->count() }} trajet(s) configuré(s) pour votre agence
        </p>
    </div>

    <div class="flex gap-3 mt-4 sm:mt-0">
        <a href="{{ route('admin.trajets.trashed') }}"
           class="inline-flex items-center rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 text-sm font-medium">
            <i class="fa-solid fa-trash-can mr-1"></i> Corbeille
        </a>

        <a href="{{ route('admin.trajets.create') }}"
           class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">
            + Ajouter un trajet
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg text-sm">
        <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r-lg text-sm">
        <i class="fa-solid fa-circle-exclamation mr-2"></i>{{ session('error') }}
    </div>
@endif

{{-- ============================================================ --}}
{{-- TABLEAU                                                      --}}
{{-- ============================================================ --}}
<div class="bg-white shadow ring-1 ring-slate-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Trajet</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Bus assigné</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Horaires de départ</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($trajets as $t)
                    <tr class="hover:bg-slate-50 transition">

                        {{-- TRAJET --}}
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-900">
                                    {{ $t->gareDepart->nom_gare ?? '?' }}
                                </span>
                                <span class="text-slate-400">→</span>
                                <span class="font-semibold text-slate-900">
                                    {{ $t->gareArrivee->nom_gare ?? '?' }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">
                                {{ $t->ville_depart ?? $t->gareDepart->ville ?? '?' }}
                                →
                                {{ $t->ville_arrivee ?? $t->gareArrivee->ville ?? '?' }}
                            </div>
                        </td>

                        {{-- BUS --}}
                        <td class="p-4">
                            @if($t->bus)
                                @php
                                    $statutBus = $t->bus->statut ?? 'Disponible';
                                    $badgeClass = match($statutBus) {
                                        'Disponible'   => 'bg-emerald-50 text-emerald-700',
                                        'Maintenance'  => 'bg-amber-50 text-amber-700',
                                        'Indisponible' => 'bg-red-50 text-red-700',
                                        default        => 'bg-slate-100 text-slate-700',
                                    };
                                    $emoji = match($statutBus) {
                                        'Disponible'   => '✅',
                                        'Maintenance'  => '🔧',
                                        'Indisponible' => '❌',
                                        default        => '•',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                    {{ $emoji }} {{ $t->bus->immatriculation }}
                                </span>
                                @if($t->bus->modele)
                                    <div class="text-xs text-slate-500 mt-1">{{ $t->bus->modele }}</div>
                                @endif
                            @else
                                <span class="text-slate-400 text-xs">Aucun bus assigné</span>
                            @endif
                        </td>

                        {{-- HORAIRES --}}
                        <td class="p-4">
                            @if($t->vagues && $t->vagues->count() > 0)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($t->vagues as $v)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                            <i class="fa-solid fa-clock mr-1"></i>
                                            {{ substr($v->heure_depart, 0, 5) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400 text-xs">Aucun horaire</span>
                            @endif
                        </td>

                        {{-- ACTIONS --}}
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-4">
                                <a href="{{ route('admin.trajets.edit', $t->id) }}"
                                   class="text-indigo-600 font-medium hover:text-indigo-900 text-sm">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Modifier
                                </a>

                                <form action="{{ route('admin.trajets.destroy', $t->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Envoyer ce trajet dans la corbeille ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 font-medium hover:text-red-900 text-sm">
                                        <i class="fa-solid fa-trash-can mr-1"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center text-slate-400">
                            <i class="fa-solid fa-route text-4xl mb-3 text-slate-300"></i>
                            <p class="text-base">Aucun trajet enregistré pour le moment.</p>
                            <a href="{{ route('admin.trajets.create') }}"
                               class="inline-block mt-4 bg-slate-900 hover:bg-slate-800 text-white px-5 py-2 rounded-lg text-sm font-semibold">
                                + Créer votre premier trajet
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection