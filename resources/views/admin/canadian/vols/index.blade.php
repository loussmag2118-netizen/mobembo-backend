@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Gestion des Vols</h1>
            <p class="text-sm text-slate-500 mt-1">
                Programmez et gérez tous les vols Canadian Airways
            </p>
        </div>
        <a href="{{ route('canadian.vols.create') }}"
           class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-[#C62828] to-[#8E0000] hover:from-[#E53935] hover:to-[#C62828] text-white font-semibold rounded-xl shadow-lg shadow-red-500/20 transition">
            <i class="fa-solid fa-plus mr-2"></i> Nouveau vol
        </a>
    </div>

    {{-- Message succès --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Cartes statistiques --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-sky-100 text-sky-600 rounded-lg">
                    <i class="fa-solid fa-plane"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total</p>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $vols->total() ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                    <i class="fa-solid fa-check"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Programmés</p>
            </div>
            <p class="text-2xl font-black text-slate-900">
                {{ $vols->where('statut', 'programme')->count() }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                    <i class="fa-solid fa-plane-departure"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">En vol</p>
            </div>
            <p class="text-2xl font-black text-slate-900">
                {{ $vols->where('statut', 'en_vol')->count() }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-red-100 text-[#C62828] rounded-lg">
                    <i class="fa-solid fa-xmark"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Annulés</p>
            </div>
            <p class="text-2xl font-black text-slate-900">
                {{ $vols->where('statut', 'annule')->count() }}
            </p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <form method="GET" action="{{ route('canadian.vols.index') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="N° vol, aéroport..."
                       class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#C62828] focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Statut</label>
                <select name="statut"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                    <option value="">Tous</option>
                    <option value="programme" {{ request('statut') == 'programme' ? 'selected' : '' }}>Programmé</option>
                    <option value="en_vol"    {{ request('statut') == 'en_vol'    ? 'selected' : '' }}>En vol</option>
                    <option value="atterri"   {{ request('statut') == 'atterri'   ? 'selected' : '' }}>Atterri</option>
                    <option value="annule"    {{ request('statut') == 'annule'    ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 bg-[#C62828] hover:bg-[#8E0000] text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filtrer
                </button>
                <a href="{{ route('canadian.vols.index') }}"
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
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">N° Vol</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Trajet</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Départ</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Prix</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Places</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Statut</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($vols as $vol)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <span class="font-mono text-xs font-bold text-[#C62828] bg-red-50 px-2 py-1 rounded">
                                {{ $vol->numero_vol }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900 text-sm">
                                {{ $vol->origine }} → {{ $vol->destination }}
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $vol->type_vol === 'escale' ? 'Avec escale' : 'Direct' }}
                            </div>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-600">
                            <div>{{ $vol->date_depart ? $vol->date_depart->format('d/m/Y') : '—' }}</div>
                            <div class="font-semibold">{{ $vol->date_depart ? $vol->date_depart->format('H:i') : '' }}</div>
                        </td>
                        <td class="px-5 py-4 font-semibold text-slate-900">
                            {{ number_format($vol->prix ?? 0, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                {{ ($vol->places_disponibles ?? 0) > 10 ? 'bg-emerald-50 text-emerald-700' : (($vol->places_disponibles ?? 0) > 0 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-[#C62828]') }}">
                                {{ $vol->places_disponibles ?? 0 }} places
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $badges = [
                                    'programme' => 'bg-emerald-50 text-emerald-700',
                                    'en_vol'    => 'bg-sky-50 text-sky-700',
                                    'atterri'   => 'bg-slate-100 text-slate-700',
                                    'annule'    => 'bg-red-50 text-[#C62828]',
                                ];
                                $labels = [
                                    'programme' => 'Programmé',
                                    'en_vol'    => 'En vol',
                                    'atterri'   => 'Atterri',
                                    'annule'    => 'Annulé',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badges[$vol->statut] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $labels[$vol->statut] ?? $vol->statut }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ route('canadian.vols.show', $vol->id) }}"
                                   class="text-slate-500 hover:text-[#C62828] transition" title="Voir">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('canadian.vols.edit', $vol->id) }}"
                                   class="text-sky-600 hover:text-sky-800 transition" title="Modifier">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('canadian.vols.dupliquer', $vol->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-violet-600 hover:text-violet-800 transition" title="Dupliquer">
                                        <i class="fa-solid fa-copy"></i>
                                    </button>
                                </form>
                                <form action="{{ route('canadian.vols.destroy', $vol->id) }}" method="POST"
                                      class="inline" onsubmit="return confirm('Supprimer ce vol ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[#C62828] hover:text-[#8E0000] transition" title="Supprimer">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-4">
                                <i class="fa-solid fa-plane text-2xl text-slate-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700 mb-2">Aucun vol</h3>
                            <p class="text-sm text-slate-500 mb-4">
                                Programmez votre premier vol Canadian Airways
                            </p>
                            <a href="{{ route('canadian.vols.create') }}"
                               class="inline-flex items-center px-4 py-2 bg-[#C62828] hover:bg-[#8E0000] text-white text-sm font-semibold rounded-lg transition">
                                <i class="fa-solid fa-plus mr-2"></i> Nouveau vol
                            </a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($vols) && method_exists($vols, 'hasPages') && $vols->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                {{ $vols->links() }}
            </div>
        @endif
    </div>
</div>
@endsection