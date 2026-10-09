@extends('layouts.admin')

@section('content')

<div class="max-w-2xl mx-auto">

    <h2 class="text-2xl font-bold mb-6 text-slate-800">
        Ajouter une vague de départ
    </h2>

    <form action="{{ route('admin.vagues.store') }}"
          method="POST"
          class="bg-white p-6 rounded-xl shadow">

        @csrf

        @if($errors->any())
            <div class="mb-5 p-4 bg-red-100 border border-red-300 rounded-lg text-red-700">
                <strong>Erreur :</strong>
                <ul class="list-disc ml-5 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 🆕 Sélection du trajet --}}
        <div class="mb-5">
            <label class="block font-semibold mb-2">
                Trajet <span class="text-red-500">*</span>
            </label>

            <select name="trajet_id"
                    required
                    class="w-full border rounded-lg p-3 @error('trajet_id') border-red-500 @enderror">

                <option value="">— Choisir un trajet —</option>

                @foreach ($trajets as $trajet)
                    <option value="{{ $trajet->id }}"
                        {{ old('trajet_id') == $trajet->id ? 'selected' : '' }}>
                        {{ $trajet->ville_depart }}
                        ({{ $trajet->gareDepart->nom_gare ?? '?' }})
                        → {{ $trajet->ville_arrivee }}
                        ({{ $trajet->gareArrivee->nom_gare ?? '?' }})
                    </option>
                @endforeach

            </select>

            @error('trajet_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-5">
            <label class="block font-semibold mb-2">
                Nom de la vague
            </label>

            <input
                type="text"
                name="nom_vague"
                value="{{ old('nom_vague') }}"
                placeholder="Ex : Matin, Après-midi, Soir"
                required
                class="w-full border rounded-lg p-3">
        </div>

        <div class="mb-6">
            <label class="block font-semibold mb-2">
                Heure de départ
            </label>

            <input
                type="time"
                name="heure_depart"
                value="{{ old('heure_depart') }}"
                required
                class="w-full border rounded-lg p-3">
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                💾 Enregistrer
            </button>

            <a href="{{ route('admin.vagues.index') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg">
                Annuler
            </a>
        </div>

    </form>

</div>

@endsection