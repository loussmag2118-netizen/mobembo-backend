@extends('layouts.admin')

@section('content')
<div class="space-y-7">

    {{-- EN-TÊTE AVEC TITRE ET RETOUR --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.dashboard') }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-emerald-700 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm transition-all">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Tableau de bord</span>
                </a>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-cash-register"></i>
                </span>
                <span>Historique Global des Ventes & Agences</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Consolidation des flux billetterie et des clients enregistrés par agence.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="location.reload()" 
                    class="w-11 h-11 rounded-2xl bg-white border border-slate-200/80 text-slate-600 hover:text-slate-900 hover:bg-slate-50 flex items-center justify-center text-sm shadow-sm transition-all" 
                    title="Actualiser">
                <i class="fa-solid fa-rotate-right text-base"></i>
            </button>
        </div>
    </div>

    @if(isset($donneesGares) && count($donneesGares) > 0)
        @foreach($donneesGares as $item)
            @php
                $gareNom = $item['gare']->nom_gare ?? $item['gare']->nom ?? 'Agence';
                $ticketsCount = $item['tickets']->count();
                $montantTotal = $item['tickets']->sum('prix');
            @endphp
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
                
                {{-- En-tête de la Gare --}}
                <div class="p-6 sm:px-8 bg-gradient-to-r from-slate-50 via-white to-slate-50/50 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0 border border-indigo-100 shadow-sm">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-900">
                                {{ $gareNom }}
                            </h2>
                            <p class="text-xs font-bold text-slate-400 mt-0.5 flex items-center gap-1.5">
                                <i class="fa-solid fa-city text-indigo-500"></i>
                                <span>{{ $item['gare']->ville ?? 'Réseau National' }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                            <i class="fa-solid fa-users text-xs"></i>
                            <span>{{ $ticketsCount }} client(s)</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 font-mono">
                            <i class="fa-solid fa-wallet text-xs"></i>
                            <span>{{ number_format($montantTotal, 0, ',', ' ') }} FCFA</span>
                        </span>
                    </div>
                </div>

                {{-- Table des opérations --}}
                <div class="p-6">
                    @if($ticketsCount > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-black uppercase tracking-wider text-slate-500">
                                        <th class="py-4 px-6">N° Billet</th>
                                        <th class="py-4 px-6">Client / Passager</th>
                                        <th class="py-4 px-6">Ligne / Voyage</th>
                                        <th class="py-4 px-6">Agent Émetteur</th>
                                        <th class="py-4 px-6">Montant</th>
                                        <th class="py-4 px-6 text-right">Date & Heure</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    @foreach($item['tickets'] as $op)
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-xs">
                                                    #{{ $op->numero_billet ?? $op->reference_ticket ?? $op->id }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="font-extrabold text-slate-900 text-sm">
                                                    {{ $op->nom_client ?? $op->nom_passager ?? 'Client direct' }}
                                                </div>
                                                <div class="text-xs text-slate-400 font-semibold">
                                                    {{ $op->telephone_client ?? $op->telephone_passager ?? '—' }}
                                                </div>
                                            </td>
                                            <td class="py-4 px-6 text-slate-600 font-semibold text-xs">
                                                {{ $op->voyage->trajet->nom ?? 'Départ #' . ($op->planification_voyage_id ?? 'N/C') }}
                                            </td>
                                            <td class="py-4 px-6">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                                                    <i class="fa-solid fa-user-tie text-slate-400 text-[10px]"></i>
                                                    {{ $op->agentVendeur->nom ?? 'Système Web' }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-6 font-extrabold text-emerald-700 font-mono text-sm">
                                                {{ number_format($op->prix ?? $op->montant_total ?? 0, 0, ',', ' ') }} FCFA
                                            </td>
                                            <td class="py-4 px-6 text-right text-slate-400 font-mono text-xs font-semibold">
                                                {{ $op->created_at ? $op->created_at->format('d/m/Y H:i') : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300"></i>
                            <p class="text-sm font-bold text-slate-600">Aucune vente enregistrée dans cette agence pour le moment.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-16 text-center text-slate-400">
            <div class="w-20 h-20 rounded-3xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-cash-register text-4xl text-slate-300"></i>
            </div>
            <p class="text-lg font-bold text-slate-700">Aucune opération consolidée disponible.</p>
            <p class="text-sm text-slate-400 mt-1">Les ventes aux guichets apparaîtront ici.</p>
        </div>
    @endif

</div>
@endsection
