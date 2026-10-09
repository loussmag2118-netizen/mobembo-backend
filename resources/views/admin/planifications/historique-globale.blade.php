@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Historique Général des Ventes & Opérations par Gare</h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
            ← Retour au tableau de bord
        </a>
    </div>

    @if(isset($donneesGares) && count($donneesGares) > 0)
        @foreach($donneesGares as $item)
            <div class="bg-white shadow ring-1 ring-slate-200 rounded-xl overflow-hidden mb-8">
                <!-- En-tête de la Gare (Moukondo, Mpila, etc.) -->
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-indigo-700">
                        📍 Gare : {{ $item['gare']->nom_gare ?? $item['gare']->nom }}
                    </h2>
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-1 rounded-full">
                        {{ $item['tickets']->count() }} Client(s)
                    </span>
                </div>

                <div class="p-6">
                    @if($item['tickets']->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        <th class="px-4 py-3">N° Billet</th>
                                        <th class="px-4 py-3">Client</th>
                                        <th class="px-4 py-3">Trajet / Voyage</th>
                                        <th class="px-4 py-3">Agent Vendeur</th>
                                        <th class="px-4 py-3">Montant</th>
                                        <th class="px-4 py-3">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 text-sm">
                                    @foreach($item['tickets'] as $op)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-slate-900">#{{ $op->numero_billet ?? $op->id }}</td>
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-slate-900">{{ $op->nom_client ?? 'Client direct' }}</div>
                                                <div class="text-xs text-slate-500">{{ $op->telephone_client ?? '' }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-slate-600">
                                                {{ $op->voyage->trajet->nom ?? 'Voyage #' . ($op->planification_voyage_id ?? 'N/C') }}
                                            </td>
                                            <td class="px-4 py-3 text-slate-600">
                                                {{ $op->agentVendeur->nom ?? 'Système / Web' }}
                                            </td>
                                            <td class="px-4 py-3 font-semibold text-emerald-600">
                                                {{ number_format($op->prix ?? 0, 0, ',', ' ') }} FCFA
                                            </td>
                                            <td class="px-4 py-3 text-slate-500">
                                                {{ $op->created_at->format('d/m/Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-slate-400 text-sm italic py-2">Aucun client n'a payé de billet dans cette gare pour le moment.</p>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="bg-white shadow ring-1 ring-slate-200 rounded-xl overflow-hidden p-6 text-center">
            <p class="text-slate-500 py-6">Aucune opération enregistrée pour le moment.</p>
        </div>
    @endif
</div>
@endsection
