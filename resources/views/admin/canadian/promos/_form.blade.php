@php
    $champ = 'w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#124231] focus:outline-none';
    $etiquette = 'block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5';
@endphp
@include('admin.canadian.partials.erreurs')
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 max-w-2xl space-y-4" x-data="{ type: @js(old('type', $promo->type)) }">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="{{ $etiquette }}">Code *</label>
            <input name="code" required maxlength="30" value="{{ old('code', $promo->code) }}" placeholder="Ex : RENTREE10" class="{{ $champ }} font-mono font-bold uppercase">
        </div>
        <div>
            <label class="{{ $etiquette }}">Type de réduction *</label>
            <select name="type" x-model="type" class="{{ $champ }}">
                <option value="pourcentage">Pourcentage du prix</option>
                <option value="fixe">Montant fixe</option>
            </select>
        </div>
        <div>
            <label class="{{ $etiquette }}" x-text="type === 'pourcentage' ? 'Réduction (%) *' : 'Réduction (FCFA) *'"></label>
            <input type="number" name="valeur" required min="1" step="1" value="{{ old('valeur', $promo->valeur !== null ? (int) $promo->valeur : null) }}" class="{{ $champ }}">
        </div>
        <div>
            <label class="{{ $etiquette }}">Valable du *</label>
            <input type="date" name="date_debut" required value="{{ old('date_debut', $promo->date_debut?->format('Y-m-d')) }}" class="{{ $champ }}">
        </div>
        <div>
            <label class="{{ $etiquette }}">au *</label>
            <input type="date" name="date_fin" required value="{{ old('date_fin', $promo->date_fin?->format('Y-m-d')) }}" class="{{ $champ }}">
        </div>
        <div>
            <label class="{{ $etiquette }}">Nombre d'utilisations max.</label>
            <input type="number" name="utilisation_max" min="1" value="{{ old('utilisation_max', $promo->utilisation_max) }}" placeholder="Illimité" class="{{ $champ }}">
        </div>
        <label class="flex items-center gap-3 self-end pb-2.5 cursor-pointer">
            <input type="checkbox" name="actif" value="1" @checked(old('actif', $promo->actif)) class="w-5 h-5 rounded accent-emerald-600">
            <span class="text-sm font-bold text-slate-700">Code actif</span>
        </label>
    </div>
    <div class="flex gap-3 pt-2">
        <a href="{{ route('canadian.promos.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm">Annuler</a>
        <button class="px-5 py-2.5 rounded-xl bg-[#124231] hover:bg-[#0c2f23] text-white font-bold text-sm">{{ $promo->exists ? 'Enregistrer' : 'Créer le code' }}</button>
    </div>
</div>
