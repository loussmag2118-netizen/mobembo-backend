@extends('layouts.admin')

@section('content')

<div class="space-y-8">

    {{-- ============================================================ --}}
    {{-- EN-TÊTE AVEC BIENVENUE                                       --}}
    {{-- ============================================================ --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8">
        <!-- Cercles décoratifs -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        
        <div class="relative z-10 flex items-center justify-between flex-wrap gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fa-solid fa-plane text-white text-xl"></i>
                    </div>
                    <span class="text-xs font-bold text-white/80 uppercase tracking-widest">Canadian Airways</span>
                </div>
                <h1 class="text-3xl font-black text-white mb-1">
                    Bonjour, {{ $user->nom ?? 'Admin' }} 👋
                </h1>
                <p class="text-white/80 text-sm">
                    Bienvenue dans votre tableau de bord administratif
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs text-white/70 uppercase tracking-wider mb-1">Connecté en tant que</p>
                <p class="text-lg font-bold text-white">{{ $user->email ?? 'admin@canadian.com' }}</p>
                <span class="inline-flex items-center px-3 py-1 mt-1 bg-white/20 backdrop-blur-sm rounded-full text-xs font-semibold text-white">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full mr-2 animate-pulse"></span>
                    En ligne
                </span>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- CARTES STATISTIQUES                                          --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

        {{-- Clients --}}
        <div class="group bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-gradient-to-br from-sky-500 to-sky-600 rounded-xl shadow-lg shadow-sky-500/30 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-users text-white text-xl"></i>
                </div>
                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                    +12%
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Clients</p>
            <p class="text-3xl font-black text-slate-900">{{ number_format($totalClients, 0, ',', ' ') }}</p>
            <p class="text-xs text-slate-400 mt-2">Total enregistré</p>
        </div>

        {{-- Utilisateurs --}}
        <div class="group bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg shadow-indigo-500/30 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-user text-white text-xl"></i>
                </div>
                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                    +8%
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Utilisateurs</p>
            <p class="text-3xl font-black text-slate-900">{{ number_format($totalUtilisateurs, 0, ',', ' ') }}</p>
            <p class="text-xs text-slate-400 mt-2">Tous rôles confondus</p>
        </div>

        {{-- Agents --}}
        <div class="group bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-user-tie text-white text-xl"></i>
                </div>
                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded-full">
                    Stable
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Agents</p>
            <p class="text-3xl font-black text-slate-900">{{ number_format($totalAgents, 0, ',', ' ') }}</p>
            <p class="text-xs text-slate-400 mt-2">Actifs et disponibles</p>
        </div>

        {{-- Admins --}}
        <div class="group bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-user-shield text-white text-xl"></i>
                </div>
                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded-full">
                    Équipe
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Admins</p>
            <p class="text-3xl font-black text-slate-900">{{ number_format($totalAdmins, 0, ',', ' ') }}</p>
            <p class="text-xs text-slate-400 mt-2">Accès complet</p>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ACTIONS RAPIDES                                              --}}
    {{-- ============================================================ --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-800">Actions rapides</h2>
                <p class="text-xs text-slate-500 mt-0.5">Accès direct aux modules principaux</p>
            </div>
            <i class="fa-solid fa-bolt text-amber-500"></i>
        </div>
        <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">

            <a href="{{ route('canadian.vols.index') }}"
               class="group p-5 rounded-xl border border-slate-200 hover:border-red-300 hover:bg-red-50/50 transition-all">
                <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-plane-departure text-lg"></i>
                </div>
                <p class="font-bold text-slate-800 text-sm">Vols</p>
                <p class="text-xs text-slate-500 mt-0.5">Gérer les vols</p>
            </a>

            <a href="{{ route('canadian.reservations.index') }}"
               class="group p-5 rounded-xl border border-slate-200 hover:border-sky-300 hover:bg-sky-50/50 transition-all">
                <div class="w-12 h-12 bg-sky-100 text-sky-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-ticket text-lg"></i>
                </div>
                <p class="font-bold text-slate-800 text-sm">Réservations</p>
                <p class="text-xs text-slate-500 mt-0.5">Voir les résas</p>
            </a>

            <a href="{{ route('canadian.paiements.index') }}"
               class="group p-5 rounded-xl border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/50 transition-all">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-credit-card text-lg"></i>
                </div>
                <p class="font-bold text-slate-800 text-sm">Paiements</p>
                <p class="text-xs text-slate-500 mt-0.5">Transactions</p>
            </a>

            <a href="{{ route('canadian.rapports.index') }}"
               class="group p-5 rounded-xl border border-slate-200 hover:border-amber-300 hover:bg-amber-50/50 transition-all">
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-chart-line text-lg"></i>
                </div>
                <p class="font-bold text-slate-800 text-sm">Rapports</p>
                <p class="text-xs text-slate-500 mt-0.5">Statistiques</p>
            </a>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- INFO SYSTEME                                                 --}}
    {{-- ============================================================ --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-start gap-4">
            <div class="p-3 bg-blue-100 text-blue-600 rounded-xl">
                <i class="fa-solid fa-circle-info text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 mb-1">Bienvenue sur Canadian Airways</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Votre espace d'administration est prêt. Vous pouvez dès maintenant gérer vos vols, 
                    consulter les réservations, suivre les paiements et générer des rapports détaillés.
                </p>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- FOOTER                                                       --}}
    {{-- ============================================================ --}}
    <div class="text-center text-xs text-slate-400 pt-4">
        <p>© {{ date('Y') }} Canadian Airways — Tableau de bord administrateur v1.0</p>
    </div>

</div>

@endsection