<div class="space-y-6">
    <!-- Champ Nom / Libellé -->
    <div class="space-y-2">
        <label for="nom" class="block text-sm font-semibold text-slate-700">Nom / Libellé</label>
        <input type="text" class="w-full px-4 py-2.5 rounded-lg border @error('nom') border-red-500 bg-red-50/50 text-red-900 focus:ring-red-500 focus:border-red-500 @else border-slate-300 bg-white text-slate-900 focus:ring-sky-500 focus:border-sky-500 @enderror focus:outline-none focus:ring-2 transition text-sm" id="nom" name="nom" value="{{ old('nom', $item->nom ?? '') }}" required>
        @error('nom')
            <p class="text-xs text-red-600 font-medium mt-1"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
        @enderror
    </div>

    <!-- Champ Description / Détails -->
    <div class="space-y-2">
        <label for="description" class="block text-sm font-semibold text-slate-700">Description / Détails</label>
        <textarea class="w-full px-4 py-2.5 rounded-lg border @error('description') border-red-500 bg-red-50/50 text-red-900 focus:ring-red-500 focus:border-red-500 @else border-slate-300 bg-white text-slate-900 focus:ring-sky-500 focus:border-sky-500 @enderror focus:outline-none focus:ring-2 transition text-sm" id="description" name="description" rows="3">{{ old('description', $item->description ?? '') }}</textarea>
        @error('description')
            <p class="text-xs text-red-600 font-medium mt-1"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
        @enderror
    </div>
</div>
