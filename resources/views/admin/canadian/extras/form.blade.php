@extends('layouts.admin')

@section('title', $extra->exists ? $extra->nom : 'Nouvelle option')
@section('header_icon', 'fa-suitcase-rolling')
@section('header_title', $extra->exists ? 'Modifier : ' . $extra->nom : 'Nouvelle option')
@section('header_subtitle', 'Bagage, siège, repas, assurance…')

@section('content')
@php
    $champ = 'w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#124231] focus:outline-none';
    $etiquette = 'block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5';
@endphp
<form method="POST" action="{{ $extra->exists ? route('canadian.extras.update', $extra->id) : route('canadian.extras.store') }}" class="space-y-6">
    @csrf
    @if($extra->exists) @method('PUT') @endif
    @include('admin.canadian.partials.erreurs')
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 max-w-2xl space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="{{ $etiquette }}">Nom *</label>
                <input name="nom" required maxlength="150" value="{{ old('nom', $extra->nom) }}" placeholder="Ex : Bagage en soute 23 kg" class="{{ $champ }}">
            </div>
            <div>
                <label class="{{ $etiquette }}">Code *</label>
                <input name="code" required maxlength="50" value="{{ old('code', $extra->code) }}" placeholder="Ex : bagage_23kg" class="{{ $champ }} font-mono">
            </div>
            <div>
                <label class="{{ $etiquette }}">Catégorie *</label>
                <select name="categorie" class="{{ $champ }}">
                    @foreach(\App\Http\Controllers\Admin\Canadian\ExtraController::CATEGORIES as $cle => [$libelle])
                        <option value="{{ $cle }}" @selected(old('categorie', $extra->categorie) === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $etiquette }}">Prix (FCFA) *</label>
                <input type="number" name="prix" required min="0" step="1" value="{{ old('prix', $extra->prix !== null ? (int) $extra->prix : null) }}" class="{{ $champ }}">
            </div>
            <div class="sm:col-span-2">
                <label class="{{ $etiquette }}">Description</label>
                <textarea name="description" rows="3" maxlength="500" class="{{ $champ }}">{{ old('description', $extra->description) }}</textarea>
            </div>
            <div>
                <label class="{{ $etiquette }}">Ordre d'affichage</label>
                <input type="number" name="ordre" min="0" max="99" value="{{ old('ordre', $extra->ordre) }}" class="{{ $champ }}">
            </div>
            <label class="flex items-center gap-3 self-end pb-2.5 cursor-pointer">
                <input type="checkbox" name="actif" value="1" @checked(old('actif', $extra->actif)) class="w-5 h-5 rounded accent-emerald-600">
                <span class="text-sm font-bold text-slate-700">Proposée aux clients</span>
            </label>
        </div>
        <div class="flex gap-3 pt-2">
            <a href="{{ route('canadian.extras.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm">Annuler</a>
            <button class="px-5 py-2.5 rounded-xl bg-[#124231] hover:bg-[#0c2f23] text-white font-bold text-sm">Enregistrer</button>
        </div>
    </div>
</form>
@endsection
