@extends('layouts.admin')

@section('title', 'Réservation ' . $reservation->reference)
@section('header_icon', 'fa-ticket')
@section('header_title', 'Réservation ' . $reservation->reference)
@section('header_subtitle', 'Créée le ' . $reservation->created_at?->format('d/m/Y à H:i'))

@section('content')
@php
    $r = $reservation;
    $vol = $r->vol;
    $paye = in_array($r->statut, ['Paye', 'Confirmee'], true);
@endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('canadian.reservations.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-slate-900"><i class="fa-solid fa-arrow-left"></i> Toutes les réservations</a>
        @include('admin.canadian.partials.statut-reservation', ['statut' => $r->statut])
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            {{-- Vol --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
                <h2 class="text-base font-black text-slate-900 mb-4">Vol</h2>
                @if($vol)
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="text-3xl font-black font-mono text-slate-900">{{ $vol->ville_depart_iata ?: '—' }}</p><p class="text-sm font-bold text-slate-600">{{ $vol->ville_depart }}</p><p class="text-xs text-slate-400">{{ $vol->date_depart?->format('d/m/Y H:i') }}</p></div>
                        <div class="text-center flex-1">
                            <a href="{{ route('canadian.vols.show', $vol->id) }}" class="font-mono font-black text-slate-800 hover:underline">{{ $vol->numero_vol }}</a>
                            <div class="flex items-center gap-2 my-1"><span class="flex-1 h-px bg-slate-200"></span><i class="fa-solid fa-plane text-slate-400"></i><span class="flex-1 h-px bg-slate-200"></span></div>
                            <p class="text-xs text-slate-400">{{ $vol->duree_formatee }}</p>
                        </div>
                        <div class="text-right"><p class="text-3xl font-black font-mono text-slate-900">{{ $vol->ville_arrivee_iata ?: '—' }}</p><p class="text-sm font-bold text-slate-600">{{ $vol->ville_arrivee }}</p><p class="text-xs text-slate-400">{{ $vol->date_arrivee?->format('d/m/Y H:i') }}</p></div>
                    </div>
                @else
                    <p class="text-sm text-slate-400">Vol supprimé.</p>
                @endif
            </div>

            {{-- Passagers --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-5 sm:px-6 border-b border-slate-100"><h2 class="text-base font-black text-slate-900">Passagers ({{ $r->nombre_places }})</h2></div>
                @forelse($r->passagers as $p)
                    <div class="px-6 py-3.5 border-b border-slate-100 last:border-0 flex flex-wrap items-center justify-between gap-2 text-sm">
                        <div><p class="font-bold text-slate-900">{{ trim($p->prenom . ' ' . $p->nom) }}</p><p class="text-xs text-slate-400">{{ ucfirst($p->type) }}{{ $p->date_naissance ? ' · né(e) le ' . \Carbon\Carbon::parse($p->date_naissance)->format('d/m/Y') : '' }}{{ $p->nationalite ? ' · ' . $p->nationalite : '' }}</p></div>
                        @if($p->numero_passeport)<span class="font-mono text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">Passeport {{ $p->numero_passeport }}</span>@endif
                    </div>
                @empty
                    <p class="px-6 py-5 text-sm text-slate-400">Détail des passagers non renseigné : {{ $r->nombre_places }} place(s) au nom de {{ $r->nom_passager ?: 'du client' }}.</p>
                @endforelse
            </div>

            @if($r->reservationExtras->isNotEmpty())
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
                    <h2 class="text-base font-black text-slate-900 mb-3">Options</h2>
                    @foreach($r->reservationExtras as $e)
                        <div class="flex justify-between text-sm py-1.5"><span>{{ $e->extra?->nom ?? 'Option' }} × {{ $e->quantite }}</span><span class="font-bold">{{ number_format($e->prix_total, 0, ',', ' ') }} F</span></div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="space-y-6">
            {{-- Client et paiement --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-3 text-sm">
                <h2 class="text-base font-black text-slate-900">Client et paiement</h2>
                <div class="flex justify-between gap-3"><span class="text-slate-500">Client</span><span class="font-bold text-right">{{ $r->nom_passager ?: ($r->canadianUser?->name ?? '—') }}</span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500">Téléphone</span><span class="font-bold">{{ $r->telephone ?: '—' }}</span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500">Montant</span><span class="font-black text-slate-900">{{ number_format($r->montant_total, 0, ',', ' ') }} FCFA</span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500">Moyen</span><span class="font-bold">{{ $r->methode_paiement ?: '—' }}</span></div>
                @if($r->date_paiement)<div class="flex justify-between gap-3"><span class="text-slate-500">Payée le</span><span class="font-bold">{{ $r->date_paiement->format('d/m/Y H:i') }}</span></div>@endif
                @if($r->paiement?->reference_transaction)<div class="flex justify-between gap-3"><span class="text-slate-500">Transaction</span><span class="font-mono text-xs font-bold break-all text-right">{{ $r->paiement->reference_transaction }}</span></div>@endif
                @if($r->code_pnr)<div class="flex justify-between gap-3"><span class="text-slate-500">PNR</span><span class="font-mono font-black text-lg tracking-widest">{{ $r->code_pnr }}</span></div>@endif
                @if($r->statut === 'En_Attente' && $r->expire_le)<p class="text-xs text-amber-700 bg-amber-50 rounded-xl p-3">Places bloquées jusqu'à {{ $r->expire_le->format('H:i') }} en attendant le paiement.</p>@endif
                @if(\App\Services\PaiementService::estReferenceTest($r->reference_paiement))<p class="text-xs text-orange-700 bg-orange-50 rounded-xl p-3"><i class="fa-solid fa-flask"></i> Paiement de test : aucun argent n'a été encaissé.</p>@endif
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-2.5">
                <h2 class="text-base font-black text-slate-900 mb-1">Actions</h2>
                @if($r->statut === 'Paye')
                    <form method="POST" action="{{ route('canadian.reservations.modifierStatut', $r->id) }}">@csrf @method('PATCH')
                        <input type="hidden" name="statut" value="Confirmee">
                        <button class="w-full py-2.5 rounded-xl bg-[#124231] hover:bg-[#0c2f23] text-white text-sm font-bold"><i class="fa-solid fa-plane-circle-check mr-1"></i> Confirmer l'embarquement</button>
                    </form>
                @endif
                @if($paye)
                    <form method="POST" action="{{ route('canadian.reservations.renvoyerBillet', $r->id) }}">@csrf
                        <button class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold"><i class="fa-solid fa-paper-plane mr-1"></i> Renvoyer le billet au client</button>
                    </form>
                @endif
                @if($r->statut === 'A_Rembourser')
                    <form method="POST" action="{{ route('canadian.reservations.rembourser', $r->id) }}" onsubmit="return confirm('Confirmez-vous avoir remboursé {{ number_format($r->montant_total, 0, ',', ' ') }} FCFA au client ?')">@csrf
                        <button class="w-full py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold"><i class="fa-solid fa-rotate-left mr-1"></i> Remboursement effectué</button>
                    </form>
                @endif
                @if($r->occupeDesPlaces())
                    <form method="POST" action="{{ route('canadian.reservations.annuler', $r->id) }}" onsubmit="return confirm('Annuler cette réservation ? {{ $paye ? 'Elle passera « à rembourser ».' : 'Les places seront rendues.' }}')">@csrf
                        <button class="w-full py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-bold"><i class="fa-solid fa-ban mr-1"></i> Annuler la réservation</button>
                    </form>
                @endif
                @unless($r->occupeDesPlaces() || $r->statut === 'A_Rembourser')
                    <p class="text-xs text-slate-400">Aucune action possible sur une réservation {{ mb_strtolower(\App\Http\Controllers\Api\CanadianClientController::libelleStatut($r->statut)) }}.</p>
                @endunless
            </div>
        </div>
    </div>
</div>
@endsection
