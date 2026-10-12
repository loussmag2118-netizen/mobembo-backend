@extends('layouts.admin')

@section('content')
<div class="space-y-8">

    {{-- ============================================================ --}}
    {{-- EN-TÊTE DE LA PAGE STATISTIQUES                              --}}
    {{-- ============================================================ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/30">
                    <i class="fa-solid fa-chart-pie"></i>
                </span>
                <span>Rapports & Statistiques</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[68px]">
                Analyse financière et performance commerciale de votre agence.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-sm font-bold text-slate-700 shadow-sm flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Données en direct</span>
            </span>
            <button onclick="location.reload()" class="w-11 h-11 rounded-2xl bg-white border border-slate-200/80 text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 flex items-center justify-center shadow-sm transition-all" title="Actualiser">
                <i class="fa-solid fa-rotate-right text-base"></i>
            </button>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- CARTES KPI PRINCIPALES (STYLE PREMIUM)                       --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- KPI 1 : Chiffre d'Affaires --}}
        <div class="relative bg-gradient-to-br from-emerald-600 to-teal-700 rounded-3xl p-6 overflow-hidden shadow-xl shadow-emerald-600/20">
            <div class="absolute -right-6 -top-6 w-32 h-32 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
            <div class="absolute right-4 bottom-4 w-20 h-20 rounded-full bg-white/5 pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-5">
                    <span class="text-xs font-black uppercase tracking-widest text-emerald-100/80">Chiffre d'Affaires</span>
                    <div class="w-11 h-11 rounded-2xl bg-white/20 text-white flex items-center justify-center text-xl">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>
                <p class="text-4xl font-black text-white leading-none">
                    {{ number_format($revenus, 0, ',', ' ') }}
                </p>
                <p class="text-sm text-emerald-100 font-bold mt-1">FCFA encaissés</p>
                <div class="mt-5 pt-4 border-t border-white/20 flex items-center gap-1.5 text-xs text-emerald-100/80 font-semibold">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Total recettes confirmées</span>
                </div>
            </div>
        </div>

        {{-- KPI 2 : Billets Vendus --}}
        <div class="relative bg-gradient-to-br from-indigo-600 to-purple-700 rounded-3xl p-6 overflow-hidden shadow-xl shadow-indigo-600/20">
            <div class="absolute -right-6 -top-6 w-32 h-32 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-5">
                    <span class="text-xs font-black uppercase tracking-widest text-indigo-100/80">Billets Émis</span>
                    <div class="w-11 h-11 rounded-2xl bg-white/20 text-white flex items-center justify-center text-xl">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                </div>
                <p class="text-4xl font-black text-white leading-none">
                    {{ number_format($ticketsVendus, 0, ',', ' ') }}
                </p>
                <p class="text-sm text-indigo-100 font-bold mt-1">Billets vendus</p>
                <div class="mt-5 pt-4 border-t border-white/20 flex items-center gap-1.5 text-xs text-indigo-100/80 font-semibold">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Sur {{ $nombrePlanifications }} voyages programmés</span>
                </div>
            </div>
        </div>

        {{-- KPI 3 : Prix Moyen --}}
        <div class="relative bg-gradient-to-br from-blue-600 to-cyan-600 rounded-3xl p-6 overflow-hidden shadow-xl shadow-blue-600/20">
            <div class="absolute -right-6 -top-6 w-32 h-32 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-5">
                    <span class="text-xs font-black uppercase tracking-widest text-blue-100/80">Prix Moyen Billet</span>
                    <div class="w-11 h-11 rounded-2xl bg-white/20 text-white flex items-center justify-center text-xl">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </div>
                <p class="text-4xl font-black text-white leading-none">
                    {{ number_format($panierMoyen, 0, ',', ' ') }}
                </p>
                <p class="text-sm text-blue-100 font-bold mt-1">FCFA / billet</p>
                <div class="mt-5 pt-4 border-t border-white/20 flex items-center gap-1.5 text-xs text-blue-100/80 font-semibold">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Rendement moyen unitaire</span>
                </div>
            </div>
        </div>

        {{-- KPI 4 : Disponibilité Flotte --}}
        <div class="relative bg-gradient-to-br from-amber-500 to-orange-600 rounded-3xl p-6 overflow-hidden shadow-xl shadow-amber-500/20">
            <div class="absolute -right-6 -top-6 w-32 h-32 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-5">
                    <span class="text-xs font-black uppercase tracking-widest text-amber-100/80">Flotte Disponible</span>
                    <div class="w-11 h-11 rounded-2xl bg-white/20 text-white flex items-center justify-center text-xl">
                        <i class="fa-solid fa-bus"></i>
                    </div>
                </div>
                <p class="text-4xl font-black text-white leading-none">
                    {{ $tauxDispoBus }}<span class="text-2xl">%</span>
                </p>
                <p class="text-sm text-amber-100 font-bold mt-1">Taux disponibilité</p>
                <div class="mt-5 pt-4 border-t border-white/20 flex items-center gap-1.5 text-xs text-amber-100/80 font-semibold">
                    <i class="fa-solid fa-bus-simple"></i>
                    <span>{{ $nombreBus }} véhicules enregistrés</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- GRAPHIQUE 1 : ÉVOLUTION MENSUELLE (6 DERNIERS MOIS)          --}}
    {{-- ============================================================ --}}
    <div class="bg-white rounded-3xl p-7 sm:p-9 border border-slate-200/80 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2.5">
                    <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-chart-column"></i>
                    </span>
                    <span>Chiffre d'Affaires Mensuel</span>
                </h2>
                <p class="text-sm text-slate-500 mt-1 ml-[52px]">6 derniers mois — Recettes générées en FCFA</p>
            </div>
            <span class="inline-flex items-center gap-1.5 text-sm font-bold text-indigo-700 bg-indigo-50 px-4 py-2 rounded-2xl border border-indigo-100">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span> Recettes (FCFA)
            </span>
        </div>

        <div class="relative h-80 sm:h-96 w-full">
            <canvas id="monthlyRevenueChart"></canvas>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- DEUX GRAPHIQUES : MODES DE PAIEMENT & EMBARQUEMENT           --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- GRAPHIQUE 2 : RÉPARTITION DES MODES DE PAIEMENT --}}
        <div class="bg-white rounded-3xl p-7 border border-slate-200/80 shadow-sm flex flex-col">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2.5">
                        <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                            <i class="fa-solid fa-credit-card"></i>
                        </span>
                        <span>Canaux de Paiement</span>
                    </h2>
                    <p class="text-sm text-slate-500 mt-1 ml-[52px]">Répartition selon le mode de règlement</p>
                </div>
            </div>

            <div class="relative h-64 w-full flex items-center justify-center mt-4">
                <canvas id="paymentMethodsChart"></canvas>
            </div>

            <div class="mt-6 pt-5 border-t border-slate-100 grid grid-cols-3 gap-3 text-center">
                <div class="p-3 rounded-2xl bg-amber-50 border border-amber-100">
                    <p class="text-xs font-bold text-amber-700 mb-1">MTN MoMo</p>
                    <p class="text-2xl font-black text-amber-900">{{ $modesPaiement['MTN MoMo'] ?? 0 }}</p>
                </div>
                <div class="p-3 rounded-2xl bg-red-50 border border-red-100">
                    <p class="text-xs font-bold text-red-700 mb-1">Airtel Money</p>
                    <p class="text-2xl font-black text-red-900">{{ $modesPaiement['Airtel Money'] ?? 0 }}</p>
                </div>
                <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-100">
                    <p class="text-xs font-bold text-emerald-700 mb-1">Guichet</p>
                    <p class="text-2xl font-black text-emerald-900">{{ $modesPaiement['Espèces Guichet'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        {{-- GRAPHIQUE 3 : STATUTS D'EMBARQUEMENT --}}
        <div class="bg-white rounded-3xl p-7 border border-slate-200/80 shadow-sm flex flex-col">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2.5">
                        <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                            <i class="fa-solid fa-qrcode"></i>
                        </span>
                        <span>Statut Embarquement</span>
                    </h2>
                    <p class="text-sm text-slate-500 mt-1 ml-[52px]">Taux de présence et scans QR code</p>
                </div>
            </div>

            <div class="relative h-64 w-full flex items-center justify-center mt-4">
                <canvas id="boardingStatusChart"></canvas>
            </div>

            <div class="mt-6 pt-5 border-t border-slate-100 grid grid-cols-3 gap-3 text-center">
                <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-100">
                    <p class="text-xs font-bold text-emerald-700 mb-1">Scannés</p>
                    <p class="text-2xl font-black text-emerald-900">{{ $statutsEmbarquement['Présents (Scannés)'] ?? 0 }}</p>
                </div>
                <div class="p-3 rounded-2xl bg-blue-50 border border-blue-100">
                    <p class="text-xs font-bold text-blue-700 mb-1">En attente</p>
                    <p class="text-2xl font-black text-blue-900">{{ $statutsEmbarquement['En attente'] ?? 0 }}</p>
                </div>
                <div class="p-3 rounded-2xl bg-rose-50 border border-rose-100">
                    <p class="text-xs font-bold text-rose-700 mb-1">Absents</p>
                    <p class="text-2xl font-black text-rose-900">{{ $statutsEmbarquement['Absents'] ?? 0 }}</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- GRAPHIQUE 4 : TOP LIGNES & TRAJETS PAR VOLUME                --}}
    {{-- ============================================================ --}}
    @if(count($trajetLabels) > 0)
    <div class="bg-white rounded-3xl p-7 sm:p-9 border border-slate-200/80 shadow-sm">
        <div class="mb-7">
            <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-route"></i>
                </span>
                <span>Billetterie par Ligne de Trajet</span>
            </h2>
            <p class="text-sm text-slate-500 mt-1 ml-[52px]">Volume de billets émis pour chaque trajet</p>
        </div>

        <div class="relative h-80 w-full">
            <canvas id="routesSalesChart"></canvas>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartDefaults = {
            font: { family: '"Plus Jakarta Sans"', size: 13, weight: '600' },
        };

        // 1. Chiffre d'Affaires Mensuel
        const ctxRev = document.getElementById('monthlyRevenueChart');
        if (ctxRev) {
            new Chart(ctxRev, {
                type: 'bar',
                data: {
                    labels: @json($moisLabels),
                    datasets: [{
                        label: 'Recettes (FCFA)',
                        data: @json($moisRevenus),
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) return '#6366f1';
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, '#6366f1');
                            gradient.addColorStop(1, '#a5b4fc');
                            return gradient;
                        },
                        borderRadius: 12,
                        borderSkipped: false,
                        hoverBackgroundColor: '#4f46e5',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 14,
                            cornerRadius: 12,
                            titleFont: { family: '"Plus Jakarta Sans"', size: 13, weight: '700' },
                            bodyFont: { family: '"Plus Jakarta Sans"', size: 12 },
                            callbacks: {
                                label: (context) => ` ${new Intl.NumberFormat('fr-FR').format(context.parsed.y)} FCFA`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: chartDefaults.font,
                                color: '#94a3b8',
                                callback: (v) => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v) + ' F'
                            },
                            grid: { color: '#f1f5f9', drawBorder: false }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: chartDefaults.font, color: '#64748b' }
                        }
                    }
                }
            });
        }

        // 2. Modes de Paiement Donut
        const ctxPay = document.getElementById('paymentMethodsChart');
        if (ctxPay) {
            const mtn = {{ $modesPaiement['MTN MoMo'] ?? 0 }};
            const airtel = {{ $modesPaiement['Airtel Money'] ?? 0 }};
            const guichet = {{ $modesPaiement['Espèces Guichet'] ?? 0 }};
            const totalPay = mtn + airtel + guichet;

            new Chart(ctxPay, {
                type: 'doughnut',
                data: {
                    labels: ['MTN MoMo', 'Airtel Money', 'Guichet (Espèces)'],
                    datasets: [{
                        data: totalPay > 0 ? [mtn, airtel, guichet] : [1, 1, 1],
                        backgroundColor: totalPay > 0 ? ['#f59e0b', '#ef4444', '#10b981'] : ['#e2e8f0', '#cbd5e1', '#94a3b8'],
                        borderWidth: 0,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'right',
                            labels: { font: { family: '"Plus Jakarta Sans"', size: 12, weight: '600' }, padding: 14, usePointStyle: true, pointStyleWidth: 10 }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            cornerRadius: 10,
                            titleFont: { family: '"Plus Jakarta Sans"', size: 12 },
                            bodyFont: { family: '"Plus Jakarta Sans"', size: 12 },
                        }
                    }
                }
            });
        }

        // 3. Statuts d'embarquement
        const ctxBoard = document.getElementById('boardingStatusChart');
        if (ctxBoard) {
            const pres = {{ $statutsEmbarquement['Présents (Scannés)'] ?? 0 }};
            const att = {{ $statutsEmbarquement['En attente'] ?? 0 }};
            const abs = {{ $statutsEmbarquement['Absents'] ?? 0 }};
            const totalBoard = pres + att + abs;

            new Chart(ctxBoard, {
                type: 'doughnut',
                data: {
                    labels: ['Présents (Scannés)', 'En attente', 'Absents'],
                    datasets: [{
                        data: totalBoard > 0 ? [pres, att, abs] : [1, 1, 1],
                        backgroundColor: totalBoard > 0 ? ['#10b981', '#3b82f6', '#f43f5e'] : ['#e2e8f0', '#cbd5e1', '#94a3b8'],
                        borderWidth: 0,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'right',
                            labels: { font: { family: '"Plus Jakarta Sans"', size: 12, weight: '600' }, padding: 14, usePointStyle: true, pointStyleWidth: 10 }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            cornerRadius: 10,
                            titleFont: { family: '"Plus Jakarta Sans"', size: 12 },
                            bodyFont: { family: '"Plus Jakarta Sans"', size: 12 },
                        }
                    }
                }
            });
        }

        // 4. Billetterie par Trajet
        const ctxRoutes = document.getElementById('routesSalesChart');
        if (ctxRoutes) {
            new Chart(ctxRoutes, {
                type: 'bar',
                data: {
                    labels: @json($trajetLabels),
                    datasets: [{
                        label: 'Billets vendus',
                        data: @json($trajetTickets),
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) return '#0d9488';
                            const gradient = ctx.createLinearGradient(chartArea.left, 0, chartArea.right, 0);
                            gradient.addColorStop(0, '#0d9488');
                            gradient.addColorStop(1, '#5eead4');
                            return gradient;
                        },
                        borderRadius: 10,
                        borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            cornerRadius: 10,
                            titleFont: { family: '"Plus Jakarta Sans"', size: 12 },
                            bodyFont: { family: '"Plus Jakarta Sans"', size: 12 },
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { family: '"Plus Jakarta Sans"', size: 12, weight: '600' }, color: '#64748b' },
                            grid: { color: '#f1f5f9', drawBorder: false }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { family: '"Plus Jakarta Sans"', size: 12, weight: '600' }, color: '#334155' }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
