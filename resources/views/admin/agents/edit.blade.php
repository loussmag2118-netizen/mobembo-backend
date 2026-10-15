@extends('layouts.admin')

@section('content')

<div class="max-w-4xl mx-auto space-y-7">

    {{-- EN-TÊTE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.agents.index') }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-emerald-700 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm transition-all">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Retour à la liste des agents</span>
                </a>
                <span class="text-xs font-extrabold text-slate-400">/</span>
                <span class="text-xs font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/60">
                    {{ $agent->nom }} {{ $agent->prenom }}
                </span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-emerald-100 text-emerald-600 border border-emerald-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-user-pen"></i>
                </span>
                <span>Modifier le Profil Agent</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Mettez à jour les informations, l'agence de rattachement ou le statut du compte.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-5 bg-rose-50 border border-rose-200 rounded-3xl text-sm text-rose-800 shadow-sm flex items-start gap-3">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg mt-0.5 shrink-0"></i>
            <div>
                <p class="font-extrabold text-sm mb-1">Veuillez corriger les erreurs ci-dessous :</p>
                <ul class="list-disc ml-5 space-y-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- CARTE FORMULAIRE --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-9">
        <form action="{{ route('admin.agents.update', $agent) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- GARE DE RATTACHEMENT --}}
            <div class="space-y-2">
                <label for="gare_id" class="block text-sm font-extrabold text-slate-900">
                    Agence / Station de rattachement <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-building text-base"></i>
                    </div>
                    <select id="gare_id" name="gare_id" required
                            class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                        <option value="">-- Sélectionner une agence --</option>
                        @foreach($gares->groupBy('ville') as $nomVille => $garesVille)
                            <optgroup label="{{ $nomVille }}">
                                @foreach($garesVille as $gare)
                                    <option value="{{ $gare->id }}" {{ old('gare_id', $agent->gare_id) == $gare->id ? 'selected' : '' }}>
                                        {{ $gare->nom_gare }}@if($gare->quartier) ({{ $gare->quartier }})@endif
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- NOM ET PRÉNOM (2 COLONNES) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label for="nom" class="block text-sm font-extrabold text-slate-900">
                        Nom de famille <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-user text-base"></i>
                        </div>
                        <input id="nom" type="text" name="nom" value="{{ old('nom', $agent->nom) }}" required autocomplete="off"
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all uppercase">
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="prenom" class="block text-sm font-extrabold text-slate-900">
                        Prénom <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-user text-base"></i>
                        </div>
                        <input id="prenom" type="text" name="prenom" value="{{ old('prenom', $agent->prenom) }}" required autocomplete="off"
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all capitalize">
                    </div>
                </div>
            </div>

            {{-- TÉLÉPHONE ET CODE D'ACCÈS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label for="telephone" class="block text-sm font-extrabold text-slate-900">
                        Téléphone <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-phone text-base"></i>
                        </div>
                        <input id="telephone" type="text" name="telephone" value="{{ old('telephone', $agent->telephone) }}"
                               placeholder="061234567" pattern="^06[0-9]{7}$"
                               title="Format attendu : 06 suivi de 7 chiffres"
                               required autocomplete="off"
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all font-mono">
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="code_acces_agent" class="block text-sm font-extrabold text-slate-900">
                        Code d'accès agent
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-key text-base"></i>
                        </div>
                        <input id="code_acces_agent" type="text" name="code_acces_agent" value="{{ old('code_acces_agent', $agent->code_acces_agent) }}"
                               autocomplete="off"
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all font-mono uppercase">
                    </div>
                </div>
            </div>

            {{-- STATUT DU COMPTE --}}
            <div class="space-y-2">
                <label for="statut" class="block text-sm font-extrabold text-slate-900">
                    Statut du compte <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-shield-halved text-base"></i>
                    </div>
                    <select id="statut" name="statut" required
                            class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                        <option value="actif" {{ old('statut', $agent->statut) == 'actif' ? 'selected' : '' }}>
                            🟢 Actif (Accès guichet autorisé)
                        </option>
                        <option value="suspendu" {{ old('statut', $agent->statut) == 'suspendu' ? 'selected' : '' }}>
                            🔴 Suspendu (Accès temporairement bloqué)
                        </option>
                    </select>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.agents.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3.5 rounded-2xl text-sm font-bold transition-all">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white px-7 py-3.5 rounded-2xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-emerald-400 text-base"></i>
                    <span>Mettre à jour l'agent</span>
                </button>
            </div>
        </form>
    </div>

</div>

@endsection
