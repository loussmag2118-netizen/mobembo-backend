@extends('layouts.admin')

@section('content')

{{-- ============================================================ --}}
{{-- EN-TÊTE                                                      --}}
{{-- ============================================================ --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <a href="{{ route('admin.agents.index') }}"
           class="text-sm text-emerald-600 hover:underline mb-2 inline-block font-medium">
            &larr; Retour à la liste des agents
        </a>
        <h1 class="text-2xl font-bold text-slate-900">
            Historique des opérations : {{ $agent->nom }} {{ $agent->prenom }}
        </h1>
        <p class="text-slate-500 text-sm mt-1">
            Consultez l'ensemble des ventes et tickets contrôlés par cet agent.
        </p>
    </div>

    {{-- Badge de la gare de rattachement de l'agent --}}
    @if($agent->gare)
        <div class="self-start sm:self-auto bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded-xl text-sm font-medium shadow-sm">
            🏢 Gare : <span class="font-bold">{{ $agent->gare->nom_gare }}</span>
            <span class="text-xs text-blue-500 block">({{ $agent->gare->ville }})</span>
        </div>
    @endif
</div>

{{-- ============================================================ --}}
{{-- STATISTIQUES RAPIDES                                          --}}
{{-- ============================================================ --}}
@php
    $total     = $operations->total();
    $presents  = $operations->where('statut_embarquement', 'Present')->count();
    $absents   = $operations->where('statut_embarquement', 'Absent')->count();
    $enAttente = $operations->where('statut_embarquement', 'En_Attente')->count();
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm ring-1 ring-slate-200">
        <p class="text-xs uppercase font-bold text-slate-500">Total</p>
        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $total }}</p>
    </div>
    <div class="bg-emerald-50 p-4 rounded-xl shadow-sm ring-1 ring-emerald-200">
        <p class="text-xs uppercase font-bold text-emerald-700">Présents</p>
        <p class="text-2xl font-bold text-emerald-700 mt-1">{{ $presents }}</p>
    </div>
    <div class="bg-red-50 p-4 rounded-xl shadow-sm ring-1 ring-red-200">
        <p class="text-xs uppercase font-bold text-red-700">Absents</p>
        <p class="text-2xl font-bold text-red-700 mt-1">{{ $absents }}</p>
    </div>
    <div class="bg-slate-50 p-4 rounded-xl shadow-sm ring-1 ring-slate-200">
        <p class="text-xs uppercase font-bold text-slate-600">En attente</p>
        <p class="text-2xl font-bold text-slate-700 mt-1">{{ $enAttente }}</p>
    </div>
</div>

{{-- ============================================================ --}}
{{-- TABLEAU                                                      --}}
{{-- ============================================================ --}}
<div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold">
                <tr>
                    <th class="p-4">Référence</th>
                    <th class="p-4">Passager</th>
                    <th class="p-4">Destination</th>
                    <th class="p-4">Quartier</th>
                    <th class="p-4">Trajet</th>
                    <th class="p-4">Rôle</th>
                    <th class="p-4">Statut</th>
                    <th class="p-4">Montant</th>
                    <th class="p-4 text-right">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                @forelse($operations as $ticket)
                    @php
                        // Déterminer le rôle de l'agent sur ce ticket
                        $role = null;
                        if ($ticket->agent_vendeur_id === $agent->id) {
                            $role = 'Vendeur';
                        }
                        if ($ticket->agent_controleur_id === $agent->id) {
                            $role = $role ? 'Vendeur + Contrôleur' : 'Contrôleur';
                        }

                        // Couleurs du badge de statut
                        $badgeColor = match($ticket->statut_embarquement) {
                            'Present'    => 'bg-emerald-100 text-emerald-800',
                            'Absent'     => 'bg-red-100 text-red-800',
                            default      => 'bg-slate-100 text-slate-700',
                        };
                    @endphp

                    <tr class="hover:bg-slate-50/50 transition-colors">

                        {{-- Référence --}}
                        <td class="p-4 font-mono text-xs font-semibold text-slate-900">
                            {{ $ticket->reference_ticket ?? '—' }}
                        </td>

                        {{-- Passager --}}
                        <td class="p-4 text-slate-700">
                            <div class="font-medium">
                                {{ trim(($ticket->nom_passager ?? '') . ' ' . ($ticket->prenom_passager ?? '')) ?: 'N/A' }}
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $ticket->telephone_passager ?? '—' }}
                            </div>
                        </td>

                        {{-- Destination --}}
                        <td class="p-4 text-slate-700">
                            {{ $ticket->ville_arrivee ?? '—' }}
                        </td>

                        {{-- Quartier --}}
                        <td class="p-4">
                            @if($ticket->quartier)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fa-solid fa-location-dot mr-1"></i>
                                    {{ $ticket->quartier }}
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Trajet --}}
                        <td class="p-4 text-xs text-slate-600">
                            @if($ticket->planificationVoyage && $ticket->planificationVoyage->trajet)
                                {{ $ticket->planificationVoyage->trajet->gareDepart->nom_gare ?? '?' }}
                                →
                                {{ $ticket->planificationVoyage->trajet->gareArrivee->nom_gare ?? '?' }}
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        {{-- Rôle --}}
                        <td class="p-4 text-xs">
                            @if($role === 'Vendeur')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    💰 Vendeur
                                </span>
                            @elseif($role === 'Contrôleur')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    🎫 Contrôleur
                                </span>
                            @elseif($role === 'Vendeur + Contrôleur')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    ⭐ Les deux
                                </span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        {{-- Statut --}}
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                                {{ str_replace('_', ' ', $ticket->statut_embarquement ?? 'En attente') }}
                            </span>
                        </td>

                        {{-- Montant --}}
                        <td class="p-4 text-right font-medium text-slate-900 text-xs">
                            {{ number_format($ticket->montant_total ?? 0, 0, ',', ' ') }} FCFA
                            <div class="text-slate-400 text-xs font-normal">
                                {{ $ticket->mode_paiement ?? '—' }}
                            </div>
                        </td>

                        {{-- Date --}}
                        <td class="p-4 text-right text-slate-500 text-xs">
                            {{ $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i') : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-slate-400">
                            <i class="fa-solid fa-inbox text-3xl mb-2"></i>
                            <p>Aucune opération enregistrée pour cet agent pour le moment.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($operations->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $operations->links() }}
        </div>
    @endif
</div>

@endsection
