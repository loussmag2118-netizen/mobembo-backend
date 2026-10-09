@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex items-center gap-3 mb-1">
        <a href="{{ route('canadian.extras.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900">Ajouter un extra</h1>
            <p class="text-sm text-slate-500">Créez un nouveau service additionnel</p>
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
    <form action="{{ route('canadian.extras.store') }}" method="POST"
          class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Code --}}
            <div>
                <label for="code" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Code technique <span class="text-red-500">*</span>
                </label>
                <input type="text" id="code" name="code"
                       value="{{ old('code') }}" required maxlength="50"
                       placeholder="Ex : bagage_23kg"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-mono placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
                <p class="text-xs text-slate-400 mt-1">Identifiant unique (minuscules, underscores)</p>
            </div>

            {{-- Nom --}}
            <div>
                <label for="nom" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Nom affiché <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nom" name="nom"
                       value="{{ old('nom') }}" required maxlength="150"
                       placeholder="Ex : Bagage soute 23kg"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
            </div>

            {{-- Prix --}}
            <div>
                <label for="prix" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Prix (FCFA) <span class="text-red-500">*</span>
                </label>
                <input type="number" id="prix" name="prix"
                       value="{{ old('prix') }}" required min="0" step="100"
                       placeholder="Ex : 15000"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
            </div>

            {{-- Catégorie --}}
            <div>
                <label for="categorie" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Catégorie <span class="text-red-500">*</span>
                </label>
                <select id="categorie" name="categorie" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
                    <option value="bagage"    {{ old('categorie') === 'bagage'    ? 'selected' : '' }}>Bagage</option>
                    <option value="siege"     {{ old('categorie') === 'siege'     ? 'selected' : '' }}>Siège</option>
                    <option value="assurance" {{ old('categorie') === 'assurance' ? 'selected' : '' }}>Assurance</option>
                    <option value="repas"     {{ old('categorie') === 'repas'     ? 'selected' : '' }}>Repas</option>
                    <option value="wifi"      {{ old('categorie') === 'wifi'      ? 'selected' : '' }}>Wifi</option>
                    <option value="autre"     {{ old('categorie') === 'autre'     ? 'selected' : '' }}>Autre</option>
                </select>
            </div>

            {{-- Icône --}}
            <div>
                <label for="icone" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Icône FontAwesome
                </label>
                <input type="text" id="icone" name="icone"
                       value="{{ old('icone', 'fa-box') }}" maxlength="50"
                       placeholder="Ex : fa-suitcase"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-mono placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
                <p class="text-xs text-slate-400 mt-1">Ex : fa-suitcase, fa-chair, fa-shield</p>
            </div>

            {{-- Ordre --}}
            <div>
                <label for="ordre" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Ordre d'affichage
                </label>
                <input type="number" id="ordre" name="ordre"
                       value="{{ old('ordre', 0) }}" min="0"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
            </div>

            {{-- Description --}}
            <div class="md:col-span-2">
                <label for="description" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Description
                </label>
                <textarea id="description" name="description" rows="2"
                          placeholder="Description optionnelle..."
                          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">{{ old('description') }}</textarea>
            </div>

            {{-- Actif --}}
            <div class="md:col-span-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="actif" value="1"
                           {{ old('actif', true) ? 'checked' : '' }}
                           class="w-5 h-5 text-[#C62828] bg-slate-50 border-slate-300 rounded focus:ring-[#C62828] focus:ring-2">
                    <span class="text-sm font-semibold text-slate-700">Extra actif (proposé aux clients)</span>
                </label>
            </div>

        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-6 border-t border-slate-100">
            <a href="{{ route('canadian.extras.index') }}"
               class="flex-1 text-center px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition">
                Annuler
            </a>
            <button type="submit"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-[#C62828] to-[#8E0000] hover:from-[#E53935] hover:to-[#C62828] text-white font-semibold rounded-xl shadow-lg shadow-red-500/20 transition">
                <i class="fa-solid fa-save mr-2"></i>
                Créer l'extra
            </button>
        </div>
    </form>
</div>
@endsection