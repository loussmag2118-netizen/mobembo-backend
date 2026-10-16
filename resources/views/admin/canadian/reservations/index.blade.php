@extends('layouts.admin')

@section('title', 'Réservations')
@section('header_icon', 'fa-ticket')
@section('header_title', 'Réservations')
@section('header_subtitle', 'Réservations faites dans l\'application : une réservation non payée libère ses places après 15 minutes')

@section('content')
@php $statuts = \App\Http\Controllers\Admin\Canadian\ReservationController::STATUTS; @endphp
<div class="space-y-6">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.canadian.partials.stat', ['titre' => 'Payées', 'valeur' => ($compteurs['Paye'] ?? 0) + ($compteurs['Confirmee'] ?? 0), 'icone' => 'fa-circle-check', 'teinte' => 'emerald'])
        @include('admin.canadian.partials.stat', ['titre' => 'En attente de paiement', 'valeur' => $compteurs['En_Attente'] ?? 0, 'icone' => 'fa-hourglass-half', 'teinte' => 'amber'])
        @include('admin.canadian.partials.stat', ['titre' => 'À rembourser', 'valeur' => $compteurs['A_Rembourser'] ?? 0, 'icone' => 'fa-rotate-left', 'teinte' => 'rose'])
        @include('admin.canadian.partials.stat', ['titre' => 'Annulées / expirées', 'valeur' => ($compteurs['Annulee'] ?? 0) + ($compteurs['Expiree'] ?? 0) + ($compteurs['Echouee'] ?? 0), 'icone' => 'fa-ban', 'teinte' => 'slate'])
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <form method="GET" class="p-5 sm:px-6 border-b border-slate-100 flex flex-col sm:flex-row gap-2">
            <div class="relative sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Référence, PNR, nom, téléphone, vol…"
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-[#124231] focus:outline-none">
            </div>
            <select name="statut" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600">
                <option value="">Tous les statuts</option>
                @foreach($statuts as $cle => $libelle)
                    <option value="{{ $cle }}" @selected(request('statut') === $cle)>{{ $libelle }} ({{ $compteurs[$cle] ?? 0 }})</option>
                @endforeach
            </select>
            <button class="bg-[#124231] hover:bg-[#0c2f23] text-white text-sm font-bold py-2 px-4 rounded-xl">Rechercher</button>
            @if(request()->hasAny(['q', 'statut']))
                <a href="{{ route('canadian.reservations.index') }}" class="text-sm font-bold text-slate-500 hover:text-slate-800 py-2 px-3 text-center">Effacer</a>
            @endif
        </form>

        @if($reservations->isEmpty())
            @include('admin.canadian.partials.vide', ['icone' => 'fa-ticket', 'titre' => 'Aucune réservation', 'texte' => request()->hasAny(['q', 'statut']) ? 'Aucune réservation ne correspond à ces critères.' : 'Les réservations faites depuis l\'application apparaîtront ici.'])
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-6">Référence</th><th class="py-3 px-4">Client</th><th class="py-3 px-4">Vol</th><th class="py-3 px-4">Places</th><th class="py-3 px-4">Montant</th><th class="py-3 px-4">Statut</th><th class="py-3 px-6 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($reservations as $r)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3.5 px-6">
                                    <p class="font-mono font-bold text-slate-900">{{ $r->reference }}</p>
                                    <p class="text-xs text-slate-400">{{ $r->code_pnr ? 'PNR ' . $r->code_pnr . ' · ' : '' }}{{ $r->created_at?->format('d/m/Y H:i') }}</p>
                                </td>
                                <td class="py-3.5 px-4"><p class="font-semibold text-slate-800">{{ $r->nom_passager ?: ($r->canadianUser?->name ?? '—') }}</p><p class="text-xs text-slate-400">{{ $r->telephone }}</p></td>
                                <td class="py-3.5 px-4">
                                    @if($r->vol)
                                        <p class="font-mono font-bold text-slate-800">{{ $r->vol->numero_vol }}</p>
                                        <p class="text-xs text-slate-400">{{ $r->vol->ville_depart }} → {{ $r->vol->ville_arrivee }} · {{ $r->vol->date_depart?->format('d/m H:i') }}</p>
                                    @else — @endif
                                </td>
                                <td class="py-3.5 px-4 font-bold">{{ $r->nombre_places }}</td>
                                <td class="py-3.5 px-4 font-bold whitespace-nowrap">{{ number_format($r->montant_total, 0, ',', ' ') }} F</td>
                                <td class="py-3.5 px-4">@include('admin.canadian.partials.statut-reservation', ['statut' => $r->statut])</td>
                                <td class="py-3.5 px-6 text-right">
                                    <a href="{{ route('canadian.reservations.show', $r->id) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 px-3 py-2 rounded-xl">Ouvrir <i class="fa-solid fa-chevron-right text-[10px]"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($reservations->hasPages())<div class="px-6 py-4 border-t border-slate-100">{{ $reservations->links() }}</div>@endif
        @endif
    </div>
</div>
@endsection
