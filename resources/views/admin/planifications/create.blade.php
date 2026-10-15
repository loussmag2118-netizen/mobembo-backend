@extends('layouts.admin')

@section('content')

{{-- FLATPICKR (Calendrier multi-dates) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>

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
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-indigo-100 text-indigo-600 border border-indigo-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-calendar-plus"></i>
                </span>
                <span>Planifier un Nouveau Voyage</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Sélectionnez la ligne, le bus, l'horaire et programmez une ou plusieurs dates en un clic.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-5 bg-rose-50 border border-rose-200 rounded-3xl text-sm text-rose-800 shadow-sm flex items-start gap-3">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg mt-0.5 shrink-0"></i>
            <div>
                <p class="font-extrabold text-sm mb-1">Veuillez corriger les erreurs de saisie :</p>
                <ul class="list-disc ml-5 space-y-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- CARTE FORMULAIRE PREMIUM --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-9">
        <form action="{{ route('admin.planifications.store') }}" method="POST" class="space-y-6" id="planif_form">
            @csrf

            {{-- 1. TRAJET --}}
            <div class="space-y-2">
                <label class="block text-sm font-extrabold text-slate-900">
                    Ligne & Trajet de voyage <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-route text-base"></i>
                    </div>
                    <select name="trajet_id" id="trajet_id"
                            class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all"
                            required>
                        <option value="">Sélectionner un trajet</option>
                        @forelse($trajets as $trajet)
                            <option value="{{ $trajet->id }}"
                                    data-bus-id="{{ $trajet->bus_id ?? '' }}"
                                    {{ old('trajet_id') == $trajet->id ? 'selected' : '' }}>
                                {{ $trajet->gareDepart->nom_gare ?? $trajet->gareDepart->nom ?? '?' }}
                                →
                                {{ $trajet->gareArrivee->nom_gare ?? $trajet->gareArrivee->nom ?? '?' }}
                                @if($trajet->gareDepart && ($trajet->gareDepart->ville || $trajet->ville_depart))
                                    ({{ $trajet->gareDepart->ville ?? $trajet->ville_depart }} → {{ $trajet->gareArrivee->ville ?? $trajet->ville_arrivee ?? '?' }})
                                @endif
                                @if($trajet->bus)
                                    · 🚌 {{ $trajet->bus->immatriculation }}
                                @endif
                            </option>
                        @empty
                            <option value="" disabled>⚠️ Aucun trajet enregistré pour cette agence</option>
                        @endforelse
                    </select>
                </div>
                @error('trajet_id')
                    <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- 2. BUS AFFECTÉ --}}
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
                        @forelse($buses as $bus)
                            @php
                                $bStatut = $bus->statut ?? 'Disponible';
                                $emoji = $bStatut === 'Disponible' ? '✅' : ($bStatut === 'Maintenance' ? '🔧' : '❌');
                            @endphp
                            <option value="{{ $bus->id }}"
                                    {{ old('bus_id') == $bus->id ? 'selected' : '' }}>
                                {{ $emoji }}
                                {{ $bus->immatriculation ?? $bus->numero ?? 'Bus #'.$bus->id }}
                                @if($bus->modele) • {{ $bus->modele }} @endif
                                • {{ $bus->capacite_totale ?? $bus->places ?? $bus->nombre_places ?? '?' }} places
                                — {{ $bStatut }}
                            </option>
                        @empty
                            <option value="" disabled>⚠️ Aucun bus enregistré pour cette agence</option>
                        @endforelse
                    </select>
                </div>
                <p id="bus_help" class="text-xs font-bold text-slate-400 mt-1">
                    Le bus est automatiquement pré-sélectionné selon le trajet choisi.
                </p>
                @error('bus_id')
                    <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- 3. VAGUE DE DÉPART (HORAIRE) --}}
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
                        <option value="">Sélectionnez d'abord un trajet</option>
                    </select>
                </div>
                <p id="vague_help" class="text-xs font-bold text-slate-400 mt-1">
                    Les horaires s'afficheront après la sélection du trajet.
                </p>
                @error('vague_depart_id')
                    <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- 4. DATES DU VOYAGE (FLATPICKR) --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-extrabold text-slate-900">
                        Dates du voyage <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-200/50">
                        Sélection multiple possible
                    </span>
                </div>
                <p class="text-xs text-slate-400 font-semibold">
                    Cliquez sur les dates dans le calendrier. Chaque date créera automatiquement un départ planifié.
                </p>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 z-10">
                        <i class="fa-regular fa-calendar text-base"></i>
                    </div>
                    <input type="text" id="dates_picker" name="dates_voyage"
                           value="{{ old('dates_voyage') }}"
                           placeholder="Cliquez pour choisir une ou plusieurs dates"
                           class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none cursor-pointer transition-all"
                           readonly
                           required>
                </div>
                <p id="dates_help" class="text-xs font-bold text-slate-400 mt-1">
                    Aucune date sélectionnée
                </p>
                @error('dates_voyage')
                    <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- 5. NOMBRE DE TICKETS ET PRIX (2 COLONNES) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                <div class="space-y-2">
                    <label class="block text-sm font-extrabold text-slate-900">
                        Quota de billets (Optionnel)
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-ticket text-base"></i>
                        </div>
                        <input type="number" name="nombre_tickets_total" id="nombre_tickets_total"
                               value="{{ old('nombre_tickets_total') }}" min="1"
                               placeholder="Capacité par défaut du bus"
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
                    </div>
                    <p class="text-xs text-slate-400 font-semibold">
                        Laissez vide pour adopter la capacité totale du véhicule.
                    </p>
                    @error('nombre_tickets_total')
                        <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-extrabold text-slate-900">
                        Prix du billet (FCFA) (Optionnel)
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-money-bill-wave text-base"></i>
                        </div>
                        <input type="number" step="0.01" name="prix" id="prix"
                               value="{{ old('prix') }}" min="0"
                               placeholder="Ex: 15000"
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
                    </div>
                    <p class="text-xs text-slate-400 font-semibold">
                        Tarif spécifique appliqué à ce voyage.
                    </p>
                    @error('prix')
                        <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
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
                               value="{{ old('prix_enfant') }}" min="0"
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

            {{-- BOUTONS D'ACTION --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.planifications.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3.5 rounded-2xl text-sm font-bold transition-all">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white px-7 py-3.5 rounded-2xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2">
                    <i class="fa-solid fa-check text-amber-400 text-base"></i>
                    <span>Enregistrer les planifications</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- SCRIPTS --}}
<script>
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        // 1. FLATPICKR — Calendrier multi-dates
        let picker = null;
        const inputDates = document.getElementById('dates_picker');
        const helpDates  = document.getElementById('dates_help');

        if (typeof flatpickr !== 'undefined' && inputDates) {
            try {
                if (flatpickr.l10ns && flatpickr.l10ns.fr) {
                    flatpickr.localize(flatpickr.l10ns.fr);
                }

                picker = flatpickr("#dates_picker", {
                    mode: "multiple",
                    minDate: "today",
                    dateFormat: "Y-m-d",
                    conjunction: ",",
                    showMonths: 1,
                    onChange: function (selectedDates) {
                        if (!helpDates) return;
                        if (selectedDates.length === 0) {
                            helpDates.textContent = "Aucune date sélectionnée";
                            helpDates.className = "text-xs font-bold text-slate-400 mt-1";
                        } else {
                            helpDates.textContent = "✨ " + selectedDates.length + " date(s) programmée(s)";
                            helpDates.className = "text-xs font-bold text-emerald-600 mt-1";
                        }
                    }
                });

                const oldValue = "{{ old('dates_voyage') }}";
                if (oldValue) {
                    picker.setDate(oldValue.split(','), false);
                    if (helpDates) {
                        helpDates.textContent = "✨ " + oldValue.split(',').length + " date(s) programmée(s)";
                        helpDates.className = "text-xs font-bold text-emerald-600 mt-1";
                    }
                }
            } catch (e) {
                console.error('Erreur Flatpickr :', e);
            }
        }

        // 2. DONNÉES INJECTÉES POUR VAGUES & BUS
        const vaguesParTrajet = @json(($vagues ?? collect())->groupBy('trajet_id'));
        const ancienVagueId   = "{{ old('vague_depart_id') }}";
        const ancienBusId     = "{{ old('bus_id') }}";

        const selectTrajet = document.getElementById('trajet_id');
        const selectBus    = document.getElementById('bus_id');
        const selectVague  = document.getElementById('vague_depart_id');
        const helpVague    = document.getElementById('vague_help');
        const helpBus      = document.getElementById('bus_help');

        if (!selectTrajet || !selectVague || !selectBus) return;

        // 3. AUTO-SÉLECTION DU BUS
        function autoSelectionnerBus() {
            const opt = selectTrajet.options[selectTrajet.selectedIndex];
            if (!opt) return;

            const busId = opt.getAttribute('data-bus-id');

            if (ancienBusId && !selectTrajet.dataset.busAutoSet) {
                selectTrajet.dataset.busAutoSet = '1';
                return;
            }

            if (!busId) {
                if (helpBus) {
                    helpBus.textContent = "⚠️ Ce trajet n'a pas de bus assigné par défaut. Sélectionnez-en un.";
                    helpBus.className = "text-xs font-bold text-amber-600 mt-1";
                }
                return;
            }

            let trouve = false;
            for (let i = 0; i < selectBus.options.length; i++) {
                if (String(selectBus.options[i].value) === String(busId)) {
                    selectBus.selectedIndex = i;
                    trouve = true;
                    break;
                }
            }

            if (trouve && helpBus) {
                helpBus.textContent = "✅ Bus sélectionné automatiquement selon la ligne choisie.";
                helpBus.className = "text-xs font-bold text-emerald-600 mt-1";
            }
        }

        // 4. CHARGEMENT DES VAGUES
        function chargerVagues() {
            const trajetId = selectTrajet.value;
            selectVague.innerHTML = '';

            if (!trajetId) {
                selectVague.innerHTML = '<option value="">Sélectionnez d\'abord un trajet</option>';
                if (helpVague) {
                    helpVague.textContent = "Les horaires s'afficheront après la sélection du trajet.";
                    helpVague.className = 'text-xs font-bold text-slate-400 mt-1';
                }
                return;
            }

            const vagues = vaguesParTrajet[trajetId] || [];

            if (vagues.length === 0) {
                selectVague.innerHTML = '<option value="">Aucun horaire pour ce trajet</option>';
                if (helpVague) {
                    helpVague.textContent = "⚠️ Aucun horaire n'a été défini pour ce trajet.";
                    helpVague.className = 'text-xs font-bold text-amber-600 mt-1';
                }
                return;
            }

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = 'Sélectionner un horaire de départ';
            selectVague.appendChild(defaultOpt);

            vagues.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                const heure = v.heure_depart ? String(v.heure_depart).substring(0, 5) : '';
                opt.textContent = (v.nom_vague || 'Départ') + (heure ? ' — ' + heure : '');

                if (ancienVagueId && String(ancienVagueId) === String(v.id)) {
                    opt.selected = true;
                }
                selectVague.appendChild(opt);
            });

            if (helpVague) {
                helpVague.textContent = "✅ " + vagues.length + " horaire(s) disponible(s)";
                helpVague.className = 'text-xs font-bold text-emerald-600 mt-1';
            }
        }

        selectTrajet.addEventListener('change', function () {
            selectTrajet.dataset.busAutoSet = '';
            autoSelectionnerBus();
            chargerVagues();
        });

        chargerVagues();
        if (selectTrajet.value) {
            autoSelectionnerBus();
        }
    });
})();
</script>

@endsection
