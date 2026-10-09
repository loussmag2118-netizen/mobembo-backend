@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Tableau de bord statistique</h1>
    <p class="text-slate-500">Vue d'ensemble des performances de l'agence.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-sm font-medium text-slate-500">Billets vendus</h3>
        <p class="text-3xl font-bold text-slate-900 mt-2">{{ $totalTickets ?? 0 }}</p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-sm font-medium text-slate-500">Chiffre d'affaires</h3>
        <p class="text-3xl font-bold text-indigo-600 mt-2">{{ number_format($totalRevenus ?? 0, 0, ',', ' ') }} FCFA</p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-sm font-medium text-slate-500">Bus actifs</h3>
        <p class="text-3xl font-bold text-slate-900 mt-2">{{ $totalBus ?? 0 }}</p>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
    <h3 class="text-lg font-bold text-slate-900 mb-4">Analyse des données</h3>
    <div class="h-64 flex items-center justify-center bg-slate-50 rounded-lg border-2 border-dashed border-slate-200">
        <p class="text-slate-400">Intégrez ici vos graphiques (Chart.js)</p>
    </div>
</div>
@endsection
