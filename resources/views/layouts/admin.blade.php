@php
    $isCanadian   = request()->is('canadian*');
    $isSuperAdmin = request()->is('superadmin*');

    $user = $isSuperAdmin
        ? Auth::guard('superadmin')->user()
        : ($isCanadian ? Auth::guard('canadian')->user() : Auth::user());

    $agenceNom = $isSuperAdmin
        ? 'Super administration'
        : ($user?->agence?->nom_agence ?? ($isCanadian ? 'Canadian Airways' : 'Mobembo Line'));
    $userEmail = $user?->email ?? ($user?->telephone ? $user?->telephone : 'admin@mobemboline.com');
    $userNomComplet = trim(($user?->prenom ?? $user?->name ?? 'Admin') . ' ' . ($user?->nom ?? ''));
    if (empty($userNomComplet)) $userNomComplet = 'Admin';

    $userInitiales = strtoupper(substr($user?->prenom ?? $user?->name ?? 'A', 0, 1) . substr($user?->nom ?? '', 0, 1));
    if (empty(trim($userInitiales))) $userInitiales = 'MB';

    // Libellé du rôle affiché dans la barre latérale
    $roleLibelle = $isSuperAdmin ? 'Super administrateur'
        : ($isCanadian ? 'Administrateur' : (($user?->role ?? null) === 'Agent' ? 'Agent' : 'Administrateur'));
