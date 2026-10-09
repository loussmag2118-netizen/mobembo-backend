@if ($errors->any())
    <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg text-sm text-red-700">
        <i class="fa-solid fa-circle-exclamation mr-2"></i> Veuillez corriger les erreurs ci-dessous.
    </div>
@endif

<div class="space-y-5">

    {{-- IMMATRICULATION --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Plaque d'immatriculation <span class="text-red-500">*</span>
        </label>
        <div class="relative rounded-lg shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fa-solid fa-id-card text-slate-400 text-sm"></i>
            </div>
            <input type="text" name="immatriculation"
                   value="{{ old('immatriculation', $bus->immatriculation ?? '') }}"
                   placeholder="Ex: 123-AB-45"
                   required
                   class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm {{ $errors->has('immatriculation') ? 'border-red-300 ring-red-500 focus:ring-red-600' : 'border-slate-300 ring-slate-300 focus:ring-indigo-600' }}">
        </div>
        @error('immatriculation')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- MODÈLE --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Modèle ou Marque du Bus <span class="text-red-500">*</span>
        </label>
        <div class="relative rounded-lg shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fa-solid fa-truck-monster text-slate-400 text-sm"></i>
            </div>
            <input type="text" name="modele"
                   value="{{ old('modele', $bus->modele ?? '') }}"
                   placeholder="Ex: Toyota Coaster, Marcopolo"
                   required
                   class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm {{ $errors->has('modele') ? 'border-red-300 ring-red-500 focus:ring-red-600' : 'border-slate-300 ring-slate-300 focus:ring-indigo-600' }}">
        </div>
        @error('modele')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- CAPACITÉ --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Capacité de passagers <span class="text-red-500">*</span>
        </label>
        <div class="relative rounded-lg shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fa-solid fa-chair text-slate-400 text-sm"></i>
            </div>
            <input type="number" name="capacite_totale"
                   value="{{ old('capacite_totale', $bus->capacite_totale ?? '') }}"
                   placeholder="Ex: 30" min="1"
                   required
                   class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm {{ $errors->has('capacite_totale') ? 'border-red-300 ring-red-500 focus:ring-red-600' : 'border-slate-300 ring-slate-300 focus:ring-indigo-600' }}">
        </div>
        @error('capacite_totale')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- STATUT --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Statut du bus <span class="text-red-500">*</span>
        </label>
        <div class="relative rounded-lg shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fa-solid fa-circle-info text-slate-400 text-sm"></i>
            </div>
            <select name="statut" required
                    class="block w-full rounded-lg py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset sm:text-sm {{ $errors->has('statut') ? 'border-red-300 ring-red-500 focus:ring-red-600' : 'border-slate-300 ring-slate-300 focus:ring-indigo-600' }}">
                <option value="Disponible"   {{ old('statut', $bus->statut ?? 'Disponible') == 'Disponible'   ? 'selected' : '' }}>✅ Disponible</option>
                <option value="Maintenance"  {{ old('statut', $bus->statut ?? '') == 'Maintenance'  ? 'selected' : '' }}>🔧 Maintenance</option>
                <option value="Indisponible" {{ old('statut', $bus->statut ?? '') == 'Indisponible' ? 'selected' : '' }}>❌ Indisponible</option>
            </select>
        </div>
        @error('statut')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

</div>