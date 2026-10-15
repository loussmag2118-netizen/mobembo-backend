@if ($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-sm text-rose-800 shadow-sm flex items-center gap-3">
        <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg shrink-0"></i>
        <span class="font-bold">Veuillez corriger les erreurs de saisie ci-dessous.</span>
    </div>
@endif

<div class="space-y-6">

    {{-- IMMATRICULATION --}}
    <div class="space-y-2">
        <label class="block text-sm font-extrabold text-slate-900">
            Plaque d'immatriculation <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fa-solid fa-id-card text-base"></i>
            </div>
            <input type="text" name="immatriculation"
                   value="{{ old('immatriculation', $bus->immatriculation ?? '') }}"
                   placeholder="Ex: 123-AB-45"
                   required
                   class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all uppercase font-mono">
        </div>
        @error('immatriculation')
            <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- MODÈLE --}}
    <div class="space-y-2">
        <label class="block text-sm font-extrabold text-slate-900">
            Modèle ou Marque du Bus <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fa-solid fa-bus text-base"></i>
            </div>
            <input type="text" name="modele"
                   value="{{ old('modele', $bus->modele ?? '') }}"
                   placeholder="Ex: Toyota Coaster, Marcopolo, Yutong"
                   required
                   class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all">
        </div>
        @error('modele')
            <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- CAPACITÉ --}}
    <div class="space-y-2">
        <label class="block text-sm font-extrabold text-slate-900">
            Capacité de passagers (Places assises) <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fa-solid fa-chair text-base"></i>
            </div>
            <input type="number" name="capacite_totale"
                   value="{{ old('capacite_totale', $bus->capacite_totale ?? '') }}"
                   placeholder="Ex: 30" min="1"
                   required
                   class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all">
        </div>
        @error('capacite_totale')
            <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- DISPOSITION DES SIÈGES (plan du bus pour le choix du siège) --}}
    <div class="space-y-2">
        <label class="block text-sm font-extrabold text-slate-900">
            Disposition des sièges
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fa-solid fa-table-cells text-base"></i>
            </div>
            @php $disposition = old('disposition_sieges', $bus->disposition_sieges ?? '2-2'); @endphp
            <select name="disposition_sieges"
                    class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all">
                @foreach (['2-2' => '2 + 2 (4 sièges par rangée)', '2-1' => '2 + 1 (VIP, 3 par rangée)', '3-2' => '3 + 2 (5 par rangée)', '1-1' => '1 + 1 (minibus)'] as $valeur => $libelle)
                    <option value="{{ $valeur }}" @selected($disposition === $valeur)>{{ $libelle }}</option>
                @endforeach
            </select>
        </div>
        <p class="text-xs text-slate-400 font-semibold">
            Sièges à gauche + à droite du couloir. Sert à dessiner le plan du bus quand le client choisit son siège.
        </p>
        @error('disposition_sieges')
            <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- STATUT --}}
    <div class="space-y-2">
        <label class="block text-sm font-extrabold text-slate-900">
            Statut opérationnel du véhicule <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fa-solid fa-shield-halved text-base"></i>
            </div>
            <select name="statut" required
                    class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all">
                <option value="Disponible"   {{ old('statut', $bus->statut ?? 'Disponible') == 'Disponible'   ? 'selected' : '' }}>🟢 Disponible en rotation</option>
                <option value="Maintenance"  {{ old('statut', $bus->statut ?? '') == 'Maintenance'  ? 'selected' : '' }}>🟡 En maintenance / Révision</option>
                <option value="Indisponible" {{ old('statut', $bus->statut ?? '') == 'Indisponible' ? 'selected' : '' }}>🔴 Indisponible / Hors service</option>
            </select>
        </div>
        @error('statut')
            <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

</div>