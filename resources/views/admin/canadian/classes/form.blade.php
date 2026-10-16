@extends('layouts.admin')

@section('title', $classe->exists ? 'Classe ' . $classe->nom : 'Nouvelle classe')
@section('header_icon', 'fa-layer-group')
@section('header_title', $classe->exists ? 'Modifier la classe ' . $classe->nom : 'Nouvelle classe tarifaire')
@section('header_subtitle', 'Prix de la classe = prix du vol × coefficient')

@section('content')
@php
    $champ = 'w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#124231] focus:outline-none';
    $etiquette = 'block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5';
@endphp
<form method="POST" action="{{ $classe->exists ? route('canadian.classes.update', $classe->id) : route('canadian.classes.store') }}" class="space-y-6">
    @csrf
    @if($classe->exists) @method('PUT') @endif
    @include('admin.canadian.partials.erreurs')
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 max-w-2xl space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="{{ $etiquette }}">Nom *</label>
                <input name="nom" required maxlength="100" value="{{ old('nom', $classe->nom) }}" placeholder="Ex : Affaires" class="{{ $champ }}">
            </div>
            <div>
                <label class="{{ $etiquette }}">Code *</label>
                <input name="code" required maxlength="30" value="{{ old('code', $classe->code) }}" placeholder="Ex : affaires" class="{{ $champ }} font-mono">
            </div>
            <div>
                <label class="{{ $etiquette }}">Coefficient de prix *</label>
                <input type="number" name="multiplicateur" required min="0.5" max="10" step="0.05" value="{{ old('multiplicateur', $classe->multiplicateur) }}" class="{{ $champ }}">
            </div>
            <div>
                <label class="{{ $etiquette }}">Ordre d'affichage</label>
                <input type="number" name="ordre" min="0" max="99" value="{{ old('ordre', $classe->ordre) }}" class="{{ $champ }}">
            </div>
            <div class="sm:col-span-2">
                <label class="{{ $etiquette }}">Description</label>
                <textarea name="description" rows="3" maxlength="500" placeholder="Ex : Siège large, bagage 2 × 23 kg, salon" class="{{ $champ }}">{{ old('description', $classe->description) }}</textarea>
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="actif" value="1" @checked(old('actif', $classe->actif)) class="w-5 h-5 rounded accent-emerald-600">
                <span class="text-sm font-bold text-slate-700">Proposée aux clients</span>
            </label>
        </div>
        <div class="flex gap-3 pt-2">
            <a href="{{ route('canadian.classes.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm">Annuler</a>
            <button class="px-5 py-2.5 rounded-xl bg-[#124231] hover:bg-[#0c2f23] text-white font-bold text-sm">Enregistrer</button>
        </div>
    </div>
</form>
@endsection
