@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- ============================================================ --}}
    {{-- 1. EN-TÊTE DASHBOARD DONEZO STYLE                            --}}
    {{-- ============================================================ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Tableau de bord
            </h1>
            <p class="text-sm sm:text-base text-slate-500 mt-1 font-medium">
                Planifiez vos départs, suivez votre flotte et gérez vos billets avec aisance.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.planifications.create') }}" 
               class="inline-flex items-center gap-2.5 bg-[#124231] hover:bg-[#0c2f23] text-white rounded-full px-6 py-3 font-extrabold text-sm shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Planifier un voyage</span>
            </a>

            <a href="{{ route('admin.bus.create') }}" 
               class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-800 rounded-full px-6 py-3 font-bold text-sm hover:bg-slate-50 shadow-sm transition-all">
                <span>+ Ajouter un bus</span>
            </a>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 2. LES 4 CARTES STATISTIQUES EN HAUT (DONEZO 4 CARDS)        --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        <!-- CARTE 1 (HIGHLIGHT : VERT FORÊT DONEZO) -->
        <div class="bg-[#124231] text-white rounded-3xl p-6 shadow-sm relative overflow-hidden flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-bold text-emerald-100 tracking-wide">Chiffre d'Affaires</span>
                    <a href="{{ route('admin.statistiques.index') }}" 
                       class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-xs font-bold transition-all">
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    </a>
                </div>
                <p class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                    {{ number_format($revenusTotal, 0, ',', ' ') }} <span class="text-sm font-semibold text-emerald-300">FCFA</span>
                </p>
            </div>
            
            <div class="mt-5">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 text-emerald-200 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Recettes validées</span>
                </span>
            </div>
        </div>

        <!-- CARTE 2 (BLANCHE : BILLETS ÉMIS) -->
        <div class="donezo-card p-6 flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-bold text-slate-500 tracking-wide">Billets Vendus</span>
                    <a href="{{ route('admin.statistiques.index') }}" 
                       class="w-9 h-9 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center text-xs font-bold transition-all">
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    </a>
                </div>
                <p class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    {{ number_format($ticketsVendus, 0, ',', ' ') }}
                </p>
            </div>

            <div class="mt-5">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold">
                    <span>+{{ $ticketsAujourdhui }} aujourd'hui</span>
                </span>
            </div>
        </div>

        <!-- CARTE 3 (BLANCHE : FLOTTE DES BUS) -->
        <div class="donezo-card p-6 flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-bold text-slate-500 tracking-wide">Flotte en Service</span>
                    <a href="{{ route('admin.bus.index') }}" 
                       class="w-9 h-9 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center text-xs font-bold transition-all">
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    </a>
                </div>
                <p class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    {{ $totalBus }}
                </p>
            </div>

            <div class="mt-5">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                    <span>{{ $busDisponibles }} bus disponibles</span>
                </span>
            </div>
        </div>

        <!-- CARTE 4 (BLANCHE : AGENTS DE GUICHET) -->
        <div class="donezo-card p-6 flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-bold text-slate-500 tracking-wide">Agents Guichets</span>
                    <a href="{{ route('admin.agents.index') }}" 
                       class="w-9 h-9 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center text-xs font-bold transition-all">
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    </a>
                </div>
                <p class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    {{ $totalAgents }}
                </p>
            </div>

            <div class="mt-5">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                    <span>{{ $agentsActifs }} actifs en poste</span>
                </span>
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- 3. RANGÉE DU MILIEU (3 COLONNES DANS LE STYLE DE L'IMAGE)    --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        <!-- COLONNE 1 : PROJECT ANALYTICS (BARRES PILULES HATCHED & VERTES) -->
        <div class="lg:col-span-5 donezo-card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-extrabold text-slate-900">Activité des Ventes</h2>
                    <span class="text-sm font-bold text-slate-400">Semaine en cours</span>
                </div>
                <p class="text-sm text-slate-400 mb-6 font-medium">Volume quotidien de réservations</p>

                <!-- Les 7 barres pilules verticales (comme sur l'image Donezo) -->
                <div class="flex items-end justify-between h-48 px-2 pt-6">
                    @foreach($pillBars as $bar)
                        <div class="flex flex-col items-center gap-3 h-full justify-end group">
                            
                            <!-- Tooltip flottant si highlight -->
                            @if(!empty($bar['highlight']))
                                <div class="px-2.5 py-1 rounded-full bg-slate-900 text-white text-xs font-extrabold shadow-md animate-bounce">
                                    {{ $bar['percent'] }}%
                                </div>
                            @endif

                            <!-- Barre pilule -->
                            <div class="w-8 sm:w-10 rounded-full overflow-hidden transition-all duration-300 group-hover:scale-105"
                                 style="height: {{ $bar['percent'] }}%;"
                                 class="relative">
                                @if(!empty($bar['isSolid']))
                                    <div class="w-full h-full bg-[#124231] rounded-full"></div>
                                @elseif(!empty($bar['isMint']))
                                    <div class="w-full h-full bg-emerald-400 rounded-full"></div>
                                @else
                                    <div class="w-full h-full hatched-bar rounded-full border border-slate-200"></div>
                                @endif
                            </div>

                            <!-- Lettre du jour -->
                            <span class="text-sm font-extrabold text-slate-400 group-hover:text-slate-800 transition-colors">
                                {{ $bar['day'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs sm:text-sm text-slate-500 mt-4">
                <span>Pic d'affluence</span>
                <span class="font-extrabold text-slate-800">Mercredi (95% remplissage)</span>
            </div>
        </div>

        <!-- COLONNE 2 : REMINDERS (PROCHAIN DÉPART IMMÉDIAT) -->
        <div class="lg:col-span-3 donezo-card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-extrabold text-slate-900">Prochain Départ</h2>
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>

                @if($prochainVoyage)
                    <div class="mt-2">
                        <div class="font-black text-lg text-slate-900 leading-snug">
                            {{ $prochainVoyage->trajet->gareDepart->nom_gare ?? $prochainVoyage->trajet->ville_depart ?? 'Départ' }}
                            →
                            {{ $prochainVoyage->trajet->gareArrivee->nom_gare ?? $prochainVoyage->trajet->ville_arrivee ?? 'Arrivée' }}
                        </div>
                        <p class="text-xs text-slate-500 mt-1 font-semibold">
                            {{ $prochainVoyage->bus->immatriculation ?? 'Bus affecté' }} · {{ $nomAgence }}
                        </p>
                    </div>

                    <div class="mt-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-wider">Horaire</p>
                        <p class="text-sm font-extrabold text-slate-800 mt-0.5">
                            {{ $prochainVoyage->vagueDepart ? substr($prochainVoyage->vagueDepart->heure_depart, 0, 5) : '07:30' }}
                            —
                            {{ $prochainVoyage->date_voyage ? \Carbon\Carbon::parse($prochainVoyage->date_voyage)->format('d/m/Y') : date('d/m/Y') }}
                        </p>
                    </div>
                @else
                    <div class="mt-2">
                        <div class="font-black text-lg text-slate-900">Brazzaville → Pointe-Noire</div>
                        <p class="text-xs text-slate-500 mt-1 font-semibold">Bus 04 · {{ $nomAgence }}</p>
                    </div>

                    <div class="mt-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-wider">Horaire</p>
                        <p class="text-sm font-extrabold text-slate-800 mt-0.5">07:30 — {{ date('d/m/Y') }}</p>
                    </div>
                @endif
            </div>

            <!-- Bouton Pilule Vert Forêt Donezo -->
            <div class="mt-6">
                <a href="{{ route('admin.planifications.index') }}" 
                   class="w-full py-3.5 px-5 rounded-full bg-[#124231] hover:bg-[#0c2f23] text-white font-extrabold text-sm shadow-sm flex items-center justify-center gap-2 transition-all">
                    <i class="fa-solid fa-play text-xs"></i>
                    <span>Contrôler le départ</span>
                </a>
            </div>
        </div>

        <!-- COLONNE 3 : PROJECT (LIGNES & TRAJETS AVEC VRAIES ICÔNES DE TRANSPORT) -->
        <div class="lg:col-span-4 donezo-card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-extrabold text-slate-900">Lignes & Trajets</h2>
                    <a href="{{ route('admin.trajets.create') }}" 
                       class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                        + Nouveau
                    </a>
                </div>

                <!-- Liste des lignes avec vraies icônes de transport professionnelles -->
                <div class="space-y-3.5">
                    
                    @forelse($listeTrajets as $idx => $tr)
                        @php
                            $transportIcons = [
                                ['bg-emerald-50 text-emerald-700', 'fa-solid fa-route'],
                                ['bg-blue-50 text-blue-700', 'fa-solid fa-bus-simple'],
                                ['bg-amber-50 text-amber-700', 'fa-solid fa-map-location-dot'],
                                ['bg-purple-50 text-purple-700', 'fa-solid fa-arrow-right-arrow-left'],
                                ['bg-teal-50 text-teal-700', 'fa-solid fa-compass'],
                            ];
                            [$clr, $iconName] = $transportIcons[$idx % count($transportIcons)];
                        @endphp
                        <div class="flex items-center justify-between gap-3 group">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-10 h-10 rounded-xl {{ $clr }} flex items-center justify-center text-sm shrink-0 font-bold group-hover:scale-105 transition-transform">
                                    <i class="{{ $iconName }}"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <p class="font-extrabold text-sm text-slate-900 truncate">
                                        {{ $tr->gareDepart->nom_gare ?? $tr->ville_depart ?? 'Départ' }} → {{ $tr->gareArrivee->nom_gare ?? $tr->ville_arrivee ?? 'Arrivée' }}
                                    </p>
                                    <p class="text-xs text-slate-400 font-medium">
                                        Bus : {{ $tr->bus->immatriculation ?? 'Standard' }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs font-extrabold text-slate-400 font-mono shrink-0">
                                Ligne #{{ $tr->id }}
                            </span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-sm">
                            Aucun trajet configuré.
                        </div>
                    @endforelse

                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs sm:text-sm mt-4">
                <a href="{{ route('admin.trajets.index') }}" class="font-bold text-slate-700 hover:text-[#124231] hover:underline">
                    Voir les {{ $totalTrajets }} trajets →
                </a>
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- 4. RANGÉE DU BAS (3 COLONNES DANS LE STYLE DE L'IMAGE)        --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        <!-- COLONNE 1 : TEAM COLLABORATION (AGENTS DE GUICHET) -->
        <div class="lg:col-span-5 donezo-card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-extrabold text-slate-900">Équipe des Guichets</h2>
                    <a href="{{ route('admin.agents.create') }}" 
                       class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                        + Ajouter
                    </a>
                </div>

                <!-- Liste des agents avec avatars memoji ronds et badges pilules -->
                <div class="space-y-3.5">
                    @forelse($listeAgents as $idx => $ag)
                        @php
                            $initialeAg = strtoupper(substr($ag->prenom ?? 'A', 0, 1) . substr($ag->nom ?? '', 0, 1));
                            $avatarBgs = ['bg-amber-100 text-amber-800', 'bg-emerald-100 text-emerald-800', 'bg-sky-100 text-sky-800', 'bg-purple-100 text-purple-800'];
                            $avatarBg = $avatarBgs[$idx % count($avatarBgs)];
                            $isActif = strtolower($ag->statut ?? '') === 'actif';
                        @endphp
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-10 h-10 rounded-full {{ $avatarBg }} flex items-center justify-center font-black text-sm shrink-0 shadow-inner">
                                    {{ $initialeAg }}
                                </div>
                                <div class="overflow-hidden">
                                    <p class="font-extrabold text-sm text-slate-900 truncate">
                                        {{ $ag->prenom }} {{ $ag->nom }}
                                    </p>
                                    <p class="text-xs text-slate-400 truncate font-medium">
                                        {{ $ag->gare->nom_gare ?? 'Agence principale' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Badge pilule statut -->
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold shrink-0 {{ $isActif ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-amber-50 text-amber-700 border border-amber-200/60' }}">
                                {{ $isActif ? 'Actif' : 'En pause' }}
                            </span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-sm">
                            Aucun agent de guichet enregistré.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs sm:text-sm mt-4">
                <a href="{{ route('admin.agents.index') }}" class="font-bold text-slate-700 hover:text-[#124231] hover:underline">
                    Gérer l'équipe ({{ $totalAgents }} membres) →
                </a>
            </div>
        </div>

        <!-- COLONNE 2 : PROJECT PROGRESS (JAUGE DE REMPLISSAGE FLOTTE) -->
        <div class="lg:col-span-4 donezo-card p-6 flex flex-col justify-between items-center text-center">
            <div class="w-full">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-extrabold text-slate-900">Remplissage Flotte</h2>
                    <span class="text-sm font-bold text-slate-400">Moyenne</span>
                </div>

                <!-- Jauge Semicirculaire SVG -->
                <div class="relative w-48 h-32 mx-auto mt-4 flex items-center justify-center">
                    <svg class="w-full h-full" viewBox="0 0 100 55">
                        <!-- Arc de fond gris -->
                        <path d="M 10 50 A 40 40 0 0 1 90 50" 
                              fill="none" 
                              stroke="#E2E8F0" 
                              stroke-width="12" 
                              stroke-linecap="round"/>
                        <!-- Arc actif vert foncé Donezo -->
                        <path d="M 10 50 A 40 40 0 0 1 90 50" 
                              fill="none" 
                              stroke="#124231" 
                              stroke-width="12" 
                              stroke-linecap="round"
                              stroke-dasharray="125.6"
                              stroke-dashoffset="{{ 125.6 * (1 - ($tauxRemplissage / 100)) }}"/>
                    </svg>

                    <!-- Texte centré dans l'arc -->
                    <div class="absolute bottom-2 text-center">
                        <span class="text-3xl font-black text-slate-900 tracking-tight leading-none">{{ $tauxRemplissage }}%</span>
                        <p class="text-xs font-bold text-slate-400 mt-1">Places Vendues</p>
                    </div>
                </div>
            </div>

            <!-- Légende sous la jauge -->
            <div class="flex items-center justify-center gap-5 text-xs font-bold text-slate-500 pt-4 border-t border-slate-100 w-full mt-4">
                <span class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#124231]"></span> Occupées
                </span>
                <span class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-400"></span> Libres
                </span>
            </div>
        </div>

        <!-- COLONNE 3 : TIME TRACKER (SURVEILLANCE TEMPS RÉEL VERT FORÊT) -->
        <div class="lg:col-span-3 rounded-3xl bg-[#124231] text-white p-6 relative overflow-hidden flex flex-col justify-between shadow-lg">
            
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-base font-bold text-emerald-200 tracking-wide">Horloge Temps Réel</span>
                    <span class="w-3 h-3 rounded-full bg-emerald-400 animate-ping"></span>
                </div>

                <!-- Grand chronomètre / horloge monospace -->
                <div class="my-4 text-center">
                    <div id="donezo-clock" class="text-4xl sm:text-5xl font-mono font-black tracking-wider text-white">
                        {{ date('H:i:s') }}
                    </div>
                    <p class="text-xs font-semibold text-emerald-300/80 mt-1.5">
                        {{ date('d F Y') }}
                    </p>
                </div>
            </div>

            <!-- Boutons de contrôle ronds -->
            <div class="relative z-10 flex items-center justify-center gap-4 pt-4 border-t border-white/10">
                <button type="button" 
                        class="w-11 h-11 rounded-full bg-white hover:bg-emerald-50 text-slate-950 flex items-center justify-center text-sm shadow-md transition-all active:scale-95"
                        title="Actualiser l'horloge"
                        onclick="location.reload();">
                    <i class="fa-solid fa-rotate-right text-sm"></i>
                </button>
                <a href="{{ route('admin.dashboard') }}" 
                   class="w-11 h-11 rounded-full bg-emerald-500 hover:bg-emerald-600 text-slate-950 flex items-center justify-center text-sm shadow-md transition-all active:scale-95"
                   title="En service">
                    <i class="fa-solid fa-play text-sm"></i>
                </a>
            </div>

            <!-- Ondulation de fond verte -->
            <div class="absolute -right-10 -bottom-10 w-36 h-36 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    // Horloge digitale vivante pour le Time Tracker
    setInterval(() => {
        const clk = document.getElementById('donezo-clock');
        if (clk) {
            const now = new Date();
            clk.textContent = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    }, 1000);
</script>
@endpush
@endsection
