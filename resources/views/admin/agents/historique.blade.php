@extends('layouts.admin')

@section('content')

<div class="space-y-7">

    {{-- EN-TÊTE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.agents.index') }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-emerald-700 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm transition-all">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Retour aux agents</span>
                </a>
                <span class="text-xs font-extrabold text-slate-400">/</span>
                <span class="text-xs font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/60">
                    Journal Opérationnel
                </span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-emerald-100 text-emerald-600 border border-emerald-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
                <span>Historique : {{ $agent->prenom }} {{ $agent->nom }}</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Traçabilité des billets vendus et des contrôles passagers effectués par cet agent.
            </p>
        </div>

        {{-- Badge de la gare de rattachement --}}
        @if($agent->gare)
            <div class="inline-flex items-center gap-3 px-5 py-3 rounded-2xl bg-white border border-slate-200 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Agence de service</p>
                    <p class="text-sm font-extrabold text-slate-900">{{ $agent->gare->nom_gare }} ({{ $agent->gare->ville }})</p>
                </div>
            </div>
        @endif
    </div>

    @php
        $total     = $operations->total();
        $presents  = $operations->where('statut_embarquement', 'Present')->count();
        $absents   = $operations->where('statut_embarquement', 'Absent')->count();
        $enAttente = $operations->where('statut_embarquement', 'En_Attente')->count();
    @endphp

    {{-- STATISTIQUES RAPIDES --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Total Opérations</p>
                <p class="text-3xl font-black text-slate-900 mt-1">{{ $total }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-list-check"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-emerald-600">Embarqués (Présents)</p>
                <p class="text-3xl font-black text-emerald-700 mt-1">{{ $presents }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-rose-500">Absents / Défaillants</p>
                <p class="text-3xl font-black text-rose-600 mt-1">{{ $absents }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-amber-500">En Attente</p>
                <p class="text-3xl font-black text-amber-600 mt-1">{{ $enAttente }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
    </div>

    {{-- TABLEAU DES OPÉRATIONS --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 sm:px-8 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-xl font-black text-slate-900 flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-ticket"></i>
                </span>
                <span>Billets & Contrôles Enregistrés</span>
            </h2>
            <span class="text-xs font-extrabold text-slate-400 bg-slate-100 px-3 py-1.5 rounded-full">
                Page {{ $operations->currentPage() }} / {{ $operations->lastPage() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-black uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-6">Référence</th>
                        <th class="py-4 px-6">Passager</th>
                        <th class="py-4 px-6">Ligne & Destination</th>
                        <th class="py-4 px-6">Rôle Agent</th>
                        <th class="py-4 px-6">Statut</th>
                        <th class="py-4 px-6">Montant</th>
                        <th class="py-4 px-6 text-right">Date & Heure</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($operations as $ticket)
                        @php
                            $role = null;
                            if ($ticket->agent_vendeur_id === $agent->id) {
                                $role = 'Vendeur';
                            }
                            if ($ticket->agent_controleur_id === $agent->id) {
                                $role = $role ? 'Vendeur + Contrôleur' : 'Contrôleur';
                            }

                            $badgeColor = match($ticket->statut_embarquement) {
                                'Present'    => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
                                'Absent'     => 'bg-rose-50 text-rose-700 border-rose-200/60',
                                default      => 'bg-amber-50 text-amber-700 border-amber-200/60',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            {{-- Référence --}}
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-xs">
                                    {{ $ticket->reference_ticket ?? '—' }}
                                </span>
                            </td>

                            {{-- Passager --}}
                            <td class="py-4 px-6">
                                <div class="font-extrabold text-slate-900 text-sm">
                                    {{ trim(($ticket->nom_passager ?? '') . ' ' . ($ticket->prenom_passager ?? '')) ?: 'Client direct' }}
                                </div>
                                <div class="text-xs text-slate-400 font-semibold">
                                    {{ $ticket->telephone_passager ?? '—' }}
                                </div>
                            </td>

                            {{-- Ligne --}}
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800 text-sm">
                                    {{ $ticket->ville_arrivee ?? '—' }}
                                </div>
                                @if($ticket->quartier)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200/50 mt-0.5">
                                        <i class="fa-solid fa-location-dot text-[9px]"></i>
                                        {{ $ticket->quartier }}
                                    </span>
                                @endif
                            </td>

                            {{-- Rôle --}}
                            <td class="py-4 px-6">
                                @if($role === 'Vendeur')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200/60">
                                        <i class="fa-solid fa-coins text-[10px]"></i> Vendeur
                                    </span>
                                @elseif($role === 'Contrôleur')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                                        <i class="fa-solid fa-qrcode text-[10px]"></i> Contrôleur
                                    </span>
                                @elseif($role === 'Vendeur + Contrôleur')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                        <i class="fa-solid fa-star text-[10px]"></i> Les deux
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Statut --}}
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $badgeColor }}">
                                    {{ str_replace('_', ' ', $ticket->statut_embarquement ?? 'En attente') }}
                                </span>
                            </td>

                            {{-- Montant --}}
                            <td class="py-4 px-6 font-mono font-bold text-emerald-700">
                                {{ number_format($ticket->montant_total ?? 0, 0, ',', ' ') }} FCFA
                            </td>

                            {{-- Date --}}
                            <td class="py-4 px-6 text-right text-slate-400 font-mono text-xs font-semibold">
                                {{ $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i class="fa-solid fa-inbox text-3xl text-slate-300"></i>
                                    </div>
                                    <p class="text-base font-bold text-slate-600">Aucune opération enregistrée pour cet agent pour le moment.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($operations->hasPages())
            <div class="p-5 border-t border-slate-100">
                {{ $operations->links() }}
            </div>
        @endif
    </div>

</div>

@endsection
