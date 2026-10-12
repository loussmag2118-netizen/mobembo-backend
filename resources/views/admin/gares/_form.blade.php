@if ($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-sm text-rose-800 shadow-sm flex items-center gap-3">
        <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg shrink-0"></i>
        <span class="font-bold">{{ $errors->first() }}</span>
    </div>
@endif

<div class="space-y-6">

    {{-- NOM DE LA GARE --}}
    <div class="space-y-2">
        <label class="block text-sm font-extrabold text-slate-900">
            Nom de la gare ou du terminal <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fa-solid fa-location-dot text-base"></i>
            </div>
            <input type="text" name="nom_gare"
                   value="{{ old('nom_gare', $gare->nom_gare ?? '') }}"
                   placeholder="Ex: Gare Centrale Mpila, Agence Moukondo, Gare Océan du Nord"
                   required
                   class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none transition-all">
        </div>
        @error('nom_gare')
            <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- VILLE --}}
    <div class="space-y-2">
        <label class="block text-sm font-extrabold text-slate-900">
            Ville / Agglomération <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fa-solid fa-city text-base"></i>
            </div>
            <select name="ville_id"
                    required
                    class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none transition-all">
                @php $selected = (int) old('ville_id', $gare->ville_id ?? 0); @endphp
                <option value="">-- Choisir une ville --</option>
                @foreach($villes as $ville)
                    <option value="{{ $ville->id }}" {{ $selected === $ville->id ? 'selected' : '' }}>
                        {{ $ville->nom }}@unless($ville->actif) (désactivée)@endunless
                    </option>
                @endforeach
            </select>
        </div>
        <p class="text-[11px] text-slate-500">
            <i class="fa-solid fa-circle-info"></i>
            La liste des villes est gérée par le Super Admin. Ville manquante ? Demandez-lui de l'ajouter.
        </p>
        @error('ville_id')
            <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

</div>