@extends('layouts.admin')

@section('content')

<div class="max-w-4xl mx-auto space-y-7">

    {{-- EN-TÊTE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.planifications.index') }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-emerald-700 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm transition-all">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Retour aux planifications</span>
                </a>
                <span class="text-xs font-extrabold text-slate-400">/</span>
                <span class="text-xs font-black text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-200/60">
                    Édition #{{ $planificationVoyage->id }}
                </span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-indigo-100 text-indigo-600 border border-indigo-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-pen-to-square"></i>
                </span>
                <span>Modifier la Planification #{{ $planificationVoyage->id }}</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Ajustez le véhicule, l'horaire de départ, la date ou le statut de ce voyage.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-5 bg-rose-50 border border-rose-200 rounded-3xl text-sm text-rose-800 shadow-sm flex items-start gap-3">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg mt-0.5 shrink-0"></i>
            <div>
                <p class="font-extrabold text-sm mb-1">Veuillez corriger les erreurs ci-dessous :</p>
                <ul class="list-disc ml-5 space-y-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- CARTE DU FORMULAIRE D'ÉDITION --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-9">
        <form action="{{ route('admin.planifications.update', $planificationVoyage->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- 1. TRAJET --}}
            <div class="space-y-2">
                <label class="block text-sm font-extrabold text-slate-900">
                    Ligne & Trajet <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-route text-base"></i>
                    </div>
                    <select name="trajet_id" id="trajet_id"
                            class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all"
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

            {{-- 2. BUS ASSIGNÉ --}}
            <div class="space-y-2">
                <label class="block text-sm font-extrabold text-slate-900">
                    Bus assigné <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-bus text-base"></i>
                    </div>
                    <select name="bus_id" id="bus_id"
                            class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all"
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

            {{-- 3. VAGUE DE DÉPART --}}
            <div class="space-y-2">
                <label class="block text-sm font-extrabold text-slate-900">
                    Vague de départ (Horaire) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-regular fa-clock text-base"></i>
                    </div>
                    <select name="vague_depart_id" id="vague_depart_id"
                            class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all"
                            required>
                        <option value="">Sélectionner un horaire</option>
                        @foreach($vagues as $vague)
                            <option value="{{ $vague->id }}"
                                    {{ (old('vague_depart_id', $planificationVoyage->vague_depart_id) == $vague->id) ? 'selected' : '' }}>
                                {{ $vague->nom_vague ?? 'Départ' }} — {{ substr((string) $vague->heure_depart, 0, 5) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- 4. DATE DU VOYAGE --}}
            <div class="space-y-2">
                <label class="block text-sm font-extrabold text-slate-900">
                    Date du voyage <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-regular fa-calendar text-base"></i>
                    </div>
                    <input type="date" name="date_voyage" id="date_voyage"
                           value="{{ old('date_voyage', $planificationVoyage->date_voyage ? \Carbon\Carbon::parse($planificationVoyage->date_voyage)->format('Y-m-d') : '') }}"
                           class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all"
                           required>
                </div>
            </div>

            {{-- 5. NOMBRE DE TICKETS ET PRIX --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                <div class="space-y-2">
                    <label class="block text-sm font-extrabold text-slate-900">
                        Nombre de tickets total
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-ticket text-base"></i>
                        </div>
                        <input type="number" name="nombre_tickets_total" id="nombre_tickets_total"
                               value="{{ old('nombre_tickets_total', $planificationVoyage->nombre_tickets_total) }}" min="1"
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-extrabold text-slate-900">
                        Prix du voyage (FCFA) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-money-bill-wave text-base"></i>
                        </div>
                        <input type="number" step="0.01" name="prix" id="prix"
                               value="{{ old('prix', $planificationVoyage->prix) }}" min="0"
                               placeholder="Ex: 15000"
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all"
                               required>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-extrabold text-slate-900">
                        Prix enfant (FCFA) (Optionnel)
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-child text-base"></i>
                        </div>
                        <input type="number" step="0.01" name="prix_enfant" id="prix_enfant"
                               value="{{ old('prix_enfant', $planificationVoyage->prix_enfant) }}" min="0"
                               placeholder="Vide = même prix que l'adulte"
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
                    </div>
                    <p class="text-xs text-slate-400 font-semibold">
                        Enfants jusqu'à {{ auth()->user()->agence?->age_max_enfant ?? 12 }} ans (réglage de l'agence dans la liste des planifications).
                    </p>
                    @error('prix_enfant')
                        <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 6. STATUT DU VOYAGE --}}
            <div class="space-y-2">
                <label class="block text-sm font-extrabold text-slate-900">
                    Statut du voyage <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-signal text-base"></i>
                    </div>
                    <select name="statut_voyage" id="statut_voyage"
                            class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all"
                            required>
                        @foreach(['Programmation', 'En cours', 'Terminé', 'Annulé'] as $statut)
                            <option value="{{ $statut }}" {{ (old('statut_voyage', $planificationVoyage->statut_voyage) == $statut) ? 'selected' : '' }}>
                                {{ $statut }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.planifications.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3.5 rounded-2xl text-sm font-bold transition-all">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white px-7 py-3.5 rounded-2xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-amber-400 text-base"></i>
                    <span>Mettre à jour la planification</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
