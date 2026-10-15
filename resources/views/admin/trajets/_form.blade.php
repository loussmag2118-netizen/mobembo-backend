@if($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-sm text-rose-800 shadow-sm flex items-start gap-3">
        <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg mt-0.5 shrink-0"></i>
        <div>
            <p class="font-extrabold text-sm mb-1">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc ml-5 space-y-1 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="space-y-7">

    {{-- 1. POINT DE DÉPART --}}
    <div class="p-5 rounded-2xl bg-emerald-50/50 border border-emerald-100 space-y-4">
        <div class="flex items-center gap-2 text-emerald-800 font-extrabold text-sm">
            <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
                <i class="fa-solid fa-arrow-up-from-bracket"></i>
            </span>
            <span>Point de Départ</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Ville de départ
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-city text-sm"></i>
                    </div>
                    @php $gareChoisie = $gares->firstWhere('id', (int) old('gare_depart_id', $trajet->gare_depart_id ?? 0)); @endphp
                    <div id="ville_depart_affichee"
                         class="w-full pl-11 pr-4 py-3 bg-slate-100 border border-slate-200 rounded-2xl text-sm font-bold text-slate-700">
                        {{ $gareChoisie->ville ?? '— choisissez une agence —' }}
                    </div>
                </div>
                <p class="text-[11px] text-slate-500">Déduite automatiquement de l'agence.</p>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Agence de départ <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-map-pin text-sm"></i>
                    </div>
                    <select name="gare_depart_id" id="gare_depart_id"
                            class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all @error('gare_depart_id') border-rose-300 ring-rose-500 @enderror"
                            required>
                        <option value="">-- Choisir une agence --</option>
                        @foreach($gares->groupBy('ville') as $nomVille => $garesVille)
                            <optgroup label="{{ $nomVille }}">
                                @foreach($garesVille as $gare)
                                    <option value="{{ $gare->id }}"
                                            data-ville="{{ $gare->ville }}"
                                            {{ old('gare_depart_id', $trajet->gare_depart_id ?? '') == $gare->id ? 'selected' : '' }}>
                                        {{ $gare->nom_gare }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                @error('gare_depart_id')
                    <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- 2. POINT D'ARRIVÉE --}}
    <div class="p-5 rounded-2xl bg-blue-50/50 border border-blue-100 space-y-4">
        <div class="flex items-center gap-2 text-blue-800 font-extrabold text-sm">
            <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs">
                <i class="fa-solid fa-location-dot"></i>
            </span>
            <span>Point d'Arrivée & Destination</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Ville d'arrivée
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-city text-sm"></i>
                    </div>
                    @php $gareChoisie = $gares->firstWhere('id', (int) old('gare_arrivee_id', $trajet->gare_arrivee_id ?? 0)); @endphp
                    <div id="ville_arrivee_affichee"
                         class="w-full pl-11 pr-4 py-3 bg-slate-100 border border-slate-200 rounded-2xl text-sm font-bold text-slate-700">
                        {{ $gareChoisie->ville ?? '— choisissez une agence —' }}
                    </div>
                </div>
                <p class="text-[11px] text-slate-500">Déduite automatiquement de l'agence.</p>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Agence d'arrivée <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-map-pin text-sm"></i>
                    </div>
                    <select name="gare_arrivee_id" id="gare_arrivee_id"
                            class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all @error('gare_arrivee_id') border-rose-300 ring-rose-500 @enderror"
                            required>
                        <option value="">-- Choisir une agence --</option>
                        @foreach($gares->groupBy('ville') as $nomVille => $garesVille)
                            <optgroup label="{{ $nomVille }}">
                                @foreach($garesVille as $gare)
                                    <option value="{{ $gare->id }}"
                                            data-ville="{{ $gare->ville }}"
                                            {{ old('gare_arrivee_id', $trajet->gare_arrivee_id ?? '') == $gare->id ? 'selected' : '' }}>
                                        {{ $gare->nom_gare }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                @error('gare_arrivee_id')
                    <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- 3. BUS ASSIGNÉ PAR DÉFAUT --}}
    <div class="space-y-2">
        <label class="block text-sm font-extrabold text-slate-900">
            Bus assigné par défaut <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fa-solid fa-bus text-base"></i>
            </div>
            <select name="bus_id" id="bus_id"
                    class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none transition-all @error('bus_id') border-rose-300 ring-rose-500 @enderror"
                    required>
                <option value="">-- Choisir un bus --</option>
                @forelse($buses as $bus)
                    @php
                        $statut = $bus->statut ?? 'Disponible';
                        $emoji = $statut === 'Disponible' ? '✅' : ($statut === 'Maintenance' ? '🔧' : '❌');
                    @endphp
                    <option value="{{ $bus->id }}"
                            {{ old('bus_id', $trajet->bus_id ?? '') == $bus->id ? 'selected' : '' }}>
                        {{ $emoji }}
                        {{ $bus->immatriculation ?? 'Bus #'.$bus->id }}
                        @if($bus->modele) — {{ $bus->modele }} @endif
                        @if($bus->capacite_totale) ({{ $bus->capacite_totale }} places) @endif
                        — {{ $statut }}
                    </option>
                @empty
                    <option value="" disabled>⚠️ Aucun bus enregistré pour cette agence</option>
                @endforelse
            </select>
        </div>
        <p class="text-xs text-slate-400 font-semibold mt-1">
            Les véhicules enregistrés dans votre flotte.
        </p>
        @error('bus_id')
            <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- INFORMATIONS POUR LE CLIENT (facultatives) --}}
    <div class="space-y-4 rounded-2xl border border-slate-200 bg-slate-50/60 p-5">
        <div>
            <p class="text-sm font-extrabold text-slate-900">Informations pour le voyageur</p>
            <p class="text-xs text-slate-500 font-semibold mt-0.5">Facultatif — affiché dans l'application uniquement si renseigné.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @php
                $dureeTotale = old('duree_heures') !== null || old('duree_mins') !== null
                    ? null
                    : ($trajet->duree_minutes ?? null);
                $dureeH = old('duree_heures', $dureeTotale !== null ? intdiv($dureeTotale, 60) : '');
                $dureeM = (int) old('duree_mins', $dureeTotale !== null ? $dureeTotale % 60 : 0);
            @endphp
            <div class="space-y-2">
                <label for="duree_heures" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Durée estimée</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="duree_heures" id="duree_heures" min="0" max="72" step="1" inputmode="numeric"
                           value="{{ $dureeH }}" placeholder="5"
                           class="w-24 px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <span class="text-sm font-bold text-slate-500">h</span>
                    <select name="duree_mins" aria-label="Minutes"
                            class="w-24 px-3 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                        @foreach(range(0, 55, 5) as $m)
                            <option value="{{ $m }}" @selected($dureeM === $m)>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                        @endforeach
                    </select>
                    <span class="text-sm font-bold text-slate-500">min</span>
                </div>
                <p class="text-xs text-slate-500 font-semibold">Ex. 5 h 30 pour Brazzaville → Pointe-Noire. Laissez les heures vides si inconnue.</p>
                @error('duree_minutes') <p class="text-rose-600 text-xs font-bold">{{ $message }}</p> @enderror
                @error('duree_heures') <p class="text-rose-600 text-xs font-bold">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-2">
                <label for="bagages_autorises" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Bagages autorisés</label>
                <input type="text" name="bagages_autorises" id="bagages_autorises" maxlength="255"
                       value="{{ old('bagages_autorises', $trajet->bagages_autorises ?? '') }}" placeholder="Ex. 1 sac de voyage par passager"
                       class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                @error('bagages_autorises') <p class="text-rose-600 text-xs font-bold">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="space-y-2">
            <label for="conditions_voyage" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Conditions du voyage (annulation, modification…)</label>
            <textarea name="conditions_voyage" id="conditions_voyage" rows="3" maxlength="2000"
                      placeholder="Ex. Présentez-vous 30 minutes avant le départ. Billet non remboursable sauf annulation du départ par l'agence."
                      class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-teal-500 focus:outline-none">{{ old('conditions_voyage', $trajet->conditions_voyage ?? '') }}</textarea>
            @error('conditions_voyage') <p class="text-rose-600 text-xs font-bold">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- 4. HORAIRES DE DÉPART (VAGUES) --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <label class="block text-sm font-extrabold text-slate-900">
                    Horaires réguliers de départ
                </label>
                <p class="text-xs text-slate-400 font-semibold">
                    Définissez les créneaux horaires habituels pour ce trajet.
                </p>
            </div>
            <button type="button" onclick="ajouterHoraire()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 text-xs font-bold border border-purple-200/60 transition-all">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Ajouter un horaire</span>
            </button>
        </div>

        <div id="horaires-container" class="space-y-2">
            @php
                $oldHoraires = old('horaires');
                if (!$oldHoraires && isset($trajet) && $trajet->vagues) {
                    $oldHoraires = $trajet->vagues->pluck('heure_depart')->toArray();
                    $oldHoraires = array_map(fn($h) => substr($h, 0, 5), $oldHoraires);
                }
                if (!$oldHoraires || count($oldHoraires) === 0) {
                    $oldHoraires = ['08:00'];
                }
            @endphp

            @foreach($oldHoraires as $h)
                <div class="flex items-center gap-2 horaire-row">
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-regular fa-clock text-sm"></i>
                        </div>
                        <input type="time" name="horaires[]" value="{{ $h }}"
                               class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-purple-500 focus:outline-none transition-all font-mono" required>
                    </div>
                    <button type="button" onclick="this.closest('.horaire-row').remove()"
                            class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition-all border border-rose-200/50"
                            title="Supprimer cet horaire">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </div>
            @endforeach
        </div>
    </div>

