@if ($errors->any())
    <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg text-sm text-red-700">
        <i class="fa-solid fa-circle-exclamation mr-2"></i> {{ $errors->first() }}
    </div>
@endif

<div class="space-y-5">

    {{-- NOM DE LA GARE --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Nom de la gare <span class="text-red-500">*</span>
        </label>
        <div class="relative rounded-lg shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fa-solid fa-location-dot text-slate-400 text-sm"></i>
            </div>
            <input type="text" name="nom_gare"
                   value="{{ old('nom_gare', $gare->nom_gare ?? '') }}"
                   placeholder="Ex: Gare Centrale, Gare de Bacongo"
                   required
                   class="block w-full rounded-lg border-slate-300 py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm {{ $errors->has('nom_gare') ? 'border-red-300 ring-red-500' : '' }}">
        </div>
        @error('nom_gare')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- VILLE --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Ville <span class="text-red-500">*</span>
        </label>
        <div class="relative rounded-lg shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fa-solid fa-city text-slate-400 text-sm"></i>
            </div>
            <select name="ville"
                    required
                    class="block w-full rounded-lg border-slate-300 py-3 pl-10 pr-4 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm {{ $errors->has('ville') ? 'border-red-300 ring-red-500' : '' }}">
                @php $selected = old('ville', $gare->ville ?? ''); @endphp
                <option value="">-- Choisir une ville --</option>
                <option value="Brazzaville"   {{ $selected === 'Brazzaville'   ? 'selected' : '' }}>Brazzaville</option>
                <option value="Pointe-Noire"  {{ $selected === 'Pointe-Noire'  ? 'selected' : '' }}>Pointe-Noire</option>
                <option value="Dolisie"       {{ $selected === 'Dolisie'       ? 'selected' : '' }}>Dolisie</option>
                <option value="Nkayi"         {{ $selected === 'Nkayi'         ? 'selected' : '' }}>Nkayi</option>
                <option value="Ouesso"        {{ $selected === 'Ouesso'        ? 'selected' : '' }}>Ouesso</option>
            </select>
        </div>
        @error('ville')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

</div>