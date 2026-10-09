@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Tableau de bord</h1>
    <p class="text-slate-500">Bienvenue, {{ Auth::user()->name }}.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-sm font-medium text-slate-500 uppercase">Bus totaux</h3>
        <p class="text-3xl font-bold text-slate-900 mt-2">
            {{ \App\Models\Bus::where('agence_id', Auth::user()->agence_id)->count() }}
        </p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-sm font-medium text-slate-500 uppercase">Trajets actifs</h3>
        <p class="text-3xl font-bold text-slate-900 mt-2">
            {{ \App\Models\Trajet::where('agence_id', Auth::user()->agence_id)->count() }}
        </p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-sm font-medium text-slate-500 uppercase">Agents inscrits</h3>
        <p class="text-3xl font-bold text-slate-900 mt-2">
            {{-- Utilisation de la classe correcte (si le modèle s'appelle User mais pointe vers la table utilisateurs) --}}
            {{ \App\Models\User::where('agence_id', Auth::user()->agence_id)->where('role', 'Agent')->count() }}
        </p>
    </div>
</div>

<div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
    <a href="{{ route('admin.bus.index') }}" class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 text-center hover:border-indigo-500 transition">Gérer les bus</a>
    <a href="{{ route('admin.gares.index') }}" class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 text-center hover:border-indigo-500 transition">Gérer les gares</a>
    <a href="{{ route('admin.trajets.index') }}" class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 text-center hover:border-indigo-500 transition">Gérer les trajets</a>
    <a href="{{ route('admin.vagues.index') }}" class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 text-center hover:border-indigo-500 transition">Vagues de départ</a>
    <a href="{{ route('admin.agents.index') }}" class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 text-center hover:border-indigo-500 transition">Gérer les agents</a>
    <a href="{{ route('admin.tickets.index') }}" class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 text-center hover:border-indigo-500 transition">Vente de billets</a>
    <a href="{{ route('admin.statistiques.index') }}" class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 text-center hover:border-indigo-500 transition">Statistiques</a>
</div>
@endsection
