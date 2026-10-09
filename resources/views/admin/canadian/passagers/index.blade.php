@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Passagers</h1>
            <p class="text-sm text-slate-500 mt-1">
                Tous les passagers ayant voyagé avec Canadian Airways
            </p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <form method="GET" action="{{ route('canadian.passagers.index') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Nom, prénom, passeport..."
                       class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#C62828] focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Type</label>
                <select name="type"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                    <option value="">Tous</option>
                    <option value="adulte" {{ request('type') === 'adulte' ? 'selected' : '' }}>Adulte</option>
                    <option value="enfant" {{ request('type') === 'enfant' ? 'selected' : '' }}>Enfant</option>
                    <option value="bebe"   {{ request('type') === 'bebe'   ? 'selected' : '' }}>Bébé</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 bg-[#C62828] hover:bg-[#8E0000] text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filtrer
                </button>
                <a href="{{ route('canadian.passagers.index') }}"
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
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Passager</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Type</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Passeport</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Nationalité</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Vol</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($passagers as $passager)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">
                                {{ $passager->prenom }} {{ $passager->nom }}
                            </div>
                            @if($passager->date_naissance)
                                <div class="text-xs text-slate-500">
                                    {{ \Carbon\Carbon::parse($passager->date_naissance)->format('d/m/Y') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $types = [
                                    'adulte' => ['label' => 'Adulte', 'bg' => 'bg-sky-50 text-sky-700'],
                                    'enfant' => ['label' => 'Enfant', 'bg' => 'bg-amber-50 text-amber-700'],
                                    'bebe'   => ['label' => 'Bébé',   'bg' => 'bg-pink-50 text-pink-700'],
                                ];
                                $t = $types[$passager->type] ?? $types['adulte'];
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $t['bg'] }}">
                                {{ $t['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-600 font-mono text-xs">
                            {{ $passager->numero_passeport ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-slate-600">
                            {{ $passager->nationalite ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-600">
                            @if($passager->reservation && $passager->reservation->vol)
                                <div class="font-mono font-semibold text-[#C62828]">
                                    {{ $passager->reservation->vol->numero_vol }}
                                </div>
                                <div>
                                    {{ $passager->reservation->vol->ville_depart }}
                                    → {{ $passager->reservation->vol->ville_arrivee }}
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end">
                                <a href="{{ route('canadian.passagers.show', $passager->id) }}"
                                   class="text-slate-500 hover:text-[#C62828] transition" title="Voir">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-4">
                                <i class="fa-solid fa-users text-2xl text-slate-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700 mb-2">Aucun passager</h3>
                            <p class="text-sm text-slate-500">
                                Les passagers apparaîtront ici dès qu'une réservation sera effectuée.
                            </p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($passagers->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                {{ $passagers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection