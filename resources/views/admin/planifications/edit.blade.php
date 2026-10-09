@extends('layouts.admin')

@section('content')

<div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">
            Modifier la planification #{{ $planificationVoyage->id }}
        </h1>
        <a href="{{ route('admin.planifications.index') }}"
           class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
            ← Retour
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg text-sm text-red-700">
            <i class="fa-solid fa-circle-exclamation mr-2"></i> Veuillez corriger les erreurs ci-dessous.
        </div>
    @endif

    <div class="bg-white shadow ring-1 ring-slate-200 rounded-xl p-6">
        <form action="{{ route('admin.planifications.update', $planificationVoyage->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- TRAJET --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Trajet</label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-route text-slate-400 text-sm"></i>
                    </div>
                    <select name="trajet_id" id="trajet_id"
                            class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm border-slate-300 focus:ring-indigo-600"
                            required>
                        <option value="">Sélectionner un trajet</option>
                        @foreach($trajets as $trajet)
                            <option value="{{ $trajet->id }}"
                                    {{ (old('trajet_id', $planificationVoyage->trajet_id) == $trajet->id) ? 'selected' : '' }}>
                                {{ $trajet->gareDepart->nom_gare ?? $trajet->gareDepart->nom ?? '?' }}
                                →
                                {{ $trajet->gareArrivee->nom_gare ?? $trajet->gareArrivee->nom ?? '?' }}
                                @if($trajet->gareDepart && ($trajet->gareDepart->ville || $trajet->ville_depart))
                                    ({{ $trajet->gareDepart->ville ?? $trajet->ville_depart }} → {{ $trajet->gareArrivee->ville ?? $trajet->ville_arrivee ?? '?' }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- BUS --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Bus assigné</label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-bus text-slate-400 text-sm"></i>
                    </div>
                    <select name="bus_id" id="bus_id"
                            class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm border-slate-300 focus:ring-indigo-600"
                            required>
                        <option value="">Sélectionner un bus</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}"
                                    {{ (old('bus_id', $planificationVoyage->bus_id) == $bus->id) ? 'selected' : '' }}>
                                {{ $bus->immatriculation ?? $bus->numero ?? 'Bus #'.$bus->id }}
                                @if($bus->modele) • {{ $bus->modele }} @endif
                                • {{ $bus->capacite_totale ?? $bus->places ?? $bus->nombre_places ?? '?' }} places
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- VAGUE DE DÉPART --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Vague de départ (Horaire)</label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-clock text-slate-400 text-sm"></i>
                    </div>
                    <select name="vague_depart_id" id="vague_depart_id"
                            class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm border-slate-300 focus:ring-indigo-600"
                            required>
                        <option value="">Sélectionner un horaire</option>
                        @foreach($vagues as $vague)
                            <option value="{{ $vague->id }}"
                                    {{ (old('vague_depart_id', $planificationVoyage->vague_depart_id) == $vague->id) ? 'selected' : '' }}>
                                {{ $vague->nom_vague ?? 'Départ' }} - {{ substr((string) $vague->heure_depart, 0, 5) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- DATE DU VOYAGE --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Date du voyage</label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-calendar text-slate-400 text-sm"></i>
                    </div>
                    <input type="date" name="date_voyage" id="date_voyage"
                           value="{{ old('date_voyage', $planificationVoyage->date_voyage ? \Carbon\Carbon::parse($planificationVoyage->date_voyage)->format('Y-m-d') : '') }}"
                           class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm border-slate-300 focus:ring-indigo-600"
                           required>
                </div>
            </div>

            {{-- NOMBRE DE TICKETS TOTAL --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre de tickets total</label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-ticket text-slate-400 text-sm"></i>
                    </div>
                    <input type="number" name="nombre_tickets_total" id="nombre_tickets_total"
                           value="{{ old('nombre_tickets_total', $planificationVoyage->nombre_tickets_total) }}" min="1"
                           class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm border-slate-300 focus:ring-indigo-600">
                </div>
            </div>

            {{-- PRIX DU VOYAGE --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Prix du voyage (FCFA)</label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-money-bill text-slate-400 text-sm"></i>
                    </div>
                    <input type="number" step="0.01" name="prix" id="prix"
                           value="{{ old('prix', $planificationVoyage->prix) }}" min="0"
                           placeholder="Ex: 15000"
                           class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm border-slate-300 focus:ring-indigo-600"
                           required>
                </div>
            </div>

            {{-- STATUT DU VOYAGE --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Statut du voyage</label>
                <select name="statut_voyage" id="statut_voyage"
                        class="block w-full rounded-lg py-3 px-4 text-slate-900 ring-1 ring-inset sm:text-sm border-slate-300 focus:ring-indigo-600"
                        required>
                    @foreach(['Programmation', 'En cours', 'Terminé', 'Annulé'] as $statut)
                        <option value="{{ $statut }}" {{ (old('statut_voyage', $planificationVoyage->statut_voyage) == $statut) ? 'selected' : '' }}>
                            {{ $statut }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ACTIONS --}}
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('admin.planifications.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2.5 rounded-lg text-sm font-medium">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-lg text-sm font-medium">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
