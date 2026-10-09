@if ($errors->any())
    <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg text-sm text-red-700">
        <i class="fa-solid fa-circle-exclamation mr-2"></i> {{ $errors->first() }}
    </div>
@endif

<div class="space-y-5">
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Nom de la vague</label>
        <div class="relative rounded-lg shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fa-solid fa-tag text-slate-400 text-sm"></i>
            </div>
            <input type="text" name="nom_vague" 
                   value="{{ old('nom_vague', $vague->nom_vague ?? '') }}" 
                   placeholder="Ex: Vague Matin"
                   class="block w-full rounded-lg border-slate-300 py-3 pl-10 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Heure de départ</label>
        <div class="relative rounded-lg shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fa-solid fa-clock text-slate-400 text-sm"></i>
            </div>
            <input type="time" name="heure_depart" 
                   value="{{ old('heure_depart', $vague->heure_depart ?? '') }}" 
                   class="block w-full rounded-lg border-slate-300 py-3 pl-10 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        </div>
    </div>
</div>
