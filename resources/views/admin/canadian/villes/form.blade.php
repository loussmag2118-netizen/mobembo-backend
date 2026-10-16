@extends('layouts.admin')

@section('title', $ville->exists ? 'Aéroport ' . $ville->code_iata : 'Nouvel aéroport')
@section('header_icon', 'fa-plane-arrival')
@section('header_title', $ville->exists ? 'Modifier l\'aéroport ' . $ville->code_iata : 'Nouvel aéroport')
@section('header_subtitle', 'Le code IATA figure sur les billets (ex. BZV pour Brazzaville)')

@section('content')
@php
    $champ = 'w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#124231] focus:outline-none';
    $etiquette = 'block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5';
@endphp
<form method="POST" action="{{ $ville->exists ? route('canadian.villes.update', $ville->id) : route('canadian.villes.store') }}" class="space-y-6">
    @csrf
    @if($ville->exists) @method('PUT') @endif
    @include('admin.canadian.partials.erreurs')
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 max-w-xl space-y-4">
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
                <label class="{{ $etiquette }}">Ville *</label>
                <input name="nom" required maxlength="100" value="{{ old('nom', $ville->nom) }}" placeholder="Ex : Brazzaville" class="{{ $champ }}">
            </div>
            <div>
                <label class="{{ $etiquette }}">Code IATA *</label>
                <input name="code_iata" required maxlength="3" value="{{ old('code_iata', $ville->code_iata) }}" placeholder="BZV" class="{{ $champ }} font-mono font-bold uppercase">
            </div>
            <div class="col-span-3">
                <label class="{{ $etiquette }}">Pays *</label>
                <input name="pays" required maxlength="100" value="{{ old('pays', $ville->pays) }}" class="{{ $champ }}">
            </div>
        </div>
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="actif" value="1" @checked(old('actif', $ville->actif)) class="w-5 h-5 rounded accent-emerald-600">
            <span class="text-sm font-bold text-slate-700">Aéroport actif (proposé pour les nouveaux vols)</span>
        </label>
        <div class="flex gap-3 pt-2">
            <a href="{{ route('canadian.villes.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm">Annuler</a>
            <button class="px-5 py-2.5 rounded-xl bg-[#124231] hover:bg-[#0c2f23] text-white font-bold text-sm">Enregistrer</button>
        </div>
    </div>
</form>
@endsection
