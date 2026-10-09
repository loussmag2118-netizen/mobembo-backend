@php
    $isCanadian = request()->is('canadian*');
    
    $user = $isCanadian 
        ? Auth::guard('canadian')->user() 
        : Auth::user();
    
    $agenceNom = $user?->agence?->nom_agence ?? ($isCanadian ? 'Canadian Airways' : 'Mobembo Line');
    $nomLower = strtolower($agenceNom);

    if ($isCanadian) {
        $themeActiveBg = 'bg-sky-600 text-white font-semibold';
        $themeBadge = 'bg-sky-50 text-sky-700 border border-sky-200';
        $logoLetterBg = 'bg-sky-500 text-white';
    } elseif (str_contains($nomLower, 'océan du nord') || str_contains($nomLower, 'ocean du nord')) {
        $themeActiveBg = 'bg-emerald-600 text-white font-semibold';
        $themeBadge = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
        $logoLetterBg = 'bg-emerald-500 text-slate-950';
    } elseif (str_contains($nomLower, 'nzoko')) {
        $themeActiveBg = 'bg-amber-500 text-slate-950 font-semibold';
        $themeBadge = 'bg-amber-50 text-amber-700 border border-amber-200';
        $logoLetterBg = 'bg-amber-500 text-slate-950';
    } elseif (str_contains($nomLower, 'transbony')) {
        $themeActiveBg = 'bg-blue-600 text-white font-semibold';
        $themeBadge = 'bg-blue-50 text-blue-700 border border-blue-200';
        $logoLetterBg = 'bg-blue-600 text-white';
    } elseif (str_contains($nomLower, 'stelimac')) {
        $themeActiveBg = 'bg-red-600 text-white font-semibold';
        $themeBadge = 'bg-red-50 text-red-700 border border-red-200';
        $logoLetterBg = 'bg-red-600 text-white';
    } else {
        $themeActiveBg = 'bg-mobembo-accent text-slate-950 font-semibold';
        $themeBadge = 'bg-indigo-50 text-indigo-700';
        $logoLetterBg = 'bg-mobembo-accent text-slate-950';
    }
@endphp
<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - {{ $isCanadian ? 'Canadian Airways' : 'Mobembo Line' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="h-full flex overflow-hidden font-sans antialiased text-slate-900">

    <!-- SIDEBAR DE GAUCHE -->
    <aside class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 bg-slate-900 text-white">
        <!-- En-tête Sidebar avec Logo -->
        <div class="flex items-center justify-center h-16 px-4 bg-slate-950 border-b border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="{{ $logoLetterBg }} p-2 rounded-lg font-black tracking-wider text-sm">
                    {{ $isCanadian ? 'CA' : 'M' }}
                </div>
                <span class="font-bold text-lg tracking-wide uppercase text-white">
                    {{ $isCanadian ? 'Canadian' : 'Mobembo' }} <span class="{{ $isCanadian ? 'text-sky-400' : 'text-amber-500' }}">{{ $isCanadian ? 'Airways' : 'Line' }}</span>
                </span>
            </div>
        </div>

        <!-- Liens de navigation dynamiques -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto text-sm">
            @if($isCanadian)
                <!-- MENU CANADIAN AIRWAYS -->
                <a href="{{ route('canadian.admin') }}" class="flex items-center px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('canadian.admin') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-chart-line w-6"></i> Tableau de bord
                </a>
                <a href="{{ route('canadian.vols.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('canadian.vols.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-plane-departure w-6"></i> Gestion des Vols
                </a>
                <a href="{{ route('canadian.reservations.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('canadian.reservations.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-passport w-6"></i> Gestion des Réservations
                </a>
                <a href="{{ route('canadian.flotte.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('canadian.flotte.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-plane w-6"></i> Flotte / Compagnies
                </a>
                <a href="{{ route('canadian.paiements.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('canadian.paiements.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-credit-card w-6"></i> Paiements & Transactions
                </a>
                <a href="{{ route('canadian.promos.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('canadian.promos.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-tags w-6"></i> Codes Promo
                </a>
                <a href="{{ route('canadian.notifications.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('canadian.notifications.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-bell w-6"></i> Notifications / SMS
                </a>
                <a href="{{ route('canadian.rapports.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('canadian.rapports.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-6"></i> Rapports & Stats
                </a>
                <a href="{{ route('canadian.parametres.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('canadian.parametres.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-gears w-6"></i> Paramètres Généraux
                </a>
            @else
                <!-- MENU STANDARD TRANSPORT TERRESTRE -->
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.dashboard') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-6 text-lg"></i> Tableau de bord
                </a>
                <a href="{{ route('admin.bus.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.bus.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-bus w-6 text-lg"></i> Gestion des Bus
                </a>
                <a href="{{ route('admin.gares.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.gares.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-map-pin w-6 text-lg"></i> Gares Routières
                </a>
                <a href="{{ route('admin.trajets.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.trajets.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-route w-6 text-lg"></i> Trajets de Voyage
                </a>

                <a href="{{ route('admin.planifications.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.planifications.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-calendar-check w-6 text-lg"></i> Planifications
                </a>

                <a href="{{ route('admin.planifications.create') }}"
                   class="mt-2 flex items-center justify-center gap-2 mx-1 px-4 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold uppercase tracking-wider transition-all">
                    <i class="fa-solid fa-plus"></i>
                    <span>Planifier un voyage</span>
                </a>

                <a href="{{ route('admin.vagues.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.vagues.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-clock w-6 text-lg"></i> Vagues de départ
                </a>
                <a href="{{ route('admin.agents.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.agents.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-user-tie w-6 text-lg"></i> Agents de Guichet
                </a>
                
                <a href="{{ route('admin.historique.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.historique.*') ? $themeActiveBg : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-clock-rotate-left w-6 text-lg"></i> Historique Global
                </a>
            @endif
        </nav>

        <!-- Bas de la Sidebar : Profil & Déconnexion -->
        <div class="p-4 border-t border-slate-800 bg-slate-950">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400">Connecté en tant que</p>
                    <p class="text-sm font-semibold truncate max-w-[150px]">
                        {{ $user?->nom ?? $user?->name ?? 'Admin' }}
                    </p>
                </div>
                {{-- ⭐ Déconnexion UNIFIÉE (déconnecte automatiquement le bon guard) --}}
                <form action="{{ route('unified.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-red-400 transition-colors" title="Se déconnecter">
                        <i class="fa-solid fa-power-off text-lg"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ZONE DE CONTENU PRINCIPALE -->
    <div class="flex-1 flex flex-col md:pl-64 overflow-hidden">
        <header class="flex items-center justify-between h-16 px-6 bg-white border-b border-slate-200 z-10">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-slate-800">
                    {{ $isCanadian ? 'Portail Aviation - Canadian Airways' : 'Portail Administrateur' }}
                </h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="{{ $themeBadge }} px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider">
                    {{ $isCanadian ? 'Canadian Airways' : ($user?->agence?->nom_agence ?? 'Agence Congolaise') }}
                </span>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto bg-slate-50 p-6 md:p-8">
            @yield('content')
        </main>
    </div>

</body>
</html>