@if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
        <strong>Erreur :</strong>
        <ul class="list-disc ml-5 mt-2 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-5">

    {{-- ============================================ --}}
    {{-- DÉPART                                       --}}
    {{-- ============================================ --}}
    <div class="border-l-4 border-emerald-500 pl-4">
        <h3 class="font-semibold text-emerald-700 mb-3">🚏 Point de départ</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Ville de départ
                </label>
                <input type="text" name="ville_depart"
                       value="{{ old('ville_depart', $trajet->ville_depart ?? '') }}"
                       placeholder="Ex: Brazzaville"
                       class="w-full p-2 border border-slate-300 rounded-lg @error('ville_depart') border-red-500 @enderror"
                       required>
                @error('ville_depart')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Gare de départ
                </label>
                <select name="gare_depart_id" id="gare_depart_id"
                        class="w-full p-2 border border-slate-300 rounded-lg bg-white @error('gare_depart_id') border-red-500 @enderror"
                        required>
                    <option value="">-- Choisir une gare --</option>
                    @foreach($gares as $gare)
                        <option value="{{ $gare->id }}"
                                data-ville="{{ $gare->ville }}"
                                {{ old('gare_depart_id', $trajet->gare_depart_id ?? '') == $gare->id ? 'selected' : '' }}>
                            {{ $gare->nom_gare }} @if($gare->ville) — {{ $gare->ville }} @endif
                        </option>
                    @endforeach
                </select>
                @error('gare_depart_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- ARRIVÉE                                      --}}
    {{-- ============================================ --}}
    <div class="border-l-4 border-blue-500 pl-4">
        <h3 class="font-semibold text-blue-700 mb-3">🏁 Point d'arrivée</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Ville d'arrivée
                </label>
                <input type="text" name="ville_arrivee"
                       value="{{ old('ville_arrivee', $trajet->ville_arrivee ?? '') }}"
                       placeholder="Ex: Pointe-Noire"
                       class="w-full p-2 border border-slate-300 rounded-lg @error('ville_arrivee') border-red-500 @enderror"
                       required>
                @error('ville_arrivee')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Gare d'arrivée
                </label>
                <select name="gare_arrivee_id" id="gare_arrivee_id"
                        class="w-full p-2 border border-slate-300 rounded-lg bg-white @error('gare_arrivee_id') border-red-500 @enderror"
                        required>
                    <option value="">-- Choisir une gare --</option>
                    @foreach($gares as $gare)
                        <option value="{{ $gare->id }}"
                                data-ville="{{ $gare->ville }}"
                                {{ old('gare_arrivee_id', $trajet->gare_arrivee_id ?? '') == $gare->id ? 'selected' : '' }}>
                            {{ $gare->nom_gare }} @if($gare->ville) — {{ $gare->ville }} @endif
                        </option>
                    @endforeach
                </select>
                @error('gare_arrivee_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- BUS (LISTE DÉROULANTE)                       --}}
    {{-- ============================================ --}}
    <div class="border-l-4 border-amber-500 pl-4">
        <h3 class="font-semibold text-amber-700 mb-3">🚌 Bus assigné</h3>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Sélectionner le bus
            </label>
            <select name="bus_id" id="bus_id"
                    class="w-full p-2 border border-slate-300 rounded-lg bg-white @error('bus_id') border-red-500 @enderror"
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
            <p class="text-xs text-slate-500 mt-1">
                Les bus proviennent de la section
                <a href="{{ route('admin.bus.index') }}" class="text-amber-600 hover:underline font-medium">
                    Gestion des Bus
                </a>.
            </p>
            @error('bus_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- HORAIRES DE DÉPART                           --}}
    {{-- ============================================ --}}
    <div class="border-l-4 border-purple-500 pl-4">
        <h3 class="font-semibold text-purple-700 mb-3">🕐 Horaires de départ</h3>
        <p class="text-xs text-slate-500 mb-3">
            Ajoutez un ou plusieurs horaires pour ce trajet.
        </p>

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
                <div class="flex gap-2 horaire-row">
                    <input type="time" name="horaires[]" value="{{ $h }}"
                           class="flex-1 p-2 border border-slate-300 rounded-lg" required>
                    <button type="button" onclick="this.parentElement.remove()"
                            class="px-3 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition">✕</button>
                </div>
            @endforeach
        </div>

        <button type="button" onclick="ajouterHoraire()"
                class="mt-3 text-sm text-purple-600 hover:text-purple-800 font-medium">
            + Ajouter un horaire
        </button>
    </div>

</div>

<script>
    function ajouterHoraire() {
        const container = document.getElementById('horaires-container');
        const div = document.createElement('div');
        div.className = 'flex gap-2 horaire-row';
        div.innerHTML = `
            <input type="time" name="horaires[]" value="08:00"
                   class="flex-1 p-2 border border-slate-300 rounded-lg" required>
            <button type="button" onclick="this.parentElement.remove()"
                    class="px-3 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition">✕</button>
        `;
        container.appendChild(div);
    }

    // Auto-remplissage des villes à partir de la gare sélectionnée
    document.addEventListener('DOMContentLoaded', function () {
        const selectDep = document.getElementById('gare_depart_id');
        const inputVilleDep = document.querySelector('input[name="ville_depart"]');

        const selectArr = document.getElementById('gare_arrivee_id');
        const inputVilleArr = document.querySelector('input[name="ville_arrivee"]');

        if (selectDep && inputVilleDep) {
            selectDep.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const ville = opt.getAttribute('data-ville');
                if (ville) inputVilleDep.value = ville;
            });
        }

        if (selectArr && inputVilleArr) {
            selectArr.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const ville = opt.getAttribute('data-ville');
                if (ville) inputVilleArr.value = ville;
            });
        }
    });
</script>