@endphp
<!DOCTYPE html>
<html lang="fr" class="h-full bg-[#F4F5F7] dark:bg-[#0B131E]" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@hasSection('title')@yield('title') — @endif{{ $isCanadian ? 'Canadian Airways' : $agenceNom }} — Portail Administrateur</title>
    
    {{-- Anti-FOUC Script pour le Thème Sombre / Clair Immédiat --}}
    <script>
        if (localStorage.getItem('mobembo_theme') === 'dark' || (!('mobembo_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- Tailwind CSS avec stratégie de Dark Mode par classe --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            dark: '#124231',      // Vert forêt Donezo
                            darker: '#0a271d',
                            primary: '#17523d',
                            accent: '#10B981',
                            mint: '#34D399',
                            light: '#ECFDF5',
                            bg: '#F4F5F7',
                        }
                    },
                    borderRadius: {
                        '3xl': '24px',
                        '4xl': '32px',
                    }
                }
            }
        }
    </script>

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Chart.js & Alpine.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #F4F5F7; 
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .card-hover { transition: transform .25s cubic-bezier(.16,1,.3,1), box-shadow .25s; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -10px rgba(15,23,42,.08); }
        html.dark {
            color-scheme: dark;
        }
        html.dark body {
            background-color: #0B131E;
            color: #F1F5F9;
        }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
        html.dark ::-webkit-scrollbar-thumb { background: #334155; }
        html.dark ::-webkit-scrollbar-thumb:hover { background: #475569; }

        .donezo-card {
            background-color: #FFFFFF;
            border-radius: 24px;
            border: 1px solid rgba(226, 232, 240, 0.7);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02), 0 1px 2px -1px rgba(0, 0, 0, 0.02);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .donezo-card:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        }

        /* Thème Sombre Harmonisé pour toutes les cartes, tableaux et inputs */
        html.dark .donezo-card {
            background-color: #121D2B;
            border: 1px solid rgba(51, 65, 85, 0.6);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.3);
        }
        html.dark .bg-white {
            background-color: #121D2B !important;
            border-color: rgba(51, 65, 85, 0.6) !important;
            color: #F1F5F9 !important;
        }
        html.dark .bg-slate-50,
        html.dark .bg-slate-50\/70,
        html.dark .bg-slate-50\/60,
        html.dark .bg-slate-50\/50,
        html.dark .bg-slate-50\/20 {
            background-color: #0E1722 !important;
            border-color: rgba(51, 65, 85, 0.5) !important;
        }
        html.dark .bg-slate-100 {
            background-color: #1A2838 !important;
            color: #E2E8F0 !important;
        }
        html.dark .text-slate-900,
        html.dark .text-slate-800 {
            color: #F8FAFC !important;
        }
        html.dark .text-slate-700,
        html.dark .text-slate-600 {
            color: #CBD5E1 !important;
        }
        html.dark .text-slate-500,
        html.dark .text-slate-400 {
            color: #94A3B8 !important;
        }
        html.dark .border-slate-200,
        html.dark .border-slate-200\/80,
        html.dark .border-slate-200\/70,
        html.dark .border-slate-100 {
            border-color: rgba(51, 65, 85, 0.6) !important;
        }
        html.dark .divide-slate-100 > * + *,
        html.dark .divide-slate-200 > * + * {
            border-color: rgba(51, 65, 85, 0.5) !important;
        }
        html.dark input,
        html.dark select,
        html.dark textarea {
            background-color: #0E1722 !important;
            border-color: rgba(51, 65, 85, 0.8) !important;
            color: #F8FAFC !important;
        }
        html.dark input::placeholder {
            color: #64748B !important;
        }
        html.dark tr:hover {
            background-color: rgba(30, 41, 59, 0.5) !important;
        }

        .hatched-bar {
            background: repeating-linear-gradient(
                45deg,
                #E2E8F0,
                #E2E8F0 4px,
                #F8FAFC 4px,
                #F8FAFC 8px
            );
        }
        html.dark .hatched-bar {
            background: repeating-linear-gradient(
                45deg,
                #1E293B,
                #1E293B 4px,
                #0F172A 4px,
                #0F172A 8px
            );
        }

        /* Effet brillance douce sur bouton actif sidebar */
        .sidebar-active-gradient {
            background: linear-gradient(135deg, #124231 0%, #1a5641 100%);
            box-shadow: 0 4px 14px -2px rgba(18, 66, 49, 0.35);
        }

        [x-cloak] { display: none !important; }
    </style>

    @stack('styles')
    @include('partials.favicon')
</head>
<body class="h-full flex overflow-hidden font-sans antialiased text-slate-800 dark:text-slate-100 bg-[#F4F5F7] dark:bg-[#0B131E]"
      x-data="{ 
          mobileMenuOpen: false, 
          sidebarCollapsed: localStorage.getItem('mobembo_sidebar_collapsed') === 'true',
          darkMode: localStorage.getItem('mobembo_theme') === 'dark' || (!('mobembo_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
          logoutModalOpen: false,
          toggleSidebar() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('mobembo_sidebar_collapsed', this.sidebarCollapsed);
          },
          toggleDarkMode() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('mobembo_theme', this.darkMode ? 'dark' : 'light');
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }">

    {{-- ============================================================ --}}
    {{-- OVERLAY MOBILE POUR LA SIDEBAR                               --}}
    {{-- ============================================================ --}}
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm md:hidden"
         @click="mobileMenuOpen = false"
         style="display: none;"></div>

    {{-- ============================================================ --}}
    {{-- SIDEBAR COLLAPSIBLE (RÉDUCTIBLE EN ICÔNES SEULES)            --}}
    {{-- ============================================================ --}}
    <aside :class="{
               'translate-x-0': mobileMenuOpen,
               '-translate-x-full md:translate-x-0': !mobileMenuOpen,
               'w-72': !sidebarCollapsed,
               'w-20': sidebarCollapsed
           }"
           class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-[#101924] border-r border-slate-200/80 dark:border-slate-800 shadow-[4px_0_24px_-4px_rgba(0,0,0,0.03)] transition-all duration-300 ease-in-out">
        
        <!-- En-tête Sidebar : Logo & Bouton Réduire / Agrandir -->
        <div class="h-24 px-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-b from-slate-50/60 to-white dark:from-slate-900/60 dark:to-[#101924]"
             :class="sidebarCollapsed ? 'justify-center flex-col py-3' : 'px-5 justify-between'">
            
            <div class="flex items-center space-x-3.5 overflow-hidden">
                <!-- Badge Logo avec ombre soignée -->
                <div class="relative w-12 h-12 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 p-1.5 shadow-sm flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/Logo.png') }}" alt="Mobembo Line" class="w-full h-full object-contain">
                    <span class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900 ring-2 ring-emerald-400/30 animate-pulse"></span>
                </div>
                
                <div x-show="!sidebarCollapsed" x-transition class="overflow-hidden">
                    <div class="flex items-center gap-1.5">
                        <span class="font-extrabold text-lg text-slate-900 dark:text-white tracking-tight">Mobembo</span>
                    </div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 truncate max-w-[145px] mt-0.5 flex items-center gap-1">
                        <i class="fa-solid fa-building text-xs text-emerald-600 dark:text-emerald-400"></i>
                        <span>{{ $agenceNom }}</span>
                    </p>
                </div>
            </div>

            <!-- Bouton pour Réduire / Déplier la sidebar (Desktop) -->
            <button @click="toggleSidebar()" 
                    class="hidden md:flex w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 hover:text-slate-900 items-center justify-center transition-all shrink-0"
                    :class="sidebarCollapsed ? 'mt-2' : ''"
                    :title="sidebarCollapsed ? 'Agrandir la barre latérale' : 'Réduire la barre latérale'">
                <i class="fa-solid text-xs" :class="sidebarCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
            </button>

            <!-- Bouton fermer sur mobile -->
            <button @click="mobileMenuOpen = false" class="md:hidden text-slate-400 hover:text-slate-800 dark:hover:text-white p-2">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Navigation Scrollable Raffinée -->
        <nav class="flex-1 px-3 py-5 space-y-6 overflow-y-auto overflow-x-hidden">
            
            @if($isSuperAdmin)
                {{-- MENU SUPER ADMINISTRATION (réseau national) --}}
                <div>
                    <div x-show="!sidebarCollapsed" class="px-3 flex items-center justify-between mb-3">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-400">Réseau national</span>
                        <span class="text-[11px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-2.5 py-0.5 rounded-full">Super admin</span>
                    </div>
                    <div x-show="sidebarCollapsed" class="w-8 h-0.5 bg-slate-200 dark:bg-slate-800 mx-auto my-3 rounded-full"></div>
                    <div class="space-y-1.5">
                        <div class="relative group">
                            <a href="{{ route('superadmin.dashboard') }}"
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('superadmin.dashboard') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('superadmin.dashboard') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-gauge-high"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Tableau de bord</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Tableau de bord
                            </div>
                        </div>
                        <div class="relative group">
                            <a href="{{ route('superadmin.agences.index') }}"
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('superadmin.agences.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('superadmin.agences.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-building-shield"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Agences partenaires</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Agences partenaires
                            </div>
                        </div>
                        <div class="relative group">
                            <a href="{{ route('superadmin.villes.index') }}"
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('superadmin.villes.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('superadmin.villes.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-city"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Villes desservies</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Villes desservies
                            </div>
                        </div>
                        <div class="relative group">
                            <a href="{{ route('superadmin.journal') }}"
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('superadmin.journal') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('superadmin.journal') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-list-check"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Journal d'audit</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Journal d'audit
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($isCanadian)
                {{-- MENU AVIATION --}}
                <div>
                    <div x-show="!sidebarCollapsed" class="px-3 flex items-center justify-between mb-2.5">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-400">Aviation Civile</span>
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    </div>
                    <div x-show="sidebarCollapsed" class="w-8 h-0.5 bg-slate-200 dark:bg-slate-800 mx-auto my-3 rounded-full"></div>

                    <div class="space-y-1.5">
                        <!-- Tableau de bord Canadian -->
                        <div class="relative group">
                            <a href="{{ route('canadian.admin') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('canadian.admin') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('canadian.admin') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-gauge-high"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold">Tableau de bord</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Tableau de bord
                            </div>
                        </div>

                        <!-- Vols -->
                        <div class="relative group">
                            <a href="{{ route('canadian.vols.index') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('canadian.vols.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('canadian.vols.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-plane-departure"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold">Gestion des Vols</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Gestion des Vols
                            </div>
                        </div>

                        <!-- Réservations -->
                        <div class="relative group">
                            <a href="{{ route('canadian.reservations.index') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('canadian.reservations.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('canadian.reservations.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-ticket"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold">Réservations</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Réservations
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- MENU PRINCIPAL (TRANSPORT TERRESTRE) --}}
                <div>
                    <div x-show="!sidebarCollapsed" class="px-3 flex items-center justify-between mb-3">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-400">Opérations Flotte</span>
                        <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/80 px-2.5 py-0.5 rounded-full border border-emerald-200/60 dark:border-emerald-850">En Ligne</span>
                    </div>
                    <div x-show="sidebarCollapsed" class="w-8 h-0.5 bg-slate-200 dark:bg-slate-800 mx-auto my-3 rounded-full"></div>

                    <div class="space-y-1.5">
                        
                        <!-- 1. Tableau de bord -->
                        <div class="relative group">
                            <a href="{{ route('admin.dashboard') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('admin.dashboard') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('admin.dashboard') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-gauge-high"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Tableau de bord</span>
                                </div>
                                @if(request()->routeIs('admin.dashboard'))
                                    <span x-show="!sidebarCollapsed" class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse shadow-sm shadow-emerald-400/50"></span>
                                @endif
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Tableau de bord
                            </div>
                        </div>

                        <!-- 2. Planifications Voyages -->
                        <div class="relative group">
                            <a href="{{ route('admin.planifications.index') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('admin.planifications.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('admin.planifications.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Planifications</span>
                                </div>
                                <span x-show="!sidebarCollapsed" class="text-xs px-2.5 py-0.5 rounded-full font-extrabold {{ request()->routeIs('admin.planifications.*') ? 'bg-white/20 text-white' : 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200/50 dark:border-emerald-850' }}">
                                    Départs
                                </span>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Planifications Départs
                            </div>
                        </div>

                        <!-- 2 bis. Voyageurs (App Mobile) -->
                        <div class="relative group">
                            <a href="{{ route('admin.voyageurs.index') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('admin.voyageurs.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('admin.voyageurs.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-person-walking-luggage"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Voyageurs</span>
                                </div>
                                <span x-show="!sidebarCollapsed" class="text-xs px-2.5 py-0.5 rounded-full font-extrabold {{ request()->routeIs('admin.voyageurs.*') ? 'bg-white/20 text-white' : 'bg-sky-50 dark:bg-sky-950/80 text-sky-700 dark:text-sky-300 border border-sky-200/50 dark:border-sky-850' }}">
                                    App Mobile
                                </span>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Voyageurs (App Mobile)
                            </div>
                        </div>

                        @if(auth()->user()?->role === 'Admin') {{-- Gestion : réservée aux administrateurs --}}
                        <!-- 3. Flotte des Bus -->
                        <div class="relative group">
                            <a href="{{ route('admin.bus.index') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('admin.bus.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('admin.bus.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-bus-simple"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Flotte des Bus</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Flotte des Bus
                            </div>
                        </div>

                        <!-- 4. Trajets & Lignes -->
                        <div class="relative group">
                            <a href="{{ route('admin.trajets.index') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('admin.trajets.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('admin.trajets.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-route"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Trajets & Lignes</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Trajets & Lignes
                            </div>
                        </div>

                        <!-- 5. Vagues de départ -->
                        <div class="relative group">
                            <a href="{{ route('admin.vagues.index') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('admin.vagues.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('admin.vagues.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-clock"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Vagues de départ</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Vagues de départ
                            </div>
                        </div>

                        <!-- 6. Gares Routières -->
                        <div class="relative group">
                            <a href="{{ route('admin.gares.index') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('admin.gares.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('admin.gares.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Gares Routières</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Gares Routières
                            </div>
                        </div>

                        <!-- 7. Équipe Guichets -->
                        <div class="relative group">
                            <a href="{{ route('admin.agents.index') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('admin.agents.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('admin.agents.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-users"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Équipe Guichets</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Équipe Guichets
                            </div>
                        </div>

                        <!-- 8. Vitrine de l'agence (page d'accueil dans l'application) -->
                        <div class="relative group">
                            <a href="{{ route('admin.vitrine.edit') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('admin.vitrine.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('admin.vitrine.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-store"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Vitrine de l'agence</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Vitrine de l'agence
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

                {{-- SECTION AUDIT & ANALYTIQUE --}}
                <div>
                    <div x-show="!sidebarCollapsed" class="px-3 flex items-center justify-between mb-3">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-400">Audit & Analyse</span>
                        <i class="fa-solid fa-chart-simple text-xs text-slate-400"></i>
                    </div>
                    <div x-show="sidebarCollapsed" class="w-8 h-0.5 bg-slate-200 dark:bg-slate-800 mx-auto my-3 rounded-full"></div>

                    <div class="space-y-1.5">
                        @if(auth()->user()?->role === 'Admin')
                        <!-- Statistiques -->
                        <div class="relative group">
                            <a href="{{ route('admin.statistiques.index') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('admin.statistiques.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('admin.statistiques.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-chart-column"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Statistiques</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Statistiques Flotte
                            </div>
                        </div>
                        @endif

                        <!-- Historique -->
                        <div class="relative group">
                            <a href="{{ route('admin.historique.index') }}" 
                               class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 group-hover:translate-x-1 {{ request()->routeIs('admin.historique.*') ? 'sidebar-active-gradient text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center p-2.5 !translate-x-0' : 'justify-between px-3.5 py-2.5'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-transform group-hover:scale-105 {{ request()->routeIs('admin.historique.*') ? 'bg-white/15 text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </span>
                                    <span x-show="!sidebarCollapsed" class="truncate font-bold text-sm">Historique Logs</span>
                                </div>
                            </a>
                            <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                                Historique Logs
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- WIDGET PREMIUM : SYNCHRONISATION CLOUD & APP MOBILE --}}
            <div x-show="!sidebarCollapsed" 
                 class="rounded-3xl bg-gradient-to-br from-[#124231] via-[#16503c] to-[#0c2f23] p-4 text-white shadow-lg shadow-emerald-950/20 relative overflow-hidden border border-emerald-800/40">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-[11px] font-black tracking-wide border border-emerald-400/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                            <span>SYNC ACTIVE</span>
                        </span>
                        <i class="fa-solid fa-cloud-arrow-up text-sm text-emerald-300/80"></i>
                    </div>

                    <p class="font-black text-sm text-white">App Mobile & Cloud SQL</p>
                    <p class="text-xs text-emerald-200/80 mt-1 leading-snug">
                        Réservations voyageurs et scanners agents synchronisés en direct.
                    </p>

                    <div class="mt-3 pt-3 border-t border-white/10 flex items-center justify-between text-xs text-emerald-200/70 font-mono">
                        <span>API v1.0.1</span>
                        <span class="text-white font-bold">Connecté</span>
                    </div>
                </div>

                <!-- Halo lumineux de fond -->
                <div class="absolute -right-8 -bottom-8 w-28 h-28 rounded-full bg-emerald-400/20 blur-2xl pointer-events-none"></div>
            </div>

            <!-- Version icône seule du widget sync quand la sidebar est repliée -->
            <div x-show="sidebarCollapsed" class="relative group flex justify-center py-2">
                <div class="w-10 h-10 rounded-2xl bg-[#124231] text-emerald-300 flex items-center justify-center text-sm shadow-md border border-emerald-800/50">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 z-50">
                    Sync Cloud SQL & App Active
                </div>
            </div>

        </nav>

        <!-- PIED DE SIDEBAR : PROFIL ADMIN & BOUTON DÉCONNEXION RAFFINÉ -->
        <div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-[#0c141d]">
            <div x-show="!sidebarCollapsed" 
                 class="p-2.5 rounded-2xl bg-white dark:bg-[#152232] border border-slate-200/70 dark:border-slate-800 shadow-sm flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-400 to-amber-600 text-slate-950 font-black text-sm flex items-center justify-center shrink-0 shadow-sm border border-amber-300">
                        {{ $userInitiales }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-extrabold text-slate-900 dark:text-white truncate leading-tight">{{ $userNomComplet }}</p>
                        <p class="text-xs text-slate-400 truncate font-semibold mt-0.5">{{ $roleLibelle }}</p>
                    </div>
                </div>

                <!-- Bouton de déconnexion ouvrant la modale de confirmation -->
                <button type="button" 
                        @click.prevent="logoutModalOpen = true"
                        class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-rose-950/50 dark:hover:text-rose-400 flex items-center justify-center transition-colors shadow-sm"
                        title="Se déconnecter">
                    <i class="fa-solid fa-power-off text-sm"></i>
                </button>
            </div>

            <!-- Version repliée du profil -->
            <div x-show="sidebarCollapsed" class="flex flex-col items-center gap-2.5 py-1">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-400 to-amber-600 text-slate-950 font-black text-sm flex items-center justify-center shrink-0 shadow-sm border border-amber-300"
                     title="{{ $userNomComplet }}">
                    {{ $userInitiales }}
                </div>
                <button type="button" 
                        @click.prevent="logoutModalOpen = true"
                        class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-rose-950/50 dark:hover:text-rose-400 flex items-center justify-center transition-colors shadow-sm"
                        title="Se déconnecter">
                    <i class="fa-solid fa-power-off text-sm"></i>
                </button>
            </div>
        </div>

    </aside>

    {{-- ============================================================ --}}
    {{-- ZONE DE CONTENU PRINCIPALE                                    --}}
    {{-- ============================================================ --}}
    <div :class="sidebarCollapsed ? 'md:pl-20' : 'md:pl-72'"
         class="flex-1 flex flex-col overflow-hidden transition-all duration-300 ease-in-out">
        
        <!-- HEADER EN HAUT (AVEC TOGGLE MODE SOMBRE/CLAIR) -->
        <header class="flex items-center justify-between h-20 px-6 sm:px-8 bg-transparent">
            
            <div class="flex items-center gap-3.5 flex-1 max-w-lg">
                <!-- Bouton toggle Sidebar Desktop -->
                <button @click="toggleSidebar()" 
                        class="hidden md:flex w-11 h-11 rounded-2xl bg-white dark:bg-[#121D2B] border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 items-center justify-center shadow-sm transition-all"
                        :title="sidebarCollapsed ? 'Agrandir la barre latérale' : 'Réduire la barre latérale'">
                    <i class="fa-solid text-base" :class="sidebarCollapsed ? 'fa-indent' : 'fa-outdent'"></i>
                </button>

                <!-- Bouton toggle Sidebar Mobile -->
                <button @click="mobileMenuOpen = true" 
                        class="md:hidden w-11 h-11 rounded-2xl bg-white dark:bg-[#121D2B] border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>

                <!-- Champ de Recherche Professionnel -->
                <div class="relative w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                    <input type="text" 
                           placeholder="{{ $isSuperAdmin ? 'Rechercher dans la page…' : 'Rechercher un bus, trajet, agent ou billet...' }}" 
                           class="w-full pl-11 pr-12 py-2.5 h-11 bg-white dark:bg-[#121D2B] border border-slate-200/80 dark:border-slate-800 rounded-full text-sm font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#124231] transition-all">
                    <span class="absolute right-3.5 top-2.5 px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-400 border border-slate-200 dark:border-slate-700">
                        ⌘K
                    </span>
                </div>
            </div>

            <!-- Côté Droit : Mode Sombre/Clair, Badges & Actualisation -->
            <div class="flex items-center space-x-2.5 sm:space-x-3.5">
                
                <!-- BOUTON DE BASCULE MODE CLAIR / SOMBRE -->
                <button @click="toggleDarkMode()" 
                        type="button"
                        class="w-11 h-11 rounded-2xl bg-white dark:bg-[#121D2B] border border-slate-200/80 dark:border-slate-800 text-slate-600 dark:text-amber-400 hover:text-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-sm shadow-sm transition-all"
                        :title="darkMode ? 'Passer en mode clair' : 'Passer en mode sombre'">
                    <template x-if="!darkMode">
                        <i class="fa-solid fa-moon text-slate-600 text-base"></i>
                    </template>
                    <template x-if="darkMode">
                        <i class="fa-solid fa-sun text-amber-400 text-lg"></i>
                    </template>
                </button>

                <!-- Badge Agence en service active -->
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white dark:bg-[#121D2B] border border-slate-200/80 dark:border-slate-800 text-sm font-bold text-slate-700 dark:text-slate-200 shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="truncate max-w-[140px] sm:max-w-none">{{ $agenceNom }}</span>
                </span>

                <!-- Bouton Actualiser Réel (recharge les données en direct) -->
                <button onclick="location.reload();" 
                        class="w-11 h-11 rounded-2xl bg-white dark:bg-[#121D2B] border border-slate-200/80 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-sm shadow-sm transition-all"
                        title="Actualiser les données en direct">
                    <i class="fa-solid fa-rotate-right text-base"></i>
                </button>

                <!-- Avatar Administrateur Direct -->
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-400 to-amber-600 text-slate-950 font-black text-sm flex items-center justify-center shadow-sm border border-amber-300"
                     title="{{ $userNomComplet }} ({{ $roleLibelle }})">
                    {{ $userInitiales }}
                </div>
            </div>
        </header>

        <!-- MAIN SCROLLABLE -->
        <main class="flex-1 overflow-y-auto px-6 sm:px-8 pb-8 space-y-6">
            
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                        <span class="text-xs font-bold">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                        <span class="text-xs font-bold">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @hasSection('header_title')
                <div class="mb-7">
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                        <i class="fa-solid @yield('header_icon', 'fa-shield-halved') text-emerald-600"></i>
                        <span>@yield('header_title')</span>
                    </h1>
                    @hasSection('header_subtitle')
                        <p class="text-sm text-slate-500 font-semibold mt-1">@yield('header_subtitle')</p>
                    @endif
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- ============================================================ --}}
    {{-- FORMULAIRE DE DÉCONNEXION GLOBAL                             --}}
    {{-- ============================================================ --}}
    <form id="global-logout-form" action="{{ route('unified.logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    {{-- ============================================================ --}}
    {{-- MODALE DE CONFIRMATION DE DÉCONNEXION                        --}}
    {{-- ============================================================ --}}
    <div x-show="logoutModalOpen" 
         x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto"
         @keydown.escape.window="logoutModalOpen = false">
        
        <!-- Backdrop flou sombre -->
        <div x-show="logoutModalOpen"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-md"
             @click="logoutModalOpen = false"></div>

        <!-- Boîte de dialogue de confirmation -->
        <div x-show="logoutModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative w-full max-w-md bg-white dark:bg-[#121D2B] rounded-3xl border border-slate-200 dark:border-slate-700/80 shadow-2xl p-7 z-10 text-center overflow-hidden">
            
            <!-- Halo d'ambiance -->
            <div class="absolute -top-12 -right-12 w-32 h-32 rounded-full bg-rose-500/10 blur-2xl pointer-events-none"></div>

            <!-- Badge icône centrale -->
            <div class="w-16 h-16 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-2xl mx-auto mb-4 shadow-sm shadow-rose-500/20">
                <i class="fa-solid fa-power-off"></i>
            </div>

            <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Déconnexion
            </h3>
            
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 font-medium leading-relaxed">
                Êtes-vous certain de vouloir vous déconnecter du portail administrateur de <span class="font-extrabold text-slate-900 dark:text-slate-100">{{ $agenceNom }}</span> ?
            </p>

            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                Vos sessions et vos modifications enregistrées restent sécurisées.
            </p>

            <div class="mt-6 flex items-center justify-center gap-3">
                <button type="button" 
                        @click="logoutModalOpen = false"
                        class="flex-1 px-5 py-3.5 rounded-2xl text-sm font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                    Annuler
                </button>
                
                <button type="button"
                        @click="document.getElementById('global-logout-form').submit()"
                        class="flex-1 px-5 py-3.5 rounded-2xl text-sm font-bold text-white bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800 shadow-lg shadow-rose-600/30 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                    <span>Se déconnecter</span>
                </button>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>