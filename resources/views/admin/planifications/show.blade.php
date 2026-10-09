@extends('layouts.admin')

@section('content')

<div class="max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">
            Détails de la Planification
        </h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.planifications.edit', $planificationVoyage->id) }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                <i class="fa-solid fa-pen-to-square mr-1"></i> Modifier
            </a>
            <a href="{{ route('admin.planifications.index') }}"
               class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
                ← Retour
            </a>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- INFORMATIONS DU VOYAGE                                       -->
    <!-- ============================================================ -->
    <div class="bg-white shadow ring-1 ring-slate-200 rounded-xl p-6 mb-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">
            <i class="fa-solid fa-circle-info text-indigo-600 mr-2"></i>
            Informations du Voyage
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- TRAJET --}}
            <div>
                <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Trajet</p>
                <div class="text-sm font-medium text-slate-800 flex items-center gap-2 flex-wrap">
                    <i class="fa-solid fa-route text-indigo-600"></i>
                    <span>
                        {{ $planificationVoyage->trajet->gareDepart->nom_gare ?? '?' }}
                        <span class="text-xs text-slate-500">
                            ({{ $planificationVoyage->trajet->gareDepart->ville ?? '?' }})
                        </span>
                    </span>
                    <span class="text-slate-400">→</span>
                    <span>
                        {{ $planificationVoyage->trajet->gareArrivee->nom_gare ?? '?' }}
                        <span class="text-xs text-slate-500">
                            ({{ $planificationVoyage->trajet->gareArrivee->ville ?? '?' }})
                        </span>
                    </span>
                </div>
            </div>

            {{-- DATE --}}
            <div>
                <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Date du voyage</p>
                <p class="text-sm font-medium text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-calendar text-indigo-600"></i>
                    @if($planificationVoyage->date_voyage)
                        {{ \Carbon\Carbon::parse($planificationVoyage->date_voyage)->format('d/m/Y') }}
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </p>
            </div>

            {{-- BUS --}}
            <div>
                <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Bus assigné</p>
                <div class="text-sm font-medium text-slate-800 flex items-center gap-2 flex-wrap">
                    <i class="fa-solid fa-bus text-indigo-600"></i>
                    @if($planificationVoyage->bus)
                        @php
                            $statutBus = $planificationVoyage->bus->statut ?? 'Disponible';
                            $badgeClass = match($statutBus) {
                                'Disponible'   => 'bg-emerald-50 text-emerald-700',
                                'Maintenance'  => 'bg-amber-50 text-amber-700',
                                'Indisponible' => 'bg-red-50 text-red-700',
                                default        => 'bg-slate-100 text-slate-700',
                            };
                            $emoji = match($statutBus) {
                                'Disponible'   => '✅',
                                'Maintenance'  => '🔧',
                                'Indisponible' => '❌',
                                default        => '•',
                            };
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                            {{ $emoji }} {{ $planificationVoyage->bus->immatriculation ?? $planificationVoyage->bus->numero ?? 'Bus #'.$planificationVoyage->bus->id }}
                        </span>
                        @if($planificationVoyage->bus->modele)
                            <span class="text-xs text-slate-500">({{ $planificationVoyage->bus->modele }})</span>
                        @endif
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </div>
            </div>

            {{-- HORAIRE --}}
            <div>
                <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Horaire de départ</p>
                <div class="text-sm font-medium text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-clock text-indigo-600"></i>
                    @php
                        $heureDepart = optional($planificationVoyage->vagueDepart)->heure_depart;
                    @endphp
                    @if($heureDepart)
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                            {{ substr((string) $heureDepart, 0, 5) }}
                        </span>
                        <span class="text-xs text-slate-500">
                            ({{ optional($planificationVoyage->vagueDepart)->nom_vague ?? '' }})
                        </span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </div>
            </div>

            {{-- PLACES --}}
            <div>
                <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Places</p>
                <div class="text-sm font-medium flex items-center gap-2 flex-wrap">
                    @php
                        $vendus    = $planificationVoyage->tickets ? $planificationVoyage->tickets->count() : 0;
                        $restantes = (int) ($planificationVoyage->places_disponibles ?? 0);
                        $total     = $planificationVoyage->bus?->capacite_totale
                                   ?? $planificationVoyage->bus?->places
                                   ?? ($restantes + $vendus);
                    @endphp
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                        {{ $restantes > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                        {{ $restantes }} / {{ $total }} place(s) dispo.
                    </span>
                    <span class="text-xs text-slate-500">
                        ({{ $vendus }} ticket(s) vendu(s))
                    </span>
                </div>
            </div>

            {{-- STATUT --}}
            <div>
                <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Statut du voyage</p>
                <div class="text-sm font-medium">
                    @php
                        $statut = $planificationVoyage->statut_voyage ?? 'Programmation';
                        $badges = [
                            'Programmation' => ['bg-blue-50 text-blue-700',        '📅'],
                            'En cours'      => ['bg-amber-50 text-amber-700',      '🚀'],
                            'Terminé'       => ['bg-emerald-50 text-emerald-700',  '✅'],
                            'Annulé'        => ['bg-red-50 text-red-700',          '❌'],
                        ];
                        [$badgeClass, $emoji] = $badges[$statut] ?? ['bg-slate-100 text-slate-700', '•'];
                    @endphp
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                        {{ $emoji }} {{ $statut }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- LISTE DES TICKETS                                            -->
    <!-- ============================================================ -->
    <div class="bg-white shadow ring-1 ring-slate-200 rounded-xl overflow-hidden">
        <div class="p-6 pb-4 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-800">
                <i class="fa-solid fa-ticket text-indigo-600 mr-2"></i>
                Tickets associés ({{ $planificationVoyage->tickets ? $planificationVoyage->tickets->count() : 0 }})
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="p-4 text-xs font-bold uppercase text-slate-500">Référence</th>
                        <th class="p-4 text-xs font-bold uppercase text-slate-500">Passager</th>
                        <th class="p-4 text-xs font-bold uppercase text-slate-500">Contact</th>
                        <th class="p-4 text-xs font-bold uppercase text-slate-500">Destination</th>
                        <th class="p-4 text-xs font-bold uppercase text-slate-500">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                @forelse($planificationVoyage->tickets ?? [] as $ticket)
                    <tr class="hover:bg-slate-50 transition">
                        {{-- RÉFÉRENCE --}}
                        <td class="p-4 text-sm font-mono font-medium text-slate-800">
                            <i class="fa-solid fa-ticket-simple text-slate-400 mr-1"></i>
                            {{ $ticket->reference_ticket ?? '—' }}
                        </td>

                        {{-- PASSAGER --}}
                        <td class="p-4 text-sm text-slate-700">
                            <div class="font-medium">
                                <i class="fa-solid fa-user text-slate-400 mr-1"></i>
                                {{ trim(($ticket->nom_passager ?? '') . ' ' . ($ticket->prenom_passager ?? '')) ?: 'N/A' }}
                            </div>
                        </td>

                        {{-- CONTACT --}}
                        <td class="p-4 text-sm text-slate-600">
                            @if($ticket->telephone_passager)
                                <i class="fa-solid fa-phone text-slate-400 mr-1"></i>
                                {{ $ticket->telephone_passager }}
                            @else
                                —
                            @endif
                        </td>

                        {{-- DESTINATION --}}
                        <td class="p-4 text-sm">
                            <span class="text-slate-700">
                                <i class="fa-solid fa-location-dot text-slate-400 mr-1"></i>
                                {{ $ticket->ville_arrivee ?? '—' }}
                            </span>
                            @if($ticket->quartier)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 ml-1">
                                    {{ $ticket->quartier }}
                                </span>
                            @endif
                        </td>

                        {{-- STATUT --}}
                        <td class="p-4 text-sm">
                            @php
                                $statutTicket = $ticket->statut_embarquement ?? 'En_Attente';
                                $statutBadges = [
                                    'Present'    => ['bg-emerald-50 text-emerald-700',  '✅'],
                                    'Absent'     => ['bg-red-50 text-red-700',          '❌'],
                                    'En_Attente' => ['bg-amber-50 text-amber-700',      '⏳'],
                                ];
                                [$badgeClass, $emoji] = $statutBadges[$statutTicket] ?? ['bg-slate-100 text-slate-700', '•'];
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                {{ $emoji }} {{ str_replace('_', ' ', $statutTicket) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-400">
                            <i class="fa-solid fa-ticket text-4xl mb-3 text-slate-300"></i>
                            <p class="text-base">Aucun ticket enregistré pour cette planification pour le moment.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection