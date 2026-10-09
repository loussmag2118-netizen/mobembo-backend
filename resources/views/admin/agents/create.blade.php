@extends('layouts.admin')

@section('content')

<div class="max-w-2xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Ajouter un agent
        </h2>

        <a href="{{ route('admin.agents.index') }}"
           class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg">
            ← Retour à la liste
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 p-4 rounded-lg bg-green-100 border border-green-300 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 p-4 rounded-lg bg-red-100 border border-red-300 text-red-700">
            <p class="font-semibold mb-2">Veuillez corriger les erreurs suivantes :</p>

            <ul class="list-disc ml-6">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.agents.store') }}" method="POST" class="bg-white rounded-xl shadow p-6">

        @csrf

        {{-- GARE DE RATTACHEMENT --}}
        <div class="mb-5">
            <label for="gare_id" class="block font-semibold mb-2">
                Gare / Station de rattachement <span class="text-red-500">*</span>
            </label>

            <select
                id="gare_id"
                name="gare_id"
                required
                class="w-full border border-slate-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">-- Sélectionner une gare --</option>
                @foreach($gares as $gare)
                    <option value="{{ $gare->id }}" {{ old('gare_id') == $gare->id ? 'selected' : '' }}>
                        {{ $gare->nom_gare }} ({{ $gare->ville }} - {{ $gare->quartier ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- NOM --}}
        <div class="mb-5">
            <label for="nom" class="block font-semibold mb-2">
                Nom
            </label>

            <input
                id="nom"
                type="text"
                name="nom"
                value="{{ old('nom') }}"
                autocomplete="off"
                required
                class="w-full border border-slate-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- PRÉNOM --}}
        <div class="mb-5">
            <label for="prenom" class="block font-semibold mb-2">
                Prénom
            </label>

            <input
                id="prenom"
                type="text"
                name="prenom"
                value="{{ old('prenom') }}"
                autocomplete="off"
                required
                class="w-full border border-slate-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- TÉLÉPHONE --}}
        <div class="mb-5">
            <label for="telephone" class="block font-semibold mb-2">
                Téléphone
            </label>

            <input
                id="telephone"
                type="text"
                name="telephone"
                value="{{ old('telephone') }}"
                placeholder="06XXXXXXX"
                pattern="^06[0-9]{7}$"
                title="Format attendu : 06 suivi de 7 chiffres (ex : 061234567)"
                autocomplete="off"
                required
                class="w-full border border-slate-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <p class="mt-1 text-xs text-slate-500">
                Exemple : 061234567 (MTN) ou 051234567 (Airtel)
            </p>
        </div>

        {{-- CODE D'ACCÈS --}}
        <div class="mb-5">
            <label for="code_acces_agent" class="block font-semibold mb-2">
                Code d'accès agent (facultatif)
            </label>

            <input
                id="code_acces_agent"
                type="text"
                name="code_acces_agent"
                value="{{ old('code_acces_agent') }}"
                autocomplete="off"
                class="w-full border border-slate-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-700">
            <strong>Information :</strong><br>
            Un mot de passe temporaire <strong>Mobembo2026</strong> sera automatiquement attribué à l'agent lors de son enregistrement.
        </div>

        <div class="flex gap-3">

            <button
                type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                💾 Enregistrer l'agent
            </button>

            <a href="{{ route('admin.agents.index') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition">
                Annuler
            </a>

        </div>

    </form>

</div>

@endsection
