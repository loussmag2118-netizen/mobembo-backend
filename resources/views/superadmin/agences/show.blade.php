@extends('layouts.admin')

@section('title', $agence->nom_agence)
@section('header_icon', 'fa-building-shield')
@section('header_title', $agence->nom_agence)
@section('header_subtitle', ($agence->estAerien() ? 'Compagnie aérienne' : 'Compagnie de bus') . ' : réglages et comptes administrateurs')

@section('content')
<div class="space-y-6">
    <a href="{{ route('superadmin.agences.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-slate-900">
        <i class="fa-solid fa-arrow-left"></i> Tous les partenaires
    </a>

    @include('partials.mot-de-passe-temporaire')

    @if($errors->any())
        <div class="rounded-2xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm font-semibold text-rose-700">{{ $errors->first() }}</div>
    @endif

    {{-- Chiffres clés --}}
    @php
        $chiffresCles = $agence->estAerien()
            ? [['Statut', $agence->statut], ['Type', 'Aérien'], ['Vols', $agence->vols_count], ['Avions', $agence->flottes_count], ['Réservations payées', $agence->reservations_payees_count]]
            : [['Statut', $agence->statut], ['Agents', $agence->agents_count], ['Bus', $agence->bus_count], ['Agences', $agence->gares_count], ['Trajets', $agence->trajets_count]];
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        @foreach($chiffresCles as [$t, $v])
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $t }}</p>
                <p class="text-xl font-black {{ $t === 'Statut' ? ($v === 'Actif' ? 'text-emerald-600' : 'text-rose-600') : 'text-slate-900' }}">{{ $v }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Réglages --}}
        <form action="{{ route('superadmin.agences.update', $agence) }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            @csrf
            @method('PUT')
            <h2 class="text-lg font-black text-slate-900">Réglages</h2>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Nom du partenaire</label>
                <input name="nom_agence" value="{{ old('nom_agence', $agence->nom_agence) }}" required maxlength="100"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Partenaire depuis</label>
                    <input type="date" name="date_partenariat" required value="{{ old('date_partenariat', \Illuminate\Support\Carbon::parse($agence->date_partenariat)->toDateString()) }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold">
                </div>
                @unless($agence->estAerien())
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Âge max. enfant</label>
                    <input type="number" name="age_max_enfant" min="1" max="17" required value="{{ old('age_max_enfant', $agence->age_max_enfant) }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold">
                </div>
                @endunless
            </div>
            @unless($agence->estAerien())
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Assurance bébé (0 à 3 ans, sans siège)</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="assurance_bebe" min="0" max="100000" step="100" required value="{{ old('assurance_bebe', $agence->assurance_bebe) }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold">
                    <span class="text-sm font-bold text-slate-500">FCFA</span>
                </div>
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="choix_siege" value="1" @checked(old('choix_siege', $agence->choix_siege)) class="w-5 h-5 rounded accent-emerald-600">
                <span class="text-sm font-bold text-slate-700">Les clients choisissent leur siège</span>
            </label>
            @endunless
            <div class="flex flex-wrap gap-3 pt-2">
                <button class="px-5 py-2.5 rounded-xl bg-[#124231] text-white text-sm font-bold hover:bg-[#0c2f23]">Enregistrer</button>
            </div>
        </form>

        {{-- Statut de l'agence --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-lg font-black text-slate-900">Accès de l'agence</h2>
            <p class="text-sm text-slate-600">
                @if($agence->statut === 'Actif')
                    L'agence est <b class="text-emerald-700">active</b> : ses comptes peuvent se connecter et ses départs sont proposés aux clients.
                @else
                    L'agence est <b class="text-rose-700">bloquée</b> : ses comptes sont déconnectés et ses départs ne sont plus vendus.
                @endif
            </p>
            <form action="{{ route('superadmin.agences.toggleStatus', $agence) }}" method="POST"
                  onsubmit="return confirm('{{ $agence->statut === 'Actif' ? 'Bloquer cette agence ? Ses comptes seront déconnectés et ses ventes arrêtées.' : 'Réactiver cette agence ?' }}')">
                @csrf
                @method('PATCH')
                <button class="px-5 py-2.5 rounded-xl text-sm font-bold {{ $agence->statut === 'Actif' ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                    {{ $agence->statut === 'Actif' ? "Bloquer l'agence" : "Réactiver l'agence" }}
                </button>
            </form>
        </div>

        {{-- Identité visuelle --}}
        <form action="{{ route('superadmin.agences.identite', $agence) }}" method="POST" enctype="multipart/form-data"
              class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6"
              x-data="{ couleur: '{{ old('couleur', $agence->couleur ?? '#124231') }}', couleur2: '{{ old('couleur_secondaire', $agence->couleur_secondaire ?? '#F9A825') }}', apercu: @js($agence->logoMedia?->url()) }">
            @csrf
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Identité visuelle</h2>
                    <p class="text-xs text-slate-500 font-semibold">Logo et couleurs de l'administration de l'agence et de sa page dans l'application.</p>
                </div>
                <div class="h-2 w-28 rounded-full shrink-0" :style="'background: linear-gradient(90deg, ' + couleur + ', ' + couleur2 + ')'"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-start">
                <label class="flex items-center gap-3 p-3 bg-slate-50 border border-dashed border-slate-300 rounded-xl cursor-pointer hover:bg-white transition">
                    <span class="w-16 h-16 rounded-xl bg-white border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                        <template x-if="apercu"><img :src="apercu" class="w-full h-full object-cover" alt=""></template>
                        <template x-if="!apercu"><i class="fa-solid fa-image text-slate-300 text-xl"></i></template>
                    </span>
                    <span class="text-xs font-semibold text-slate-500">Logo : JPG, PNG ou WebP, 2 Mo max.<br><span class="text-slate-700 font-bold">Changer le logo</span></span>
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/webp" class="hidden"
                           @change="const f = $event.target.files[0]; if (f) apercu = URL.createObjectURL(f)">
                </label>
                <div class="md:col-span-2">
                    @include('partials.choix-couleurs')
                    @error('logo')<p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button class="px-5 py-2.5 rounded-xl bg-[#124231] text-white text-sm font-bold hover:bg-[#0c2f23]">Enregistrer l'identité</button>
            </div>
        </form>
    </div>

    {{-- Administrateurs --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="text-lg font-black text-slate-900">Administrateurs de l'agence</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($admins as $admin)
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1">
                        <p class="font-extrabold text-slate-900">{{ $admin->prenom }} {{ $admin->nom }}</p>
                        <p class="text-xs text-slate-500 font-semibold">{{ $admin->telephone }}{{ $admin->email ? ' · ' . $admin->email : '' }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold w-fit {{ $admin->statut === 'actif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $admin->statut === 'actif' ? 'Actif' : 'Suspendu' }}</span>
                    <form action="{{ route('superadmin.agences.admins.reinitialiser', [$agence, $admin]) }}" method="POST"
                          onsubmit="return confirm('Générer un nouveau mot de passe pour {{ $admin->prenom }} {{ $admin->nom }} ?')">
                        @csrf
                        <button class="px-3 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200"><i class="fa-solid fa-key mr-1"></i> Nouveau mot de passe</button>
                    </form>
                    <form action="{{ route('superadmin.agences.admins.statut', [$agence, $admin]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button class="px-3 py-2 rounded-xl text-xs font-bold {{ $admin->statut === 'actif' ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                            {{ $admin->statut === 'actif' ? 'Suspendre' : 'Réactiver' }}
                        </button>
                    </form>
                </div>
            @empty
                <p class="px-6 py-6 text-sm text-rose-600 font-semibold">Cette agence n'a aucun administrateur : ajoutez-en un ci-dessous.</p>
            @endforelse
        </div>

        <form action="{{ route('superadmin.agences.admins.store', $agence) }}" method="POST" class="px-6 py-5 bg-slate-50/60 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Nom</label>
                <input name="nom" required maxlength="100" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Prénom</label>
                <input name="prenom" required maxlength="100" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Téléphone</label>
                <input name="telephone" required placeholder="06XXXXXXX" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold">
            </div>
            <button class="px-4 py-2.5 rounded-xl bg-[#124231] text-white text-sm font-bold hover:bg-[#0c2f23]"><i class="fa-solid fa-user-plus mr-1"></i> Ajouter un admin</button>
        </form>
    </div>
</div>
@endsection
