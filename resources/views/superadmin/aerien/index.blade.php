@extends('layouts.admin')

@section('title', 'Transport aérien')
@section('header_icon', 'fa-plane')
@section('header_title', 'Partenaire aérien')
@section('header_subtitle', 'Canadian Airways : activité et administrateurs de son espace de gestion')

@section('content')
<div class="space-y-6">

    @include('partials.mot-de-passe-temporaire')

    @if($errors->any())
        <div class="p-5 bg-red-50 border border-red-200 text-red-700 rounded-2xl shadow-sm">
            <div class="font-bold mb-2 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                <span>L'opération n'a pas abouti :</span>
            </div>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 1. CARTE DU PARTENAIRE --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#7f1d1d] via-[#b91c1c] to-[#ef4444] text-white p-6 sm:p-8 shadow-sm">
        <i class="fa-solid fa-plane absolute -right-6 -bottom-8 text-[160px] text-white/10 -rotate-12"></i>
        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center text-3xl">
                    <i class="fa-solid fa-plane-departure"></i>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-white/70">Compagnie aérienne partenaire</p>
                    <h2 class="text-2xl font-black">Canadian Airways</h2>
                    <p class="text-sm text-white/80 mt-0.5">Vols, réservations et paiements gérés dans son propre espace.</p>
                </div>
            </div>
            <a href="{{ route('canadian.admin') }}" target="_blank" rel="noopener"
               class="inline-flex items-center justify-center gap-2 bg-white text-[#991b1b] font-bold text-sm py-2.5 px-5 rounded-xl shadow-sm hover:bg-rose-50 transition-all">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Adresse de son espace</span>
            </a>
        </div>
    </div>

    {{-- 2. ACTIVITÉ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        @foreach([
            ['Vols enregistrés', $stats['vols'], 'fa-plane', 'rose'],
            ['Vols à venir', $stats['vols_a_venir'], 'fa-calendar-check', 'blue'],
            ['Réservations', $stats['reservations'], 'fa-ticket', 'amber'],
            ['Clients inscrits', $stats['clients'], 'fa-users', 'emerald'],
        ] as [$titre, $valeur, $icone, $teinte])
            <div class="card-hover bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500">{{ $titre }}</span>
                    <div class="w-10 h-10 rounded-2xl bg-{{ $teinte }}-50 text-{{ $teinte }}-600 flex items-center justify-center text-lg">
                        <i class="fa-solid {{ $icone }}"></i>
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-900">{{ $valeur }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
        {{-- 3. ADMINISTRATEURS --}}
        <div class="xl:col-span-3 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 sm:px-8 border-b border-slate-100">
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-shield text-slate-700"></i>
                    <span>Administrateurs ({{ $admins->count() }})</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Ils se connectent sur la page de connexion habituelle avec leur email ou leur téléphone.</p>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($admins as $admin)
                    <div class="px-6 sm:px-8 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-600 to-red-700 text-white flex items-center justify-center shrink-0 font-black text-sm">
                                {{ mb_strtoupper(mb_substr($admin->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-slate-900 truncate">{{ $admin->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $admin->email }} · {{ $admin->telephone }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <form method="POST" action="{{ route('superadmin.aerien.admins.reinitialiser', $admin) }}"
                                  onsubmit="return confirm('Générer un nouveau mot de passe pour {{ e($admin->name) }} ?')">
                                @csrf
                                <button class="text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 px-3 py-2 rounded-xl transition-all">
                                    <i class="fa-solid fa-key"></i> Mot de passe
                                </button>
                            </form>
                            <form method="POST" action="{{ route('superadmin.aerien.admins.retirer', $admin) }}"
                                  onsubmit="return confirm('Retirer l\'accès de {{ e($admin->name) }} ? Son compte sera supprimé.')">
                                @csrf
                                @method('DELETE')
                                <button class="text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-2 rounded-xl transition-all">
                                    <i class="fa-solid fa-user-minus"></i> Retirer
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-8 py-12 text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl mb-3">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>
                        <p class="font-bold text-slate-700">Aucun administrateur</p>
                        <p class="text-xs text-slate-400 mt-1">Personne ne peut encore gérer les vols. Ajoutez un administrateur avec le formulaire.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- 4. AJOUT D'UN ADMINISTRATEUR --}}
        <div class="xl:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-7">
            <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-emerald-600"></i>
                <span>Nouvel administrateur</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5 mb-5">Un mot de passe temporaire s'affiche une seule fois : transmettez-le à la personne.</p>

            <form method="POST" action="{{ route('superadmin.aerien.admins.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <input type="text" name="prenom" value="{{ old('prenom') }}" required maxlength="100" placeholder="Prénom"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
                    <input type="text" name="nom" value="{{ old('nom') }}" required maxlength="100" placeholder="Nom"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
                </div>
                <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" placeholder="Email professionnel"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
                <input type="tel" name="telephone" value="{{ old('telephone') }}" required pattern="0[56][0-9]{7}" maxlength="9" placeholder="Téléphone (06XXXXXXX)"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
                <button type="submit"
                        class="w-full bg-[#124231] hover:bg-[#0c2f23] text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Créer l'administrateur</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
