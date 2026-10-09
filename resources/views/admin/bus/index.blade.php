@extends('layouts.admin')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-slate-800">
        Gestion des Bus
    </h1>

    <div class="flex gap-3">
        <a href="{{ route('admin.bus.trashed') }}"
           class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
            <i class="fa-solid fa-trash-can mr-1"></i> Corbeille
        </a>

        <a href="{{ route('admin.bus.create') }}"
           class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Ajouter un bus
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-5 p-4 rounded-lg bg-green-50 border border-green-300 text-green-700 text-sm">
        <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-300 text-red-700 text-sm">
        <i class="fa-solid fa-circle-exclamation mr-2"></i>{{ session('error') }}
    </div>
@endif

<div class="bg-white shadow ring-1 ring-slate-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Immatriculation</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Modèle</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Capacité</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Statut</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-200">
            @forelse($bus as $b)
                <tr class="hover:bg-slate-50">
                    {{-- IMMATRICULATION --}}
                    <td class="p-4 text-sm font-medium text-slate-800">
                        <i class="fa-solid fa-id-card text-slate-400 mr-2"></i>
                        {{ $b->immatriculation }}
                    </td>

                    {{-- MODÈLE --}}
                    <td class="p-4 text-sm text-slate-600">
                        {{ $b->modele }}
                    </td>

                    {{-- CAPACITÉ --}}
                    <td class="p-4 text-sm text-slate-600">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                            <i class="fa-solid fa-chair mr-1"></i>
                            {{ $b->capacite_totale }} places
                        </span>
                    </td>

                    {{-- STATUT --}}
                    <td class="p-4 text-sm">
                        @php
                            $statut = $b->statut ?? 'Disponible';
                            $badges = [
                                'Disponible'   => ['bg-emerald-50 text-emerald-700', '✅'],
                                'Maintenance'  => ['bg-amber-50 text-amber-700',    '🔧'],
                                'Indisponible' => ['bg-red-50 text-red-700',        '❌'],
                            ];
                            [$badgeClass, $emoji] = $badges[$statut] ?? ['bg-slate-100 text-slate-700', '•'];
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                            {{ $emoji }} {{ $statut }}
                        </span>
                    </td>

                    {{-- ACTIONS --}}
                    <td class="p-4">
                        <div class="flex justify-end gap-3">
                            <a href="{{ route('admin.bus.edit', $b->id) }}"
                               class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Modifier
                            </a>

                            <form action="{{ route('admin.bus.destroy', $b->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Voulez-vous envoyer ce bus dans la corbeille ?')">
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
                    <td colspan="5" class="p-8 text-center text-slate-400">
                        <i class="fa-solid fa-bus text-3xl mb-2 text-slate-300"></i>
                        <p>Aucun bus enregistré.</p>
                        <a href="{{ route('admin.bus.create') }}"
                           class="inline-block mt-4 bg-slate-900 hover:bg-slate-800 text-white px-5 py-2 rounded-lg text-sm font-semibold">
                            + Ajouter un premier bus
                        </a>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if(method_exists($bus, 'links'))
        <div class="p-4 border-t border-slate-200">
            {{ $bus->links() }}
        </div>
    @endif
</div>

@endsection