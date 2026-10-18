@php
    $champ = 'w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#124231] focus:outline-none';
    $etiquette = 'block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5';
@endphp
@include('admin.canadian.partials.erreurs')
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 max-w-2xl space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="{{ $etiquette }}">Modèle *</label>
            <input name="modele" required maxlength="100" value="{{ old('modele', $flotte->modele) }}" placeholder="Ex : Boeing 737-800" class="{{ $champ }}">
        </div>
        <div>
            <label class="{{ $etiquette }}">Immatriculation *</label>
            <input name="immatriculation" required maxlength="20" value="{{ old('immatriculation', $flotte->immatriculation) }}" placeholder="Ex : TN-AJB" class="{{ $champ }} font-mono uppercase">
        </div>
        <div>
            <label class="{{ $etiquette }}">Nombre de sièges *</label>
            <input type="number" name="capacite_places" required min="1" max="900" value="{{ old('capacite_places', $flotte->capacite_places) }}" class="{{ $champ }}">
        </div>
        <div>
            <label class="{{ $etiquette }}">Année de mise en service</label>
            <input type="number" name="annee" min="1950" max="{{ date('Y') + 1 }}" value="{{ old('annee', $flotte->annee) }}" class="{{ $champ }}">
        </div>
        <div>
            <label class="{{ $etiquette }}">État *</label>
            <select name="statut" class="{{ $champ }}">
                @foreach(\App\Http\Controllers\Admin\Canadian\FlotteController::STATUTS as $cle => $libelle)
                    <option value="{{ $cle }}" @selected(old('statut', $flotte->statut) === $cle)>{{ $libelle }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="flex gap-3 pt-2">
        <a href="{{ route('canadian.flotte.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm">Annuler</a>
        <button class="px-5 py-2.5 rounded-xl bg-[#124231] hover:bg-[#0c2f23] text-white font-bold text-sm">{{ $flotte->exists ? 'Enregistrer' : 'Ajouter l\'avion' }}</button>
    </div>
</div>
