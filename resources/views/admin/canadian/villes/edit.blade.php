@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex items-center gap-3 mb-1">
        <a href="{{ route('canadian.villes.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900">Modifier la ville</h1>
            <p class="text-sm text-slate-500">
                <span class="font-mono font-bold text-[#C62828] bg-red-50 px-2 py-1 rounded">
                    {{ $ville->code_iata }}
                </span>
                — {{ $ville->nom }}
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
    <form action="{{ route('canadian.villes.update', $ville->id) }}" method="POST"
          class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Nom --}}
            <div>
                <label for="nom" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Nom de la ville <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nom" name="nom"
                       value="{{ old('nom', $ville->nom) }}" required maxlength="100"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
            </div>

            {{-- Code IATA --}}
            <div>
                <label for="code_iata" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Code IATA <span class="text-red-500">*</span>
                </label>
                <input type="text" id="code_iata" name="code_iata"
                       value="{{ old('code_iata', $ville->code_iata) }}" required maxlength="3" minlength="3"
                       style="text-transform: uppercase"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-mono font-bold text-center text-lg focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
            </div>

            {{-- Pays --}}
            <div>
                <label for="pays" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Pays <span class="text-red-500">*</span>
                </label>
                <input type="text" id="pays" name="pays"
                       value="{{ old('pays', $ville->pays) }}" required maxlength="100"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
            </div>

            {{-- Drapeau --}}
            <div>
                <label for="drapeau" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Drapeau (emoji)
                </label>
                <input type="text" id="drapeau" name="drapeau"
                       value="{{ old('drapeau', $ville->drapeau) }}" maxlength="10"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-lg focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
            </div>

            {{-- Actif --}}
            <div class="md:col-span-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="actif" value="1"
                           {{ old('actif', $ville->actif) ? 'checked' : '' }}
                           class="w-5 h-5 text-[#C62828] bg-slate-50 border-slate-300 rounded focus:ring-[#C62828] focus:ring-2">
                    <span class="text-sm font-semibold text-slate-700">Ville active</span>
                </label>
            </div>

        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-6 border-t border-slate-100">
            <a href="{{ route('canadian.villes.index') }}"
               class="flex-1 text-center px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition">
                Annuler
            </a>
            <button type="submit"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-[#C62828] to-[#8E0000] hover:from-[#E53935] hover:to-[#C62828] text-white font-semibold rounded-xl shadow-lg shadow-red-500/20 transition">
                <i class="fa-solid fa-save mr-2"></i>
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection