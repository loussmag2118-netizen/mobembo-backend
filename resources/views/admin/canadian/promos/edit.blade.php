@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('canadian.promos.index') }}"
                   class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-black text-slate-900">Modifier le code promo</h1>
            </div>
            <p class="text-sm text-slate-500 ml-12">
                <span class="font-mono font-bold text-[#C62828] bg-red-50 px-2 py-1 rounded">
                    {{ $promo->code }}
                </span>
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
    <form action="{{ route('canadian.promos.update', $promo->id) }}" method="POST"
          class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Code --}}
            <div class="md:col-span-2">
                <label for="code" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Code promo <span class="text-red-500">*</span>
                </label>
                <input type="text" id="code" name="code"
                       value="{{ old('code', $promo->code) }}" required maxlength="50"
                       placeholder="Ex : CANADIAN2026"
                       style="text-transform: uppercase"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-mono font-bold placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
                <p class="text-xs text-slate-400 mt-1">Le code doit être unique.</p>
            </div>

            {{-- Type de réduction --}}
            <div>
                <label for="type_reduction" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Type de réduction <span class="text-red-500">*</span>
                </label>
                <select id="type_reduction" name="type_reduction" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
                    <option value="pourcentage" {{ old('type_reduction', $promo->type_reduction) === 'pourcentage' ? 'selected' : '' }}>
                        Pourcentage (%)
                    </option>
                    <option value="fixe" {{ old('type_reduction', $promo->type_reduction) === 'fixe' ? 'selected' : '' }}>
                        Montant fixe (FCFA)
                    </option>
                </select>
            </div>

            {{-- Valeur --}}
            <div>
                <label for="valeur" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Valeur <span class="text-red-500">*</span>
                </label>
                <input type="number" id="valeur" name="valeur"
                       value="{{ old('valeur', $promo->valeur) }}" required min="0" step="0.01"
                       placeholder="Ex : 10"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
                <p class="text-xs text-slate-400 mt-1">Ex : 10 pour -10% ou -10 000 FCFA</p>
            </div>

            {{-- Date début --}}
            <div>
                <label for="date_debut" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Date de début
                </label>
                <input type="date" id="date_debut" name="date_debut"
                       value="{{ old('date_debut', $promo->date_debut ? \Carbon\Carbon::parse($promo->date_debut)->format('Y-m-d') : '') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
            </div>

            {{-- Date fin --}}
            <div>
                <label for="date_fin" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Date de fin
                </label>
                <input type="date" id="date_fin" name="date_fin"
                       value="{{ old('date_fin', $promo->date_fin ? \Carbon\Carbon::parse($promo->date_fin)->format('Y-m-d') : '') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
                <p class="text-xs text-slate-400 mt-1">Laisser vide pour aucune expiration.</p>
            </div>

            {{-- Limite utilisation --}}
            <div>
                <label for="limite_utilisation" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Limite d'utilisation
                </label>
                <input type="number" id="limite_utilisation" name="limite_utilisation"
                       value="{{ old('limite_utilisation', $promo->limite_utilisation) }}" min="1"
                       placeholder="Ex : 100"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828] focus:border-transparent transition">
                <p class="text-xs text-slate-400 mt-1">Laisser vide pour illimité.</p>
            </div>

            {{-- Utilisations (info seulement) --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Utilisations actuelles
                </label>
                <div class="px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl">
                    <span class="font-bold text-slate-900">{{ $promo->utilisations ?? 0 }}</span>
                    <span class="text-slate-500 text-sm">fois utilisé</span>
                </div>
                <p class="text-xs text-slate-400 mt-1">Compteur non modifiable.</p>
            </div>

            {{-- Actif --}}
            <div class="md:col-span-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="actif" value="1"
                           {{ old('actif', $promo->actif) ? 'checked' : '' }}
                           class="w-5 h-5 text-[#C62828] bg-slate-50 border-slate-300 rounded focus:ring-[#C62828] focus:ring-2">
                    <span class="text-sm font-semibold text-slate-700">
                        Code actif
                    </span>
                </label>
            </div>

        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-6 border-t border-slate-100">
            <a href="{{ route('canadian.promos.index') }}"
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