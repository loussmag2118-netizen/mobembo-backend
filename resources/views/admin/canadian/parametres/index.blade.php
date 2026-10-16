@extends('layouts.admin')

@section('title', 'Paramètres')
@section('header_icon', 'fa-sliders')
@section('header_title', 'Paramètres de la compagnie')
@section('header_subtitle', 'Coordonnées du service client et règles communiquées aux voyageurs')

@section('content')
@php
    $champ = 'w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#124231] focus:outline-none';
    $etiquette = 'block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5';
    $champs = \App\Http\Controllers\Admin\Canadian\ParametreController::CHAMPS;
@endphp
<form method="POST" action="{{ route('canadian.parametres.update') }}" class="space-y-6 max-w-3xl">
    @csrf
    @include('admin.canadian.partials.erreurs')
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <h2 class="text-base font-black text-slate-900">Service client</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="{{ $etiquette }}">{{ $champs['telephone'][0] }}</label><input name="telephone" value="{{ old('telephone', $parametres['telephone']) }}" placeholder="Ex : 06 123 45 67" class="{{ $champ }}"></div>
            <div><label class="{{ $etiquette }}">{{ $champs['email_contact'][0] }}</label><input type="email" name="email_contact" value="{{ old('email_contact', $parametres['email_contact']) }}" class="{{ $champ }}"></div>
            <div class="sm:col-span-2"><label class="{{ $etiquette }}">{{ $champs['adresse'][0] }}</label><input name="adresse" value="{{ old('adresse', $parametres['adresse']) }}" class="{{ $champ }}"></div>
        </div>
    </div>
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <h2 class="text-base font-black text-slate-900">Voyage</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="{{ $etiquette }}">{{ $champs['franchise_bagage'][0] }}</label><input name="franchise_bagage" value="{{ old('franchise_bagage', $parametres['franchise_bagage']) }}" class="{{ $champ }}"></div>
            <div><label class="{{ $etiquette }}">{{ $champs['enregistrement_min'][0] }}</label><input type="number" min="15" max="240" name="enregistrement_min" value="{{ old('enregistrement_min', $parametres['enregistrement_min']) }}" class="{{ $champ }}"></div>
            <div class="sm:col-span-2"><label class="{{ $etiquette }}">{{ $champs['conditions_annulation'][0] }}</label><textarea name="conditions_annulation" rows="4" maxlength="1000" class="{{ $champ }}">{{ old('conditions_annulation', $parametres['conditions_annulation']) }}</textarea></div>
        </div>
        <p class="text-xs text-slate-400"><i class="fa-solid fa-circle-info mr-1"></i> Le nom, le logo et les couleurs de la compagnie sont gérés par l'administration Mobembo Line.</p>
    </div>
    <button class="px-6 py-3 rounded-xl bg-[#124231] hover:bg-[#0c2f23] text-white font-bold text-sm">Enregistrer</button>
</form>
@endsection
