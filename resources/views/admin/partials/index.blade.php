@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- En-tête avec titre et bouton d'ajout -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Gestion des éléments</h1>
            <p class="text-sm text-slate-500">Liste et administration de tous les enregistrements.</p>
        </div>
        <a href="{{ route('admin.trajets.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
            <i class="fa-solid fa-plus mr-2"></i> Ajouter un élément
        </a>
    </div>

    <!-- Message de succès -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm font-medium">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Tableau des données -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Nom</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-sm">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">#{{ $item->id }}</td>
                            <td class="px-6 py-4 text-slate-900 font-medium">{{ $item->nom }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.trajets.edit', $item->id) }}" class="inline-flex items-center px-3 py-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 font-medium rounded-md transition text-xs">
                                    <i class="fa-solid fa-pen-to-square mr-1.5"></i> Modifier
                                </a>
                                <form action="{{ route('admin.trajets.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 hover:bg-red-100 font-medium rounded-md transition text-xs">
                                        <i class="fa-solid fa-trash mr-1.5"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                                <p class="text-sm">Aucun enregistrement trouvé.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
