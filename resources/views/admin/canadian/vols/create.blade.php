@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <div class="flex items-center gap-3 mb-1">
        <a href="{{ route('canadian.vols.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900">Créer un vol</h1>
            <p class="text-sm text-slate-500">Ajoutez un nouveau vol Canadian Airways</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
            <div class="flex items-center gap-2 font-bold mb-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Veuillez corriger :</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('canadian.vols.store') }}" method="POST"
          class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Numéro de vol --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Numéro de vol <span class="text-red-500">*</span>
                </label>
                <input type="text" name="numero_vol" value="{{ old('numero_vol') }}" required
                       placeholder="Ex : CA-001"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-mono font-bold placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- Compagnie --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Compagnie
                </label>
                <input type="text" name="compagnie" value="{{ old('compagnie', 'Canadian Airways') }}"
                       placeholder="Canadian Airways"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- ⭐ AVION (NOUVEAU) --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Type d'avion
                </label>
                <input type="text" name="avion" value="{{ old('avion', 'Boeing 737') }}"
                       placeholder="Ex : Boeing 737, Airbus A320"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- Aéroport départ --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Aéroport de départ <span class="text-red-500">*</span>
                </label>
                <input type="text" name="origine" value="{{ old('origine') }}" required
                       placeholder="Ex : Aéroport Maya-Maya"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- Aéroport arrivée --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Aéroport d'arrivée <span class="text-red-500">*</span>
                </label>
                <input type="text" name="destination" value="{{ old('destination') }}" required
                       placeholder="Ex : Aéroport de Pointe-Noire"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- Ville départ --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Ville de départ <span class="text-red-500">*</span>
                </label>
                <input type="text" name="ville_depart" value="{{ old('ville_depart') }}" required
                       placeholder="Ex : Brazzaville"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- Ville arrivée --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Ville d'arrivée <span class="text-red-500">*</span>
                </label>
                <input type="text" name="ville_arrivee" value="{{ old('ville_arrivee') }}" required
                       placeholder="Ex : Pointe-Noire"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- IATA départ --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Code IATA départ
                </label>
                <input type="text" name="ville_depart_iata" value="{{ old('ville_depart_iata') }}"
                       maxlength="3" minlength="3" placeholder="Ex : BZV"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 uppercase font-mono placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- IATA arrivée --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Code IATA arrivée
                </label>
                <input type="text" name="ville_arrivee_iata" value="{{ old('ville_arrivee_iata') }}"
                       maxlength="3" minlength="3" placeholder="Ex : PNR"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 uppercase font-mono placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- Date départ --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Date de départ <span class="text-red-500">*</span>
                </label>
                <input type="date" name="date_depart" value="{{ old('date_depart') }}" required
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- Heure départ --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Heure de départ <span class="text-red-500">*</span>
                </label>
                <input type="time" name="heure_depart" value="{{ old('heure_depart') }}" required
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- Date arrivée --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Date d'arrivée <span class="text-red-500">*</span>
                </label>
                <input type="date" name="date_arrivee" value="{{ old('date_arrivee') }}" required
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- Heure arrivée --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Heure d'arrivée <span class="text-red-500">*</span>
                </label>
                <input type="time" name="heure_arrivee" value="{{ old('heure_arrivee') }}" required
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- Prix --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Prix (FCFA) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="prix" value="{{ old('prix') }}" required min="0" step="100"
                       placeholder="Ex : 250000"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- Places disponibles --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Places disponibles <span class="text-red-500">*</span>
                </label>
                <input type="number" name="places_disponibles" value="{{ old('places_disponibles', 100) }}" required min="0"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
            </div>

            {{-- Type de vol --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Type de vol <span class="text-red-500">*</span>
                </label>
                <select name="type_vol" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
                    <option value="direct" {{ old('type_vol') === 'direct' ? 'selected' : '' }}>Direct</option>
                    <option value="escale" {{ old('type_vol') === 'escale' ? 'selected' : '' }}>Avec escale</option>
                </select>
            </div>

            {{-- Statut --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Statut <span class="text-red-500">*</span>
                </label>
                <select name="statut" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#C62828]">
                    <option value="programme" {{ old('statut') === 'programme' ? 'selected' : '' }}>Programmé</option>
                    <option value="en_vol"    {{ old('statut') === 'en_vol'    ? 'selected' : '' }}>En vol</option>
                    <option value="atterri"   {{ old('statut') === 'atterri'   ? 'selected' : '' }}>Atterri</option>
                    <option value="annule"    {{ old('statut') === 'annule'    ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>

        </div>

        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-6 border-t border-slate-100">
            <a href="{{ route('canadian.vols.index') }}"
               class="flex-1 text-center px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition">
                Annuler
            </a>
            <button type="submit"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-[#C62828] to-[#8E0000] hover:from-[#E53935] hover:to-[#C62828] text-white font-semibold rounded-xl shadow-lg transition">
                <i class="fa-solid fa-save mr-2"></i>
                Créer le vol
            </button>
        </div>
    </form>
</div>
@endsection