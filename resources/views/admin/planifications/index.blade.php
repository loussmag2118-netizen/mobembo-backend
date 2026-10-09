@extends('layouts.admin')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-slate-800">
        Gestion des Planifications de Voyages
    </h1>

    <div class="flex gap-3">
        <a href="{{ route('admin.planifications.create') }}"
           class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Planifier un voyage
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-5 p-4 rounded-lg bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm">
        <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-5 p-4 rounded-lg bg-red-50 border-l-4 border-red-500 text-red-700 text-sm">
        <i class="fa-solid fa-circle-exclamation mr-2"></i>{{ session('error') }}
    </div>
@endif

<div class="bg-white shadow ring-1 ring-slate-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Trajet</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Bus</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Horaire</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Date</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Tickets / Places</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Statut</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
            @forelse($planifications as $p)
                <tr class="hover:bg-slate-50 transition">

                    {{-- TRAJET --}}
                    <td class="p-4 text-sm font-medium text-slate-800">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-solid fa-route text-slate-400 text-xs"></i>
                            <span>
                                {{ $p->trajet->gareDepart->nom_gare ?? '?' }}
                                <span class="text-xs text-slate-500 font-normal">
                                    ({{ $p->trajet->gareDepart->ville ?? '?' }})
                                </span>
                            </span>
                        </div>
                        <div class="text-slate-400 text-xs ml-5">↓</div>
                        <div class="flex items-center gap-1.5">
                            <i class="fa-solid fa-location-dot text-slate-400 text-xs"></i>
                            <span>
                                {{ $p->trajet->gareArrivee->nom_gare ?? '?' }}
                                <span class="text-xs text-slate-500 font-normal">
                                    ({{ $p->trajet->gareArrivee->ville ?? '?' }})
                                </span>
                            </span>
                        </div>
                    </td>

                    {{-- BUS --}}
                    <td class="p-4 text-sm text-slate-600">
                        @if($p->bus)
                            @php
                                $statutBus = $p->bus->statut ?? 'Disponible';
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
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                {{ $emoji }} {{ $p->bus->immatriculation ?? $p->bus->numero ?? 'Bus #'.$p->bus->id }}
                            </span>
                            @if($p->bus->modele)
                                <div class="text-xs text-slate-400 mt-1">{{ $p->bus->modele }}</div>
                            @endif
                        @else
                            <span class="text-slate-400 text-xs">—</span>
                        @endif
                    </td>

                    {{-- HORAIRE --}}
                    <td class="p-4 text-sm text-slate-600">
                        @php
                            $heureDepart = optional($p->vagueDepart)->heure_depart;
                        @endphp
                        @if($heureDepart)
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                <i class="fa-solid fa-clock mr-1"></i>
                                {{ substr((string) $heureDepart, 0, 5) }}
                            </span>
                        @else
                            <span class="text-slate-400 text-xs">—</span>
                        @endif
                    </td>

                    {{-- DATE --}}
                    <td class="p-4 text-sm text-slate-600">
                        @if($p->date_voyage)
                            <i class="fa-solid fa-calendar text-slate-400 mr-1"></i>
                            {{ \Carbon\Carbon::parse($p->date_voyage)->format('d/m/Y') }}
                        @else
                            <span class="text-slate-400 text-xs">—</span>
                        @endif
                    </td>

                    {{-- TICKETS / PLACES & ALERTE RUPTURE DE STOCK --}}
                    <td class="p-4 text-sm">
                        @php
                            $vendus    = $p->tickets ? $p->tickets->count() : 0;
                            $restantes = (int) ($p->places_disponibles ?? 0);
                            $total     = $restantes + $vendus;
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $restantes > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                            {{ $vendus }} / {{ $total }}
                        </span>
                        <div class="text-xs text-slate-400 mt-1">
                            {{ $restantes }} place(s) restante(s)
                        </div>

                        {{-- Message de rupture de stock --}}
                        @if($restantes <= 0)
                            <div class="mt-2 p-1.5 bg-red-50 border border-red-200 rounded text-[11px] font-semibold text-red-700 flex items-center gap-1">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span>Rupture de stock (complet)</span>
                            </div>
                        @endif
                    </td>

                    {{-- STATUT --}}
                    <td class="p-4 text-sm">
                        @php
                            $statut = $p->statut_voyage ?? 'Programmation';
                            $badges = [
                                'Programmation' => ['bg-blue-50 text-blue-700',     '📅'],
                                'En cours'      => ['bg-amber-50 text-amber-700',   '🚀'],
                                'Terminé'       => ['bg-emerald-50 text-emerald-700','✅'],
                                'Annulé'        => ['bg-red-50 text-red-700',       '❌'],
                            ];
                            [$badgeClass, $emoji] = $badges[$statut] ?? ['bg-slate-100 text-slate-700', '•'];
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                            {{ $emoji }} {{ $statut }}
                        </span>
                    </td>

                    {{-- ACTIONS --}}
                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-3">
                            <a href="{{ route('admin.planifications.show', $p->id) }}"
                               class="text-slate-600 hover:text-slate-900 text-sm font-medium">
                                <i class="fa-solid fa-eye mr-1"></i> Détails
                            </a>
                            <a href="{{ route('admin.planifications.edit', $p->id) }}"
                               class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Modifier
                            </a>
                            <form action="{{ route('admin.planifications.destroy', $p->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Voulez-vous supprimer cette planification ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 hover:text-red-900 text-sm font-medium">
                                    <i class="fa-solid fa-trash-can mr-1"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-12 text-center text-slate-400">
                        <i class="fa-solid fa-calendar-xmark text-4xl mb-3 text-slate-300"></i>
                        <p class="text-base">Aucune planification de voyage enregistrée.</p>
                        <a href="{{ route('admin.planifications.create') }}"
                           class="inline-block mt-4 bg-slate-900 hover:bg-slate-800 text-white px-5 py-2 rounded-lg text-sm font-semibold">
                            + Planifier un premier voyage
                        </a>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if(method_exists($planifications, 'links'))
        <div class="p-4 border-t border-slate-200">
            {{ $planifications->links() }}
        </div>
    @endif
</div>

@endsection
