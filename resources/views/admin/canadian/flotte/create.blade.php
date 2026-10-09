@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('canadian.flotte.index') }}"
                   class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-black text-slate-900">Ajouter un aéronef</h1>
            </div>
            <p class="text-sm text-slate-500 ml-12">
                Enregistrez un nouvel avion dans la flotte Air Canadian
            </p>
        </div>
    </div>

    {{-- Erreurs --}}
    @if ($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
            <div class="flex items-center gap-2 font-bold mb-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Veuillez corriger les erreurs suivantes :</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulaire --}}
    <form action="{{ route('canadian.flotte.store') }}" method="POST"
          class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Modèle --}}
            <div class="md:col-span-2">
                <label for="modele" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Modèle <span class="text-red-500">*</span>
                </label>
                <input type="text" id="modele" name="modele"
                       value="{{ old('modele') }}" required maxlength="100"
                       placeholder="Ex : Boeing 737-800"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
            </div>

            {{-- Capacité --}}
            <div>
                <label for="capacite_places" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Capacité (places) <span class="text-red-500">*</span>
                </label>
                <input type="number" id="capacite_places" name="capacite_places"
                       value="{{ old('capacite_places') }}" required min="1" max="1000"
                       placeholder="Ex : 180"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
            </div>

            {{-- Année --}}
            <div>
                <label for="annee" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Année
                </label>
                <input type="number" id="annee" name="annee"
                       value="{{ old('annee') }}" min="1950" max="{{ date('Y') + 1 }}"
                       placeholder="Ex : {{ date('Y') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
            </div>

            {{-- Statut --}}
            <div class="md:col-span-2">
                <label for="statut" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Statut <span class="text-red-500">*</span>
                </label>
                <select id="statut" name="statut" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
                    <option value="Actif"       {{ old('statut') === 'Actif' ? 'selected' : '' }}>Actif</option>
                    <option value="Maintenance" {{ old('statut') === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="Inactif"     {{ old('statut') === 'Inactif' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>

        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-6 border-t border-slate-100">
            <a href="{{ route('canadian.flotte.index') }}"
               class="flex-1 text-center px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition">
                Annuler
            </a>
            <button type="submit"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-[#C62828] to-[#8E0000] hover:from-[#E53935] hover:to-[#C62828] text-white font-semibold rounded-xl shadow-lg shadow-red-500/20 transition">
                <i class="fa-solid fa-save mr-2"></i>
                Enregistrer l'aéronef
            </button>
        </div>
    </form>
</div>
@endsection