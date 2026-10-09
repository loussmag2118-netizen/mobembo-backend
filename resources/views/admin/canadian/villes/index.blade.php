@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Villes & Codes IATA</h1>
            <p class="text-sm text-slate-500 mt-1">
                Gérez les villes desservies par Canadian Airways
            </p>
        </div>
        <a href="{{ route('canadian.villes.create') }}"
           class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-[#C62828] to-[#8E0000] hover:from-[#E53935] hover:to-[#C62828] text-white font-semibold rounded-xl shadow-lg shadow-red-500/20 transition">
            <i class="fa-solid fa-plus mr-2"></i> Ajouter une ville
        </a>
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

    {{-- Tableau --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Code IATA</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Ville</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Pays</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Statut</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($villes as $ville)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <span class="font-mono font-black text-lg text-[#C62828] bg-red-50 px-3 py-1.5 rounded-lg">
                                {{ $ville->code_iata }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                @if($ville->drapeau)
                                    <span class="text-lg">{{ $ville->drapeau }}</span>
                                @endif
                                <span class="font-semibold text-slate-900">{{ $ville->nom }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-slate-600">
                            {{ $ville->pays ?? '—' }}
                        </td>
                        <td class="px-5 py-4">
                            @if($ville->actif ?? false)
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block mr-1"></span>
                                    Active
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ route('canadian.villes.edit', $ville->id) }}"
                                   class="text-sky-600 hover:text-sky-800 transition" title="Modifier">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('canadian.villes.destroy', $ville->id) }}" method="POST"
                                      class="inline" onsubmit="return confirm('Supprimer cette ville ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 transition" title="Supprimer">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-4">
                                <i class="fa-solid fa-city text-2xl text-slate-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700 mb-2">Aucune ville</h3>
                            <p class="text-sm text-slate-500 mb-4">
                                Ajoutez votre première ville avec son code IATA
                            </p>
                            <a href="{{ route('canadian.villes.create') }}"
                               class="inline-flex items-center px-4 py-2 bg-[#C62828] hover:bg-[#8E0000] text-white text-sm font-semibold rounded-lg transition">
                                <i class="fa-solid fa-plus mr-2"></i> Ajouter une ville
                            </a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($villes->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                {{ $villes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection