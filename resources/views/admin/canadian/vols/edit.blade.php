@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center gap-4">
        <a href="{{ route('canadian.vols.index') }}"
           class="p-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900">Modifier le Vol</h1>
            <p class="text-sm text-slate-500 mt-1">
                Vol <span class="font-mono text-[#C62828]">{{ $vol->numero_vol }}</span>
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 border-l-4 border-[#C62828] rounded-r-xl">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation text-[#C62828] mt-0.5"></i>
                <div>
                    <p class="font-semibold text-red-800 text-sm">Corrigez les erreurs :</p>
                    <ul class="list-disc ml-5 mt-2 text-xs text-red-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('canadian.vols.update', $vol->id) }}" method="POST"
          class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @csrf
        @method('PUT')

        {{-- Section 1 : Informations générales --}}
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-[#C62828]"></i>
                Informations générales
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Numéro de vol <span class="text-[#C62828]">*</span>
                    </label>
                    <input type="text" name="numero_vol" value="{{ old('numero_vol', $vol->numero_vol) }}" required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Compagnie
                    </label>
                    <input type="text" name="compagnie" value="{{ old('compagnie', $vol->compagnie ?? 'Canadian Airways') }}"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>

                {{-- ⭐ AVION (NOUVEAU) --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Type d'avion
                    </label>
                    <input type="text" name="avion" value="{{ old('avion', $vol->avion ?? 'Boeing 737') }}"
                           placeholder="Ex : Boeing 737, Airbus A320"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Type de vol <span class="text-[#C62828]">*</span>
                    </label>
                    <select name="type_vol" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none bg-white">
                        @php $typeVol = old('type_vol', $vol->type_vol ?? 'direct'); @endphp
                        <option value="direct" {{ $typeVol == 'direct' ? 'selected' : '' }}>Direct</option>
                        <option value="escale" {{ $typeVol == 'escale' ? 'selected' : '' }}>Avec escale</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Statut <span class="text-[#C62828]">*</span>
                    </label>
                    <select name="statut" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none bg-white">
                        @php $statut = old('statut', $vol->statut); @endphp
                        <option value="programme" {{ $statut == 'programme' ? 'selected' : '' }}>Programmé</option>
                        <option value="en_vol"    {{ $statut == 'en_vol'    ? 'selected' : '' }}>En vol</option>
                        <option value="atterri"   {{ $statut == 'atterri'   ? 'selected' : '' }}>Atterri</option>
                        <option value="annule"    {{ $statut == 'annule'    ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Section 2 : Itinéraire --}}
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fa-solid fa-route text-[#C62828]"></i>
                Itinéraire
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Aéroport de départ <span class="text-[#C62828]">*</span>
                    </label>
                    <input type="text" name="origine" value="{{ old('origine', $vol->origine) }}" required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Aéroport d'arrivée <span class="text-[#C62828]">*</span>
                    </label>
                    <input type="text" name="destination" value="{{ old('destination', $vol->destination) }}" required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Ville de départ <span class="text-[#C62828]">*</span>
                    </label>
                    <input type="text" name="ville_depart" value="{{ old('ville_depart', $vol->ville_depart) }}" required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Ville d'arrivée <span class="text-[#C62828]">*</span>
                    </label>
                    <input type="text" name="ville_arrivee" value="{{ old('ville_arrivee', $vol->ville_arrivee) }}" required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Code IATA départ</label>
                    <input type="text" name="ville_depart_iata" value="{{ old('ville_depart_iata', $vol->ville_depart_iata) }}"
                           maxlength="3" minlength="3"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm uppercase font-mono focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Code IATA arrivée</label>
                    <input type="text" name="ville_arrivee_iata" value="{{ old('ville_arrivee_iata', $vol->ville_arrivee_iata) }}"
                           maxlength="3" minlength="3"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm uppercase font-mono focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Date de départ <span class="text-[#C62828]">*</span>
                    </label>
                    <input type="date" name="date_depart"
                           value="{{ old('date_depart', $vol->date_depart ? \Carbon\Carbon::parse($vol->date_depart)->format('Y-m-d') : '') }}"
                           required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Heure de départ <span class="text-[#C62828]">*</span>
                    </label>
                    <input type="time" name="heure_depart"
                           value="{{ old('heure_depart', $vol->date_depart ? \Carbon\Carbon::parse($vol->date_depart)->format('H:i') : '') }}"
                           required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Date d'arrivée <span class="text-[#C62828]">*</span>
                    </label>
                    <input type="date" name="date_arrivee"
                           value="{{ old('date_arrivee', $vol->date_arrivee ? \Carbon\Carbon::parse($vol->date_arrivee)->format('Y-m-d') : '') }}"
                           required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Heure d'arrivée <span class="text-[#C62828]">*</span>
                    </label>
                    <input type="time" name="heure_arrivee"
                           value="{{ old('heure_arrivee', $vol->date_arrivee ? \Carbon\Carbon::parse($vol->date_arrivee)->format('H:i') : '') }}"
                           required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>
            </div>
        </div>

        {{-- Section 3 : Tarification & Places --}}
        <div class="p-6">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fa-solid fa-dollar-sign text-[#C62828]"></i>
                Tarification & Places
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Prix unitaire (FCFA) <span class="text-[#C62828]">*</span>
                    </label>
                    <input type="number" name="prix" value="{{ old('prix', $vol->prix) }}" required min="0" step="100"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Places disponibles <span class="text-[#C62828]">*</span>
                    </label>
                    <input type="number" name="places_disponibles" value="{{ old('places_disponibles', $vol->places_disponibles) }}"
                           required min="0"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
            <a href="{{ route('canadian.vols.index') }}"
               class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-lg text-sm font-semibold transition">
                Annuler
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-[#C62828] to-[#8E0000] hover:from-[#E53935] hover:to-[#C62828] text-white rounded-lg text-sm font-semibold shadow-lg transition">
                <i class="fa-solid fa-save mr-2"></i> Mettre à jour
            </button>
        </div>
    </form>
</div>
@endsection