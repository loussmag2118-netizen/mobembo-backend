@extends('layouts.admin')

@section('title', 'Paiements')
@section('header_icon', 'fa-money-bill-wave')
@section('header_title', 'Paiements')
@section('header_subtitle', 'Encaissements confirmés par MTN Mobile Money et Airtel Money')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.canadian.partials.stat', ['titre' => 'Aujourd\'hui', 'valeur' => number_format($stats['jour'], 0, ',', ' ') . ' F', 'icone' => 'fa-calendar-day', 'teinte' => 'sky'])
        @include('admin.canadian.partials.stat', ['titre' => 'Ce mois-ci', 'valeur' => number_format($stats['mois'], 0, ',', ' ') . ' F', 'icone' => 'fa-calendar', 'teinte' => 'emerald'])
        @include('admin.canadian.partials.stat', ['titre' => 'Total encaissé', 'valeur' => number_format($stats['total'], 0, ',', ' ') . ' F', 'icone' => 'fa-sack-dollar', 'teinte' => 'amber'])
        @include('admin.canadian.partials.stat', ['titre' => 'Paiements', 'valeur' => $stats['nombre'], 'icone' => 'fa-receipt', 'teinte' => 'slate',
            'note' => 'MTN ' . number_format($parMethode['MTN'] ?? 0, 0, ',', ' ') . ' F · Airtel ' . number_format($parMethode['Airtel'] ?? 0, 0, ',', ' ') . ' F'])
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <form method="GET" class="p-5 sm:px-6 border-b border-slate-100 flex flex-col lg:flex-row gap-2 lg:items-center">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Transaction, client, téléphone…" class="lg:w-64 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-[#124231] focus:outline-none">
            <select name="methode" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600">
                <option value="">MTN et Airtel</option>
                <option value="MTN" @selected(request('methode') === 'MTN')>MTN</option>
                <option value="Airtel" @selected(request('methode') === 'Airtel')>Airtel</option>
            </select>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <input type="date" name="du" value="{{ request('du') }}" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm"> au
                <input type="date" name="au" value="{{ request('au') }}" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
            </div>
            <button class="bg-[#124231] hover:bg-[#0c2f23] text-white text-sm font-bold py-2 px-4 rounded-xl">Filtrer</button>
            <a href="{{ route('canadian.paiements.export', request()->query()) }}" class="lg:ml-auto inline-flex items-center justify-center gap-2 text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 py-2 px-4 rounded-xl"><i class="fa-solid fa-file-csv"></i> Exporter (Excel)</a>
        </form>

        @if($paiements->isEmpty())
            @include('admin.canadian.partials.vide', ['icone' => 'fa-money-bill-wave', 'titre' => 'Aucun paiement', 'texte' => 'Les paiements confirmés par l\'opérateur apparaissent ici automatiquement.'])
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-6">Date</th><th class="py-3 px-4">Client</th><th class="py-3 px-4">Réservation</th><th class="py-3 px-4">Moyen</th><th class="py-3 px-4">Transaction</th><th class="py-3 px-6 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($paiements as $p)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3.5 px-6 whitespace-nowrap"><p class="font-semibold text-slate-700">{{ $p->created_at?->format('d/m/Y') }}</p><p class="text-xs text-slate-400">{{ $p->created_at?->format('H:i') }}</p></td>
                                <td class="py-3.5 px-4"><p class="font-semibold text-slate-800">{{ $p->nom_client ?: '—' }}</p><p class="text-xs text-slate-400">{{ $p->telephone_client }}</p></td>
                                <td class="py-3.5 px-4">
                                    @if($p->reservation)
                                        <a href="{{ route('canadian.reservations.show', $p->reservation_id) }}" class="font-mono font-bold text-slate-800 hover:underline">{{ $p->reservation->reference }}</a>
                                        <p class="text-xs text-slate-400">{{ $p->reservation->vol?->numero_vol }}</p>
                                    @else — @endif
                                </td>
                                <td class="py-3.5 px-4"><span class="px-2.5 py-1 rounded-full text-xs font-black {{ $p->methode === 'MTN' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-700' }}">{{ $p->methode }}</span></td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-500 break-all max-w-[180px]">{{ $p->reference_transaction }}</td>
                                <td class="py-3.5 px-6 text-right font-black text-slate-900 whitespace-nowrap">{{ number_format($p->montant, 0, ',', ' ') }} F</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($paiements->hasPages())<div class="px-6 py-4 border-t border-slate-100">{{ $paiements->links() }}</div>@endif
        @endif
    </div>
</div>
@endsection
