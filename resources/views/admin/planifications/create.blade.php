@extends('layouts.admin')

@section('content')

{{-- ============================================================ --}}
{{-- FLATPICKR (Calendrier multi-dates)                           --}}
{{-- ============================================================ --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>

<div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">
            Planifier un nouveau voyage
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
        <form action="{{ route('admin.planifications.store') }}" method="POST" class="space-y-5" id="planif_form">
            @csrf

            {{-- TRAJET --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Trajet</label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-route text-slate-400 text-sm"></i>
                    </div>
                    <select name="trajet_id" id="trajet_id"
                            class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm {{ $errors->has('trajet_id') ? 'border-red-300 ring-red-500 focus:ring-red-600' : 'border-slate-300 ring-slate-300 focus:ring-indigo-600' }}"
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
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- BUS (auto-sélectionné depuis le trajet) --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Bus assigné
                </label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-bus text-slate-400 text-sm"></i>
                    </div>
                    <select name="bus_id" id="bus_id"
                            class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm {{ $errors->has('bus_id') ? 'border-red-300 ring-red-500 focus:ring-red-600' : 'border-slate-300 ring-slate-300 focus:ring-indigo-600' }}"
                            required>
                        <option value="">Sélectionner un bus</option>
                        @forelse($buses as $bus)
                            @php
                                $statut = $bus->statut ?? 'Disponible';
                                $emoji = $statut === 'Disponible' ? '✅' : ($statut === 'Maintenance' ? '🔧' : '❌');
                            @endphp
                            <option value="{{ $bus->id }}"
                                    {{ old('bus_id') == $bus->id ? 'selected' : '' }}>
                                {{ $emoji }}
                                {{ $bus->immatriculation ?? $bus->numero ?? 'Bus #'.$bus->id }}
                                @if($bus->modele) • {{ $bus->modele }} @endif
                                • {{ $bus->capacite_totale ?? $bus->places ?? $bus->nombre_places ?? '?' }} places
                                — {{ $statut }}
                            </option>
                        @empty
                            <option value="" disabled>⚠️ Aucun bus enregistré pour cette agence</option>
                        @endforelse
                    </select>
                </div>
                <p id="bus_help" class="mt-1 text-xs text-slate-500">
                    Le bus est automatiquement pré-sélectionné selon le trajet choisi.
                </p>
                @error('bus_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- VAGUE DE DÉPART (HORAIRE) --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Vague de départ (Horaire)
                </label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-clock text-slate-400 text-sm"></i>
                    </div>
                    <select name="vague_depart_id" id="vague_depart_id"
                            class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm {{ $errors->has('vague_depart_id') ? 'border-red-300 ring-red-500 focus:ring-red-600' : 'border-slate-300 ring-slate-300 focus:ring-indigo-600' }}"
                            required>
                        <option value="">Sélectionnez d'abord un trajet</option>
                    </select>
                </div>
                <p id="vague_help" class="mt-1 text-xs text-slate-500">
                    Les horaires s'afficheront après la sélection du trajet.
                </p>
                @error('vague_depart_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- DATES DU VOYAGE --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Dates du voyage <span class="text-red-500">*</span>
                </label>
                <p class="text-xs text-slate-500 mb-2">
                    Cliquez sur une ou plusieurs dates dans le calendrier. Chaque date créera une planification.
                </p>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 z-10">
                        <i class="fa-solid fa-calendar text-slate-400 text-sm"></i>
                    </div>
                    <input type="text" id="dates_picker" name="dates_voyage"
                           value="{{ old('dates_voyage') }}"
                           placeholder="Cliquez pour ouvrir le calendrier"
                           class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm cursor-pointer {{ $errors->has('dates_voyage') ? 'border-red-300 ring-red-500' : 'border-slate-300 ring-slate-300 focus:ring-indigo-600' }}"
                           readonly
                           required>
                </div>
                <p id="dates_help" class="mt-1 text-xs text-slate-500">
                    Aucune date sélectionnée
                </p>
                @error('dates_voyage')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- NOMBRE DE TICKETS TOTAL PERSONNALISÉ (Optionnel) --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Nombre de tickets personnalisé (Optionnel)
                </label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-ticket text-slate-400 text-sm"></i>
                    </div>
                    <input type="number" name="nombre_tickets_total" id="nombre_tickets_total"
                           value="{{ old('nombre_tickets_total') }}" min="1"
                           placeholder="Laisser vide pour utiliser la capacité totale par défaut du bus"
                           class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm border-slate-300 ring-slate-300 focus:ring-indigo-600">
                </div>
                <p class="mt-1 text-xs text-slate-500">
                    Définit le quota maximal de places vendables pour ce voyage spécifique.
                </p>
                @error('nombre_tickets_total')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- PRIX DU VOYAGE (Optionnel) --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Prix du voyage (FCFA) (Optionnel)
                </label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-money-bill text-slate-400 text-sm"></i>
                    </div>
                    <input type="number" step="0.01" name="prix" id="prix"
                           value="{{ old('prix') }}" min="0"
                           placeholder="Ex: 15000"
                           class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm border-slate-300 ring-slate-300 focus:ring-indigo-600">
                </div>
                <p class="mt-1 text-xs text-slate-500">
                    Laissez vide si vous souhaitez appliquer la tarification standard ou par défaut.
                </p>
                @error('prix')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- ACTIONS --}}
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('admin.planifications.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2.5 rounded-lg text-sm font-medium">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-lg text-sm font-medium">
                    Enregistrer les planifications
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================ --}}
{{-- SCRIPTS                                                      --}}
{{-- ============================================================ --}}
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
                            helpDates.className = "mt-1 text-xs text-slate-500";
                        } else {
                            helpDates.textContent = selectedDates.length + " date(s) sélectionnée(s)";
                            helpDates.className = "mt-1 text-xs text-emerald-600 font-medium";
                        }
                    }
                });

                const oldValue = "{{ old('dates_voyage') }}";
                if (oldValue) {
                    picker.setDate(oldValue.split(','), false);
                    if (helpDates) {
                        helpDates.textContent = oldValue.split(',').length + " date(s) sélectionnée(s)";
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
                    helpBus.textContent = "⚠️ Ce trajet n'a pas de bus assigné. Choisissez-en un manuellement.";
                    helpBus.className = "mt-1 text-xs text-amber-600 font-medium";
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
                helpBus.textContent = "✅ Bus pré-sélectionné automatiquement selon le trajet.";
                helpBus.className = "mt-1 text-xs text-emerald-600 font-medium";
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
                    helpVague.className = 'mt-1 text-xs text-slate-500';
                }
                return;
            }

            const vagues = vaguesParTrajet[trajetId] || [];

            if (vagues.length === 0) {
                selectVague.innerHTML = '<option value="">Aucun horaire pour ce trajet</option>';
                if (helpVague) {
                    helpVague.textContent = "⚠️ Aucun horaire n'a été défini pour ce trajet.";
                    helpVague.className = 'mt-1 text-xs text-amber-600 font-medium';
                }
                return;
            }

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = 'Sélectionner un horaire';
            selectVague.appendChild(defaultOpt);

            vagues.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                const heure = v.heure_depart ? String(v.heure_depart).substring(0, 5) : '';
                opt.textContent = (v.nom_vague || 'Départ') + (heure ? ' - ' + heure : '');

                if (ancienVagueId && String(ancienVagueId) === String(v.id)) {
                    opt.selected = true;
                }
                selectVague.appendChild(opt);
            });

            if (helpVague) {
                helpVague.textContent = vagues.length + ' horaire(s) disponible(s)';
                helpVague.className = 'mt-1 text-xs text-emerald-600';
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
