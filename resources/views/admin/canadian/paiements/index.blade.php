@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Paiements & Transactions</h1>
            <p class="text-sm text-slate-500 mt-1">
                Suivi et gestion de tous les paiements
            </p>
        </div>
        <a href="{{ route('canadian.paiements.export') }}"
           class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold rounded-xl shadow-sm transition">
            <i class="fa-solid fa-file-export mr-2"></i> Exporter CSV
        </a>
    </div>

    {{-- Message succès --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Message erreur --}}
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Cartes financières --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-6 text-white shadow-lg shadow-emerald-500/20">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2.5 bg-white/20 backdrop-blur-sm rounded-xl">
                    <i class="fa-solid fa-wallet text-xl"></i>
                </div>
                <i class="fa-solid fa-arrow-trend-up text-white/40 text-2xl"></i>
            </div>
            <p class="text-xs uppercase tracking-wider text-white/80 mb-1">Revenus totaux</p>
            <p class="text-3xl font-black">{{ number_format($revenus ?? 0, 0, ',', ' ') }} FCFA</p>
            <p class="text-xs text-white/70 mt-2">Depuis le début</p>
        </div>

        <div class="bg-gradient-to-br from-[#C62828] to-[#8E0000] rounded-2xl p-6 text-white shadow-lg shadow-red-500/20">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2.5 bg-white/20 backdrop-blur-sm rounded-xl">
                    <i class="fa-solid fa-credit-card text-xl"></i>
                </div>
                <i class="fa-solid fa-check text-white/40 text-2xl"></i>
            </div>
            <p class="text-xs uppercase tracking-wider text-white/80 mb-1">Transactions réussies</p>
            <p class="text-3xl font-black">{{ $reussies ?? 0 }}</p>
            <p class="text-xs text-white/70 mt-2">Paiements validés</p>
        </div>

        <div class="bg-gradient-to-br from-amber-500 to-amber-700 rounded-2xl p-6 text-white shadow-lg shadow-amber-500/20">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2.5 bg-white/20 backdrop-blur-sm rounded-xl">
                    <i class="fa-solid fa-clock text-xl"></i>
                </div>
                <i class="fa-solid fa-hourglass text-white/40 text-2xl"></i>
            </div>
            <p class="text-xs uppercase tracking-wider text-white/80 mb-1">En attente</p>
            <p class="text-3xl font-black">{{ $en_attente ?? 0 }}</p>
            <p class="text-xs text-white/70 mt-2">À traiter</p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <form method="GET" action="{{ route('canadian.paiements.index') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Référence, client, téléphone..."
                       class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#C62828] focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Méthode</label>
                <select name="methode"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                    <option value="">Toutes</option>
                    <option value="MTN" {{ request('methode') == 'MTN' ? 'selected' : '' }}>MTN Mobile Money</option>
                    <option value="Airtel" {{ request('methode') == 'Airtel' ? 'selected' : '' }}>Airtel Money</option>
                    <option value="Especes" {{ request('methode') == 'Especes' ? 'selected' : '' }}>Espèces</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 bg-[#C62828] hover:bg-[#8E0000] text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filtrer
                </button>
                <a href="{{ route('canadian.paiements.index') }}"
                   class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-semibold transition">
                    <i class="fa-solid fa-rotate"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Tableau --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">ID Transaction</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Client</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Méthode</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Montant</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Date</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Statut</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($paiements as $paiement)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <span class="font-mono text-xs text-slate-600">
                                {{ $paiement->reference_transaction ?? '#PAY-' . $paiement->id }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">
                                {{ $paiement->nom_client ?? '—' }}
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $paiement->telephone_client ?? '' }}
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $methodes = [
                                    'MTN'     => ['label' => 'MTN MoMo',      'bg' => 'bg-yellow-50 text-yellow-700'],
                                    'Airtel'  => ['label' => 'Airtel Money',  'bg' => 'bg-red-50 text-red-700'],
                                    'Especes' => ['label' => 'Espèces',       'bg' => 'bg-slate-100 text-slate-700'],
                                ];
                                $m = $methodes[$paiement->methode] ?? ['label' => $paiement->methode ?? '—', 'bg' => 'bg-slate-100 text-slate-700'];
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $m['bg'] }}">
                                {{ $m['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 font-bold text-slate-900">
                            {{ number_format($paiement->montant ?? 0, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-5 py-4 text-slate-600 text-xs">
                            {{ $paiement->created_at ? $paiement->created_at->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $badges = [
                                    'Reussi'     => 'bg-emerald-50 text-emerald-700',
                                    'En_Attente' => 'bg-amber-50 text-amber-700',
                                    'Echoue'     => 'bg-red-50 text-[#C62828]',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badges[$paiement->statut] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ str_replace('_', ' ', $paiement->statut ?? 'En attente') }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ route('canadian.paiements.show', $paiement->id) }}"
                                   class="text-slate-500 hover:text-[#C62828] transition" title="Voir">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-4">
                                <i class="fa-solid fa-credit-card text-2xl text-slate-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700 mb-2">Aucun paiement</h3>
                            <p class="text-sm text-slate-500">
                                Les transactions apparaîtront ici.
                            </p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($paiements->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                {{ $paiements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection