@extends('layouts.admin')

@section('content')

<div class="max-w-3xl mx-auto space-y-7">

    {{-- EN-TÊTE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.vagues.index') }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-emerald-700 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm transition-all">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Retour aux vagues</span>
                </a>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-purple-100 text-purple-600 border border-purple-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-clock"></i>
                </span>
                <span>Ajouter un Créneau / Vague</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Définissez un créneau horaire de départ régulier pour l'une de vos lignes.
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="p-5 bg-rose-50 border border-rose-200 rounded-3xl text-sm text-rose-800 shadow-sm flex items-start gap-3">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg mt-0.5 shrink-0"></i>
            <div>
                <p class="font-extrabold text-sm mb-1">Veuillez corriger les erreurs de saisie :</p>
                <ul class="list-disc ml-5 space-y-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- CARTE DU FORMULAIRE --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-9">
        <form action="{{ route('admin.vagues.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- TRAJET ASSIGNÉ --}}
            <div class="space-y-2">
                <label class="block text-sm font-extrabold text-slate-900">
                    Ligne & Trajet concerné <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-route text-base"></i>
                    </div>
                    <select name="trajet_id" required
                            class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-purple-500 focus:outline-none transition-all">
                        <option value="">— Choisir un trajet —</option>
                        @foreach ($trajets as $trajet)
                            <option value="{{ $trajet->id }}" {{ old('trajet_id') == $trajet->id ? 'selected' : '' }}>
                                {{ $trajet->ville_depart }} ({{ $trajet->gareDepart->nom_gare ?? '?' }})
                                → {{ $trajet->ville_arrivee }} ({{ $trajet->gareArrivee->nom_gare ?? '?' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('trajet_id')
                    <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- NOM DE LA VAGUE --}}
            <div class="space-y-2">
                <label class="block text-sm font-extrabold text-slate-900">
                    Nom de la vague ou rotation <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-tag text-base"></i>
                    </div>
                    <input type="text" name="nom_vague" value="{{ old('nom_vague') }}" required
                           placeholder="Ex : Première Vague Matin, Vague 08h00, Départ Soir"
                           class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-purple-500 focus:outline-none transition-all">
                </div>
            </div>

            {{-- HEURE DE DÉPART --}}
            <div class="space-y-2">
                <label class="block text-sm font-extrabold text-slate-900">
                    Heure précise de départ <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-regular fa-clock text-base"></i>
                    </div>
                    <input type="time" name="heure_depart" value="{{ old('heure_depart') }}" required
                           class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-purple-500 focus:outline-none transition-all font-mono">
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.vagues.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3.5 rounded-2xl text-sm font-bold transition-all">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white px-7 py-3.5 rounded-2xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2">
                    <i class="fa-solid fa-check text-purple-400 text-base"></i>
                    <span>Enregistrer la vague</span>
                </button>
            </div>
        </form>
    </div>

</div>

@endsection