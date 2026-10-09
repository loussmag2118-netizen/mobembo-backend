@extends('layouts.admin')

@section('content')

{{-- ============================================================ --}}
{{-- EN-TÊTE                                                      --}}
{{-- ============================================================ --}}
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">
            Gestion des gares
        </h1>
        <p class="text-slate-500 text-sm mt-1">
            {{ $gares->count() }} gare(s) enregistrée(s) pour votre agence
        </p>
    </div>

    <div class="flex gap-3 mt-4 sm:mt-0">
        <a href="{{ route('admin.gares.trashed') }}"
           class="inline-flex items-center rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 text-sm font-medium">
            <i class="fa-solid fa-trash-can mr-1"></i> Corbeille
        </a>

        <a href="{{ route('admin.gares.create') }}"
           class="inline-flex items-center rounded-lg bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-sm font-semibold transition">
            + Ajouter une gare
        </a>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MESSAGES FLASH                                              --}}
{{-- ============================================================ --}}
@if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg text-sm">
        <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r-lg text-sm">
        <i class="fa-solid fa-circle-exclamation mr-2"></i>{{ session('error') }}
    </div>
@endif

{{-- ============================================================ --}}
{{-- TABLEAU                                                      --}}
{{-- ============================================================ --}}
<div class="bg-white shadow ring-1 ring-slate-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Nom de la gare</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Ville</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-200">
            @forelse($gares as $g)
                <tr class="hover:bg-slate-50 transition">

                    {{-- NOM DE LA GARE --}}
                    <td class="p-4">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-indigo-500"></i>
                            <span class="font-semibold text-slate-900">
                                {{ $g->nom_gare }}
                            </span>
                        </div>
                    </td>

                    {{-- VILLE --}}
                    <td class="p-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                            <i class="fa-solid fa-city mr-1"></i>
                            {{ $g->ville }}
                        </span>
                    </td>

                    {{-- ACTIONS --}}
                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-4">
                            <a href="{{ route('admin.gares.edit', $g->id) }}"
                               class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Modifier
                            </a>

                            <form action="{{ route('admin.gares.destroy', $g->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Voulez-vous envoyer cette gare dans la corbeille ?')">
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
                    <td colspan="3" class="p-12 text-center text-slate-400">
                        <i class="fa-solid fa-location-dot text-4xl mb-3 text-slate-300"></i>
                        <p class="text-base">Aucune gare enregistrée pour le moment.</p>
                        <a href="{{ route('admin.gares.create') }}"
                           class="inline-block mt-4 bg-slate-900 hover:bg-slate-800 text-white px-5 py-2 rounded-lg text-sm font-semibold">
                            + Créer votre première gare
                        </a>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if(method_exists($gares, 'links'))
        <div class="p-4 border-t border-slate-200">
            {{ $gares->links() }}
        </div>
    @endif
</div>

@endsection