@extends('layouts.admin')

@section('title', 'Utilisateurs inscrits')
@section('header_icon', 'fa-users')
@section('header_title', 'Utilisateurs inscrits')
@section('header_subtitle', 'Clients qui ont créé un compte sur les applications Mobembo Line')

@php
    use App\Http\Controllers\SuperAdmin\UtilisateurController;
    $estBus = $espace === 'bus';
@endphp

@section('content')
<div class="space-y-6">

    {{-- 1. ESPACES --}}
    <div class="flex flex-wrap gap-2">
        @foreach(['bus' => ['Transport par bus', 'fa-bus'], 'aerien' => ['Transport aérien', 'fa-plane']] as $cle => [$libelle, $icone])
            <a href="{{ route('superadmin.utilisateurs.index', ['espace' => $cle]) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-bold border transition-all {{ $espace === $cle ? 'bg-[#124231] text-white border-[#124231] shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' }}">
                <i class="fa-solid {{ $icone }}"></i>
                <span>{{ $libelle }}</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full {{ $espace === $cle ? 'bg-white/20' : 'bg-slate-100 text-slate-500' }}">{{ $totaux[$cle] }}</span>
            </a>
        @endforeach
    </div>

    {{-- 2. STATISTIQUES --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        @foreach([
            ['Comptes inscrits', $stats['total'], 'fa-users', 'emerald', 'Depuis le lancement'],
            ['Comptes confirmés', $stats['confirmes'], 'fa-circle-check', 'emerald', 'Numéro vérifié par code'],
            ['Inscrits aujourd\'hui', $stats['aujourdhui'], 'fa-calendar-day', 'blue', now()->translatedFormat('d F Y')],
            ['7 derniers jours', $stats['semaine'], 'fa-chart-line', 'amber', 'Nouvelles inscriptions'],
        ] as [$titre, $valeur, $icone, $teinte, $note])
            <div class="card-hover bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500">{{ $titre }}</span>
                    <div class="w-10 h-10 rounded-2xl bg-{{ $teinte }}-50 text-{{ $teinte }}-600 flex items-center justify-center text-lg">
                        <i class="fa-solid {{ $icone }}"></i>
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-900">{{ $valeur }}</p>
                <p class="text-xs text-slate-400 mt-2">{{ $note }}</p>
            </div>
        @endforeach
    </div>

    {{-- 3. LISTE --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 sm:px-8 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid {{ $estBus ? 'fa-bus' : 'fa-plane' }} text-slate-700"></i>
                    <span>{{ $estBus ? 'Clients du transport par bus' : 'Clients du transport aérien' }} ({{ $utilisateurs->total() }})</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $estBus ? 'Un même compte achète chez toutes les compagnies de bus.' : 'Comptes de l\'application Canadian Airways (hors administrateurs).' }}
                    Un compte « non confirmé » n'a pas encore saisi le code reçu.
                </p>
            </div>

            <form method="GET" class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                <input type="hidden" name="espace" value="{{ $espace }}">
                <div class="relative sm:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Nom, téléphone ou email…"
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
                </div>
                <select name="statut" onchange="this.form.submit()"
                        class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                    <option value="">Tous les comptes</option>
                    <option value="confirme" @selected($statut === 'confirme')>Confirmés</option>
                    <option value="non_confirme" @selected($statut === 'non_confirme')>Non confirmés</option>
                </select>
                <button type="submit" class="bg-[#124231] hover:bg-[#0c2f23] text-white text-xs font-bold py-2 px-4 rounded-xl transition-all">
                    Rechercher
                </button>
                @if($q !== '' || $statut)
                    <a href="{{ route('superadmin.utilisateurs.index', ['espace' => $espace]) }}"
                       class="text-xs font-bold text-slate-500 hover:text-slate-800 py-2 px-3 text-center">Effacer</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-6">Client</th>
                        <th class="py-3.5 px-6">Téléphone</th>
                        <th class="py-3.5 px-6">Compte</th>
                        <th class="py-3.5 px-6">{{ $estBus ? 'Billets payés' : 'Réservations' }}</th>
                        <th class="py-3.5 px-6">Inscription</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($utilisateurs as $u)
                        @php
                            $nom = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: ($u->name ?? 'Sans nom');
                            $reseau = UtilisateurController::reseau($u->telephone);
                            $emailVisible = $u->email && ! str_ends_with($u->email, '@canadian.local');
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-emerald-700 text-white flex items-center justify-center shrink-0 font-black text-sm">
                                        {{ mb_strtoupper(mb_substr($nom, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 truncate">{{ $nom }}</p>
                                        @if($emailVisible)
                                            <p class="text-xs text-slate-400 truncate">{{ $u->email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="font-semibold text-slate-700">{{ $u->telephone ?: '—' }}</span>
                                @if($reseau)
                                    <span class="ml-1.5 text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ $reseau === 'MTN' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-700' }}">{{ $reseau }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($u->compte_confirme)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                        <i class="fa-solid fa-circle-check"></i> Confirmé
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                                        <i class="fa-regular fa-clock"></i> Non confirmé
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-700">{{ $u->achats_count }}</td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <p class="text-slate-700 font-semibold">{{ $u->created_at?->format('d/m/Y') ?? '—' }}</p>
                                <p class="text-xs text-slate-400">{{ $u->created_at?->format('H:i') }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-14 text-center">
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-3">
                                    <i class="fa-solid fa-user-slash"></i>
                                </div>
                                <p class="font-bold text-slate-700">Aucun utilisateur trouvé</p>
                                <p class="text-xs text-slate-400 mt-1">
                                    {{ $q !== '' || $statut ? 'Essayez une autre recherche.' : 'Les inscriptions depuis l\'application apparaîtront ici.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($utilisateurs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">{{ $utilisateurs->links() }}</div>
        @endif
    </div>
</div>
@endsection