</div>

<script>
    function ajouterHoraire() {
        const container = document.getElementById('horaires-container');
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 horaire-row';
        div.innerHTML = `
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <i class="fa-regular fa-clock text-sm"></i>
                </div>
                <input type="time" name="horaires[]" value="08:00"
                       class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-purple-500 focus:outline-none transition-all font-mono" required>
            </div>
            <button type="button" onclick="this.closest('.horaire-row').remove()"
                    class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition-all border border-rose-200/50"
                    title="Supprimer cet horaire">
                <i class="fa-solid fa-trash text-xs"></i>
            </button>
        `;
        container.appendChild(div);
    }

    // Affiche la ville de la gare sélectionnée (les villes ne sont plus saisies)
    document.addEventListener('DOMContentLoaded', function () {
        [['gare_depart_id', 'ville_depart_affichee'], ['gare_arrivee_id', 'ville_arrivee_affichee']]
            .forEach(function ([selectId, affichageId]) {
                const select = document.getElementById(selectId);
                const affichage = document.getElementById(affichageId);
                if (!select || !affichage) return;

                select.addEventListener('change', function () {
                    const opt = this.options[this.selectedIndex];
                    affichage.textContent = opt.getAttribute('data-ville') || '— choisissez une agence —';
                });
            });
    });
</script>