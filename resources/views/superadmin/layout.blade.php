<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Administration') — Mobembo Line</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind CSS & FontAwesome --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-hover {
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -10px rgba(15, 23, 42, 0.08);
        }
    </style>
</head>
<body class="h-full flex overflow-hidden font-sans antialiased text-slate-900 bg-slate-50/70">

    {{-- SIDEBAR SUPERADMIN --}}
    <aside class="hidden md:flex md:w-72 md:flex-col md:fixed md:inset-y-0 bg-slate-950 text-white border-r border-slate-800/80 shadow-2xl">
        <div class="flex items-center space-x-3.5 h-20 px-6 bg-slate-950/90 border-b border-slate-800/70">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-400 text-slate-950 flex items-center justify-center font-black text-lg shadow-lg shadow-amber-500/20">
                SA
            </div>
            <div>
                <span class="font-black text-base tracking-wide uppercase text-white">
                    MOBEMBO <span class="text-amber-400">SUPERADMIN</span>
                </span>
                <p class="text-[11px] text-slate-400 font-medium">Contrôle National</p>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 flex flex-col space-y-2">
            <div class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-500">Administration Globale</div>
            
            @php
                $liensSuperAdmin = [
                    ['route' => 'superadmin.agences.index', 'actif' => 'superadmin.agences.*', 'icone' => 'fa-building-shield', 'label' => 'Agences Partenaires'],
                    ['route' => 'superadmin.villes.index',  'actif' => 'superadmin.villes.*',  'icone' => 'fa-city',           'label' => 'Villes desservies'],
                ];
            @endphp
            @foreach($liensSuperAdmin as $lien)
                <a href="{{ route($lien['route']) }}"
                   class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all {{ request()->routeIs($lien['actif']) ? 'bg-amber-500 text-slate-950 shadow-lg shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                    <i class="fa-solid {{ $lien['icone'] }} w-6 text-lg"></i>
                    <span>{{ $lien['label'] }}</span>
                </a>
            @endforeach

            <!-- Déconnexion -->
            <div class="mt-auto pt-6 border-t border-slate-800/80">
                <form action="{{ route('unified.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-all"
                            onclick="return confirm('Voulez-vous vous déconnecter du portail SuperAdmin ?')">
                        <i class="fa-solid fa-power-off text-lg"></i>
                        <span>Se déconnecter</span>
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    {{-- ZONE PRINCIPALE --}}
    <div class="flex-1 flex flex-col md:pl-72 overflow-hidden">

        <!-- HEADER GLASSPERFECT -->
        <header class="sticky top-0 z-30 flex items-center justify-between h-20 px-6 sm:px-8 bg-white/80 backdrop-blur-md border-b border-slate-200/80">
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid @yield('header_icon', 'fa-shield-halved') text-amber-500"></i>
                    <span>@yield('header_title')</span>
                </h1>
                <p class="text-xs text-slate-500 hidden sm:block">
                    @yield('header_subtitle')
                </p>
            </div>

            <div class="flex items-center space-x-3">
                <span class="bg-red-50 text-red-700 border border-red-200/80 px-3.5 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-wider shadow-sm flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    Super Administrateur
                </span>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6">

            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <span class="font-medium text-sm">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-red-500 text-white flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <span class="font-medium text-sm">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @yield('content')

        </main>
    </div>

</body>
</html>
