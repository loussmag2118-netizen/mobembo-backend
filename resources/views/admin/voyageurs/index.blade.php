@extends('layouts.admin')

@section('content')
<div class="space-y-7" x-data="{ searchQuery: '' }">

    {{-- EN-TÊTE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-sky-100 text-sky-600 border border-sky-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-person-walking-luggage"></i>
                </span>
                <span>Gestion des Voyageurs</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Passagers inscrits via l'application mobile — Agence : <strong>{{ $agenceAdmin ?? 'Centrale' }}</strong>
            </p>
        </div>

        <span class="px-4 py-2.5 rounded-2xl text-sm font-bold bg-sky-50 text-sky-700 border border-sky-200 flex items-center gap-2 self-start sm:self-auto">
            <i class="fa-solid fa-mobile-alt text-sky-500"></i>
            Synchronisé depuis l'App Mobile
        </span>
    </div>

    {{-- TABLE DES VOYAGEURS --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">

        <div class="p-6 sm:px-8 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="relative w-full sm:w-96">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400"></i>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Rechercher par nom, référence..." 
                       class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition-all">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-black uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-8">Voyageur</th>
                        <th class="py-4 px-8">Référence / Ticket</th>
                        <th class="py-4 px-8">Trajet</th>
                        <th class="py-4 px-8">Date de Voyage</th>
                        <th class="py-4 px-8">Paiement</th>
                        <th class="py-4 px-8 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($voyageurs as $v)
                        @php
                            $nomComplet = ($v->nom ?? '') . ' ' . ($v->prenom ?? '');
                            $initiales = strtoupper(substr($v->nom ?? 'V', 0, 1) . substr($v->prenom ?? '', 0, 1));
                        @endphp
                        <tr x-show="searchQuery === '' || '{{ strtolower($nomComplet) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($v->reference ?? '') }}'.includes(searchQuery.toLowerCase())"
                            class="hover:bg-sky-50/20 transition-colors group">

                            <td class="py-5 px-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-100 to-blue-100 text-sky-700 font-black text-sm flex items-center justify-center shrink-0 border border-sky-200/60">
                                        {{ $initiales ?: 'VG' }}
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-slate-900 text-base">{{ $v->nom }} {{ $v->prenom ?? '' }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5 font-semibold">ID #{{ $v->id }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-5 px-8">
                                <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-800 font-mono text-sm font-bold border border-slate-200">
                                    {{ $v->reference ?? '#' . $v->id }}
                                </span>
                            </td>

                            <td class="py-5 px-8 text-sm font-semibold text-slate-700">
                                {{ $v->nom_trajet ?? 'N/A' }}
                            </td>

                            <td class="py-5 px-8 text-sm font-semibold text-slate-700">
                                <i class="fa-regular fa-calendar text-slate-400 mr-1.5"></i>
                                {{ $v->date_voyage ?? 'N/A' }}
                            </td>

                            <td class="py-5 px-8">
                                @php
                                    $badges = [
                                        'Paye'       => ['bg-emerald-50 text-emerald-700 border-emerald-200/60', 'bg-emerald-500', 'Payé'],
                                        'En_Attente' => ['bg-amber-50 text-amber-700 border-amber-200/60', 'bg-amber-500 animate-pulse', 'En attente'],
                                        'Echoue'     => ['bg-slate-100 text-slate-600 border-slate-200', 'bg-slate-400', 'Annulé / échoué'],
                                        'Rembourse'  => ['bg-sky-50 text-sky-700 border-sky-200/60', 'bg-sky-500', 'Remboursé'],
                                    ];
                                    [$classe, $point, $libelle] = $badges[$v->statut_paiement] ?? $badges['En_Attente'];
                                @endphp
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold border {{ $classe }}">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $point }}"></span>
                                    {{ $libelle }}
                                </span>
                                @if($v->billet_test)
                                    <span class="ml-1 px-2 py-1 rounded-lg text-[10px] font-black bg-orange-100 text-orange-700">TEST</span>
                                @endif
                            </td>

                            <td class="py-5 px-8 text-right">
                                @if(auth()->user()?->role === 'Admin')
                                    @if($v->statut_paiement === 'Paye')
                                        <form action="{{ route('admin.voyageurs.rembourser', $v->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Confirmez-vous que ce billet a été remboursé au client ? Sa place sera libérée.')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-2 text-sm font-bold text-sky-700 bg-sky-50 hover:bg-sky-100 px-4 py-2 rounded-xl transition-colors border border-sky-100">
                                                <i class="fa-solid fa-rotate-left"></i>
                                                <span>Marquer remboursé</span>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.voyageurs.destroy', $v->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce voyageur ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-2 text-sm font-bold text-red-600 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-xl transition-colors border border-red-100">
                                            <i class="fa-solid fa-trash-can"></i>
                                            <span>Supprimer</span>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-20 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 rounded-3xl bg-slate-100 flex items-center justify-center">
                                        <i class="fa-solid fa-person-walking-luggage text-4xl text-slate-300"></i>
                                    </div>
                                    <div>
                                        <p class="text-base font-bold text-slate-600">Aucun voyageur enregistré pour cette agence.</p>
                                        <p class="text-sm text-slate-400 mt-1">Les passagers de l'application mobile apparaîtront ici.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
