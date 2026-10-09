@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Gestion des Réservations</h1>
            <p class="text-sm text-slate-500 mt-1">
                Consultez et gérez toutes les réservations de billets
            </p>
        </div>
    </div>

    {{-- Messages --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Cartes statistiques --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-sky-100 text-sky-600 rounded-lg">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total</p>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $total ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                    <i class="fa-solid fa-check"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Confirmées</p>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $confirmees ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">En attente</p>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $en_attente ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-red-100 text-[#C62828] rounded-lg">
                    <i class="fa-solid fa-xmark"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Annulées</p>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $annulees ?? 0 }}</p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <form method="GET" action="{{ route('canadian.reservations.index') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Référence, client, numéro vol..."
                       class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#C62828] focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Statut</label>
                <select name="statut"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#C62828] focus:outline-none bg-white">
                    <option value="">Tous</option>
                    <option value="Confirmee"  {{ request('statut') == 'Confirmee'  ? 'selected' : '' }}>Confirmée</option>
                    <option value="En_Attente" {{ request('statut') == 'En_Attente' ? 'selected' : '' }}>En attente</option>
                    <option value="Annulee"    {{ request('statut') == 'Annulee'    ? 'selected' : '' }}>Annulée</option>
                    <option value="Remboursee" {{ request('statut') == 'Remboursee' ? 'selected' : '' }}>Remboursée</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 bg-[#C62828] hover:bg-[#8E0000] text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filtrer
                </button>
                <a href="{{ route('canadian.reservations.index') }}"
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
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Référence</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Client</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Vol</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Places</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Montant</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Statut</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($reservations as $resa)
                    <tr class="hover:bg-slate-50/50 transition">
                        {{-- Référence --}}
                        <td class="px-5 py-4">
                            <span class="font-mono text-xs font-bold text-[#C62828] bg-red-50 px-2 py-1 rounded">
                                {{ $resa->reference ?? 'RES-' . str_pad($resa->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>

                        {{-- Client --}}
                        <td class="px-5 py-4">
                            @if($resa->canadianUser)
                                <div class="font-semibold text-slate-900">
                                    {{ $resa->canadianUser->prenom }} {{ $resa->canadianUser->nom }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $resa->canadianUser->telephone ?? '' }}
                                </div>
                            @elseif($resa->utilisateur)
                                <div class="font-semibold text-slate-900">
                                    {{ $resa->utilisateur->nom ?? '' }} {{ $resa->utilisateur->prenom ?? '' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $resa->utilisateur->telephone ?? '' }}
                                </div>
                            @else
                                <span class="text-xs text-slate-400">Client inconnu</span>
                            @endif
                        </td>

                        {{-- Vol --}}
                        <td class="px-5 py-4">
                            @if($resa->vol)
                                <div class="font-mono font-semibold text-slate-900">
                                    {{ $resa->vol->numero_vol }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $resa->vol->ville_depart ?? $resa->vol->origine }}
                                    → {{ $resa->vol->ville_arrivee ?? $resa->vol->destination }}
                                </div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        {{-- Places --}}
                        <td class="px-5 py-4">
                            <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs font-semibold">
                                {{ $resa->nombre_places ?? 1 }} place(s)
                            </span>
                        </td>

                        {{-- Montant --}}
                        <td class="px-5 py-4 font-semibold text-slate-900">
                            {{ number_format($resa->montant_total ?? 0, 0, ',', ' ') }} FCFA
                        </td>

                        {{-- Statut --}}
                        <td class="px-5 py-4">
                            @php
                                $badges = [
                                    'Confirmee'  => 'bg-emerald-50 text-emerald-700',
                                    'En_Attente' => 'bg-amber-50 text-amber-700',
                                    'Annulee'    => 'bg-red-50 text-[#C62828]',
                                    'Remboursee' => 'bg-slate-100 text-slate-700',
                                ];
                                $statut = $resa->statut ?? 'En_Attente';
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badges[$statut] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ str_replace('_', ' ', $statut) }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4">
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ route('canadian.reservations.show', $resa->id) }}"
                                   class="text-slate-500 hover:text-[#C62828] transition" title="Voir">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @if(in_array($resa->statut, ['Confirmee', 'En_Attente']))
                                    <form action="{{ route('canadian.reservations.annuler', $resa->id) }}" method="POST"
                                          class="inline" onsubmit="return confirm('Annuler cette réservation ?')">
                                        @csrf
                                        <button type="submit" class="text-amber-600 hover:text-amber-800 transition" title="Annuler">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($resa->statut === 'Confirmee')
                                    <form action="{{ route('canadian.reservations.rembourser', $resa->id) }}" method="POST"
                                          class="inline" onsubmit="return confirm('Rembourser cette réservation ?')">
                                        @csrf
                                        <button type="submit" class="text-[#C62828] hover:text-[#8E0000] transition" title="Rembourser">
                                            <i class="fa-solid fa-money-bill-wave"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-4">
                                <i class="fa-solid fa-passport text-2xl text-slate-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700 mb-2">Aucune réservation</h3>
                            <p class="text-sm text-slate-500">
                                Les réservations apparaîtront ici dès qu'elles seront créées.
                            </p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($reservations) && method_exists($reservations, 'hasPages') && $reservations->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection