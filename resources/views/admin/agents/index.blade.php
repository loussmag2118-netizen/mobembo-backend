@extends('layouts.admin')

@section('content')

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Gestion des agents
        </h1>
        <p class="text-slate-500 text-sm mt-1">
            {{ $agents->count() }} agent(s) dans votre agence
        </p>
    </div>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.agents.trashed') }}"
           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all shadow-sm">
            🗑 Corbeille
        </a>

        <a href="{{ route('admin.agents.create') }}"
           class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all shadow-sm">
            + Ajouter un agent
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm shadow-sm">
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-100 text-slate-600 text-xs uppercase font-semibold">
                <tr>
                    <th class="p-4">Agent</th>
                    <th class="p-4">Gare / Station</th>
                    <th class="p-4">Téléphone</th>
                    <th class="p-4">Code d'accès</th>
                    <th class="p-4 text-center">Statut</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">

            @forelse($agents as $agent)
                <tr class="hover:bg-slate-50/50 transition-colors">

                    {{-- Agent (nom + prénom + avatar) --}}
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr($agent->nom ?? '?', 0, 1)) }}{{ strtoupper(substr($agent->prenom ?? '', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-medium text-slate-900">
                                    {{ $agent->nom }} {{ $agent->prenom }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    Créé le {{ $agent->created_at ? $agent->created_at->format('d/m/Y') : '—' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Gare --}}
                    <td class="p-4">
                        @if($agent->gare)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                🏢 {{ $agent->gare->nom_gare }}
                            </span>
                        @else
                            <span class="text-slate-400 text-xs italic">Aucune gare</span>
                        @endif
                    </td>

                    {{-- Téléphone --}}
                    <td class="p-4 text-slate-600 font-mono text-xs">
                        {{ $agent->telephone }}
                    </td>

                    {{-- Code d'accès --}}
                    <td class="p-4 text-slate-600 font-mono text-xs">
                        @if($agent->code_acces_agent)
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                {{ $agent->code_acces_agent }}
                            </span>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>

                    {{-- Statut --}}
                    <td class="p-4 text-center">
                        @if($agent->statut == 'actif')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                ✅ Actif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                ⛔ Suspendu
                            </span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="p-4">
                        <div class="flex justify-end items-center gap-3">
                            <a href="{{ route('admin.agents.historique', $agent) }}"
                               class="text-emerald-600 hover:text-emerald-700 hover:underline font-medium">
                                Historique
                            </a>

                            <a href="{{ route('admin.agents.edit', $agent) }}"
                               class="text-blue-600 hover:text-blue-700 hover:underline font-medium">
                                Modifier
                            </a>

                            <form action="{{ route('admin.agents.destroy', $agent) }}"
                                  method="POST"
                                  onsubmit="return confirm('Voulez-vous vraiment supprimer cet agent ?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="text-red-600 hover:text-red-700 hover:underline font-medium cursor-pointer">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center p-8 text-slate-500">
                        <i class="fa-solid fa-user-slash text-3xl mb-2 text-slate-300"></i>
                        <p>Aucun agent enregistré pour le moment.</p>
                        <a href="{{ route('admin.agents.create') }}"
                           class="inline-block mt-3 text-emerald-600 hover:underline font-medium text-sm">
                            + Ajouter votre premier agent
                        </a>
                    </td>
                </tr>
            @endforelse

            </tbody>
        </table>
    </div>
</div>

@endsection
