@extends('layouts.admin')

@section('content')
<div class="space-y-7">

    {{-- EN-TÊTE AVEC TITRE ET ACTIONS --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.planifications.index') }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-emerald-700 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm transition-all">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Retour aux planifications</span>
                </a>
                <span class="text-xs font-extrabold text-slate-400">/</span>
                <span class="text-xs font-black text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-200/60">
                    Voyage #{{ $planificationVoyage->id }}
                </span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-indigo-100 text-indigo-600 border border-indigo-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-ticket-simple"></i>
                </span>
                <span>Détails de la Planification #{{ $planificationVoyage->id }}</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Feuille de route complète, remplissage des places et passagers enregistrés.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.planifications.edit', $planificationVoyage->id) }}"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white px-5 py-3 rounded-2xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98]">
                <i class="fa-solid fa-pen-to-square text-amber-400 text-base"></i>
                <span>Modifier le voyage</span>
            </a>
        </div>
    </div>

    @php
        $statut = $planificationVoyage->statut_voyage ?? 'Programmation';
        $statutBadges = [
            'Programmation' => ['bg-blue-50 text-blue-700 border-blue-200/60', 'bg-blue-500', 'fa-calendar'],
            'En cours'      => ['bg-amber-50 text-amber-700 border-amber-200/60', 'bg-amber-500', 'fa-rocket'],
            'Terminé'       => ['bg-emerald-50 text-emerald-700 border-emerald-200/60', 'bg-emerald-500', 'fa-circle-check'],
            'Annulé'        => ['bg-red-50 text-red-700 border-red-200/60', 'bg-red-500', 'fa-ban'],
        ];
        [$badgeClass, $dotClass, $statutIcon] = $statutBadges[$statut] ?? ['bg-slate-100 text-slate-700 border-slate-200', 'bg-slate-400', 'fa-circle'];

        $vendus    = $planificationVoyage->tickets ? $planificationVoyage->tickets->count() : 0;
        $restantes = (int) ($planificationVoyage->places_disponibles ?? 0);
        $total     = $planificationVoyage->bus?->capacite_totale
                   ?? $planificationVoyage->bus?->places
                   ?? ($restantes + $vendus);
        $taux      = $total > 0 ? round(($vendus / $total) * 100) : 0;
        $prixVoyage = $planificationVoyage->prix ?? 0;
        $recetteEstimee = $vendus * $prixVoyage;
    @endphp

    {{-- STATS RAPIDES EN CARTES --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Statut --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Statut Voyage</p>
                <div class="mt-2">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-sm font-bold border {{ $badgeClass }}">
                        <span class="w-2.5 h-2.5 rounded-full {{ $dotClass }}"></span>
                        <span>{{ $statut }}</span>
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid {{ $statutIcon }}"></i>
            </div>
        </div>

        {{-- Date & Heure --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Départ Prévu</p>
                <p class="text-lg font-black text-slate-900 mt-1">
                    {{ $planificationVoyage->date_voyage ? \Carbon\Carbon::parse($planificationVoyage->date_voyage)->format('d/m/Y') : '—' }}
                </p>
                <p class="text-xs font-bold text-indigo-600 mt-0.5 font-mono">
                    <i class="fa-regular fa-clock mr-1"></i>
                    {{ substr((string) optional($planificationVoyage->vagueDepart)->heure_depart, 0, 5) ?: 'Heure N/C' }}
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-regular fa-calendar-check"></i>
            </div>
        </div>

        {{-- Jauge de places --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">Remplissage</p>
                    <span class="text-xs font-black text-slate-700">{{ $taux }}%</span>
                </div>
                <p class="text-2xl font-black text-slate-900 mt-1">
                    {{ $vendus }} <span class="text-sm font-semibold text-slate-400">/ {{ $total }} places</span>
                </p>
            </div>
            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden mt-3">
                <div class="h-full rounded-full transition-all duration-500 {{ $taux >= 90 ? 'bg-rose-500' : ($taux >= 60 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                     style="width: {{ min(100, $taux) }}%;"></div>
            </div>
        </div>

        {{-- Recettes --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Recettes Billetterie</p>
                <p class="text-2xl font-black text-emerald-700 mt-1">
                    {{ number_format($recetteEstimee, 0, ',', ' ') }}
                </p>
                <p class="text-xs font-bold text-slate-400 mt-0.5">FCFA estimé</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>
    </div>

    {{-- FICHE DÉTAILLÉE DU TRAJET & DU VÉHICULE --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- Trajet & Gares --}}
        <div class="bg-white rounded-3xl p-7 border border-slate-200/80 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h2 class="text-lg font-black text-slate-900 flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-route"></i>
                    </span>
                    <span>Itinéraire & Ligne</span>
                </h2>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ligne directe</span>
            </div>

            <div class="space-y-4">
                <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 text-base font-bold">
                        <i class="fa-solid fa-arrow-up-from-bracket"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Gare de départ</p>
                        <p class="text-base font-extrabold text-slate-900">
                            {{ $planificationVoyage->trajet->gareDepart->nom_gare ?? 'Gare Départ' }}
                        </p>
                        <p class="text-xs font-semibold text-slate-500">
                            Ville : {{ $planificationVoyage->trajet->ville_depart ?? $planificationVoyage->trajet->gareDepart->ville ?? '?' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 text-base font-bold">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Gare d'arrivée</p>
                        <p class="text-base font-extrabold text-slate-900">
                            {{ $planificationVoyage->trajet->gareArrivee->nom_gare ?? 'Gare Arrivée' }}
                        </p>
                        <p class="text-xs font-semibold text-slate-500">
                            Ville : {{ $planificationVoyage->trajet->ville_arrivee ?? $planificationVoyage->trajet->gareArrivee->ville ?? '?' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 px-1 text-sm font-bold text-slate-600">
                    <span>Prix unitaire standard :</span>
                    <span class="text-base font-black text-slate-900">{{ number_format($prixVoyage, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        {{-- Bus affecté --}}
        <div class="bg-white rounded-3xl p-7 border border-slate-200/80 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h2 class="text-lg font-black text-slate-900 flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-bus"></i>
                    </span>
                    <span>Véhicule Assigné</span>
                </h2>
                <a href="{{ route('admin.bus.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                    Gérer la flotte →
                </a>
            </div>

            @if($planificationVoyage->bus)
                @php
                    $statutBus = $planificationVoyage->bus->statut ?? 'Disponible';
                @endphp
                <div class="space-y-4">
                    <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Plaque d'immatriculation</span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statutBus === 'Disponible' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-amber-500/20 text-amber-300' }}">
                                {{ $statutBus }}
                            </span>
                        </div>
                        <p class="text-3xl font-black text-white tracking-wider mt-2 font-mono">
                            {{ $planificationVoyage->bus->immatriculation }}
                        </p>
                        <p class="text-sm font-semibold text-slate-300 mt-1">
                            {{ $planificationVoyage->bus->modele ?: 'Modèle standard' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <p class="text-xs font-bold text-slate-400 uppercase">Capacité totale</p>
                            <p class="text-xl font-black text-slate-900 mt-1">{{ $planificationVoyage->bus->capacite_totale ?? $total }} places</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <p class="text-xs font-bold text-slate-400 uppercase">Places restantes</p>
                            <p class="text-xl font-black text-emerald-600 mt-1">{{ $restantes }} places</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="py-12 text-center text-slate-400">
                    <i class="fa-solid fa-triangle-exclamation text-3xl mb-2 text-amber-500"></i>
                    <p class="font-bold text-slate-700">Aucun bus n'a encore été assigné à ce voyage.</p>
                    <p class="text-xs text-slate-400 mt-1">Éditez la planification pour choisir un véhicule.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- LISTE DES TICKETS PASSAGERS ASSOCIÉS --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 sm:px-8 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-900 flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-users"></i>
                    </span>
                    <span>Passagers & Billets Enregistrés</span>
                </h2>
                <p class="text-slate-400 text-xs font-semibold mt-1 ml-[46px]">
                    Liste des voyageurs ayant réservé ou embarqué sur ce départ.
                </p>
            </div>

            <span class="text-sm font-bold text-slate-600 bg-slate-100 px-4 py-2.5 rounded-2xl">
                <i class="fa-solid fa-ticket text-indigo-500 mr-1.5"></i>
                {{ $vendus }} ticket(s) émis
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-black uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-8">Référence Billet</th>
                        <th class="py-4 px-8">Passager</th>
                        <th class="py-4 px-8">Téléphone</th>
                        <th class="py-4 px-8">Destination & Quartier</th>
                        <th class="py-4 px-8">Statut Embarquement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($planificationVoyage->tickets ?? [] as $ticket)
                    @php
                        $statutTicket = $ticket->statut_embarquement ?? 'En_Attente';
                        $statutTBadges = [
                            'Present'    => ['bg-emerald-50 text-emerald-700 border-emerald-200/60', 'bg-emerald-500', 'Présent'],
                            'Absent'     => ['bg-rose-50 text-rose-700 border-rose-200/60', 'bg-rose-500', 'Absent'],
                            'En_Attente' => ['bg-amber-50 text-amber-700 border-amber-200/60', 'bg-amber-500', 'En attente'],
                        ];
                        [$tBadgeClass, $tDotClass, $tLabel] = $statutTBadges[$statutTicket] ?? ['bg-slate-100 text-slate-700 border-slate-200', 'bg-slate-400', str_replace('_', ' ', $statutTicket)];
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        {{-- RÉFÉRENCE --}}
                        <td class="py-5 px-8">
                            <span class="inline-flex items-center gap-2 font-mono text-sm font-extrabold text-slate-900 bg-slate-100 px-3.5 py-1.5 rounded-xl border border-slate-200/60">
                                <i class="fa-solid fa-barcode text-slate-400 text-xs"></i>
                                {{ $ticket->reference_ticket ?? '—' }}
                            </span>
                        </td>

                        {{-- PASSAGER --}}
                        <td class="py-5 px-8">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($ticket->nom_passager ?? 'P', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-extrabold text-slate-900 text-sm">
                                        {{ trim(($ticket->nom_passager ?? '') . ' ' . ($ticket->prenom_passager ?? '')) ?: 'Client direct' }}
                                    </p>
                                    <p class="text-xs text-slate-400 font-semibold">Billet ID #{{ $ticket->id }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- TÉLÉPHONE --}}
                        <td class="py-5 px-8 text-sm font-semibold text-slate-700">
                            @if($ticket->telephone_passager)
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-phone text-slate-400 text-xs"></i>
                                    {{ $ticket->telephone_passager }}
                                </span>
                            @else
                                <span class="text-slate-400 italic">—</span>
                            @endif
                        </td>

                        {{-- DESTINATION --}}
                        <td class="py-5 px-8">
                            <div class="text-sm font-bold text-slate-800">
                                {{ $ticket->ville_arrivee ?? '—' }}
                            </div>
                            @if($ticket->quartier)
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200/50 mt-0.5">
                                    <i class="fa-solid fa-location-dot text-[10px]"></i>
                                    {{ $ticket->quartier }}
                                </span>
                            @endif
                        </td>

                        {{-- STATUT EMBARQUEMENT --}}
                        <td class="py-5 px-8">
                            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold border {{ $tBadgeClass }}">
                                <span class="w-2 h-2 rounded-full {{ $tDotClass }}"></span>
                                <span>{{ $tLabel }}</span>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center">
                                    <i class="fa-solid fa-ticket text-3xl text-slate-300"></i>
                                </div>
                                <p class="text-base font-bold text-slate-600">Aucun billet vendu pour le moment.</p>
                                <p class="text-xs text-slate-400">Les réservations apparaîtront ici dès que les passagers achèteront des places.</p>
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