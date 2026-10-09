@extends('layouts.admin')

@section('content')

<div class="max-w-2xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Modifier l'agent
        </h2>
        <a href="{{ route('admin.agents.index') }}"
           class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm">
            ← Retour
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-5 p-4 rounded-lg bg-red-100 border border-red-300 text-red-700">
            <p class="font-semibold mb-2">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc ml-6">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.agents.update', $agent) }}" method="POST"
          class="bg-white p-6 rounded-xl shadow">

        @csrf
        @method('PUT')

        {{-- GARE DE RATTACHEMENT --}}
        <div class="mb-5">
            <label for="gare_id" class="block text-sm font-semibold mb-2 text-slate-700">
                Gare / Station de rattachement <span class="text-red-500">*</span>
            </label>

            <select
                id="gare_id"
                name="gare_id"
                required
                class="w-full border border-slate-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">-- Sélectionner une gare --</option>
                @foreach($gares as $gare)
                    <option value="{{ $gare->id }}" {{ old('gare_id', $agent->gare_id) == $gare->id ? 'selected' : '' }}>
                        {{ $gare->nom_gare }} ({{ $gare->ville }} - {{ $gare->quartier ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- NOM --}}
        <div class="mb-5">
            <label for="nom" class="block text-sm font-semibold mb-2 text-slate-700">
                Nom
            </label>
            <input
                id="nom"
                type="text"
                name="nom"
                value="{{ old('nom', $agent->nom) }}"
                autocomplete="off"
                required
                class="w-full border border-slate-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- PRÉNOM --}}
        <div class="mb-5">
            <label for="prenom" class="block text-sm font-semibold mb-2 text-slate-700">
                Prénom
            </label>
            <input
                id="prenom"
                type="text"
                name="prenom"
                value="{{ old('prenom', $agent->prenom) }}"
                autocomplete="off"
                required
                class="w-full border border-slate-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- TÉLÉPHONE --}}
        <div class="mb-5">
            <label for="telephone" class="block text-sm font-semibold mb-2 text-slate-700">
                Téléphone
            </label>
            <input
                id="telephone"
                type="text"
                name="telephone"
                value="{{ old('telephone', $agent->telephone) }}"
                placeholder="06XXXXXXX"
                pattern="^06[0-9]{7}$"
                title="Format attendu : 06 suivi de 7 chiffres"
                autocomplete="off"
                required
                class="w-full border border-slate-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="mt-1 text-xs text-slate-500">
                Exemple : 061234567 (MTN) ou 051234567 (Airtel)
            </p>
        </div>

        {{-- CODE D'ACCÈS --}}
        <div class="mb-5">
            <label for="code_acces_agent" class="block text-sm font-semibold mb-2 text-slate-700">
                Code d'accès agent (facultatif)
            </label>
            <input
                id="code_acces_agent"
                type="text"
                name="code_acces_agent"
                value="{{ old('code_acces_agent', $agent->code_acces_agent) }}"
                autocomplete="off"
                class="w-full border border-slate-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- STATUT --}}
        <div class="mb-6">
            <label for="statut" class="block text-sm font-semibold mb-2 text-slate-700">
                Statut
            </label>
            <select
                id="statut"
                name="statut"
                required
                class="w-full border border-slate-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">

                <option value="actif"
                    {{ old('statut', $agent->statut) == 'actif' ? 'selected' : '' }}>
                    ✅ Actif
                </option>

                <option value="suspendu"
                    {{ old('statut', $agent->statut) == 'suspendu' ? 'selected' : '' }}>
                    ⛔ Suspendu
                </option>

            </select>
        </div>

        {{-- ACTIONS --}}
        <div class="flex gap-3 pt-4 border-t border-slate-200">
            <button
                type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                💾 Mettre à jour
            </button>
            <a href="{{ route('admin.agents.index') }}"
               class="bg-slate-500 hover:bg-slate-600 text-white px-6 py-3 rounded-lg font-semibold transition">
                Annuler
            </a>
        </div>

    </form>

</div>

@endsection
