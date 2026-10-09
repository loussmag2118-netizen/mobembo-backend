@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Extras & Services</h1>
            <p class="text-sm text-slate-500 mt-1">
                Bagages, sièges, assurance et autres services additionnels
            </p>
        </div>
        <a href="{{ route('canadian.extras.create') }}"
           class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-[#C62828] to-[#8E0000] hover:from-[#E53935] hover:to-[#C62828] text-white font-semibold rounded-xl shadow-lg shadow-red-500/20 transition">
            <i class="fa-solid fa-plus mr-2"></i> Ajouter un extra
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
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Icône</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Nom</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Catégorie</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Prix</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Statut</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($extras as $extra)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <div class="w-10 h-10 bg-red-50 text-[#C62828] rounded-lg flex items-center justify-center">
                                <i class="fa-solid {{ $extra->icone ?? 'fa-box' }}"></i>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $extra->nom }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $extra->code }}</div>
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $cats = [
                                    'bagage'    => ['label' => 'Bagage',    'bg' => 'bg-sky-50 text-sky-700'],
                                    'siege'     => ['label' => 'Siège',     'bg' => 'bg-purple-50 text-purple-700'],
                                    'assurance' => ['label' => 'Assurance', 'bg' => 'bg-emerald-50 text-emerald-700'],
                                    'repas'     => ['label' => 'Repas',     'bg' => 'bg-amber-50 text-amber-700'],
                                    'wifi'      => ['label' => 'Wifi',      'bg' => 'bg-indigo-50 text-indigo-700'],
                                    'autre'     => ['label' => 'Autre',     'bg' => 'bg-slate-100 text-slate-700'],
                                ];
                                $c = $cats[$extra->categorie] ?? $cats['autre'];
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $c['bg'] }}">
                                {{ $c['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 font-bold text-slate-900">
                            {{ number_format($extra->prix, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-5 py-4">
                            @if($extra->actif ?? false)
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block mr-1"></span>
                                    Actif
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    Inactif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ route('canadian.extras.edit', $extra->id) }}"
                                   class="text-sky-600 hover:text-sky-800 transition" title="Modifier">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('canadian.extras.destroy', $extra->id) }}" method="POST"
                                      class="inline" onsubmit="return confirm('Supprimer cet extra ?')">
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
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-4">
                                <i class="fa-solid fa-box text-2xl text-slate-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700 mb-2">Aucun extra</h3>
                            <p class="text-sm text-slate-500 mb-4">
                                Ajoutez vos premiers services additionnels
                            </p>
                            <a href="{{ route('canadian.extras.create') }}"
                               class="inline-flex items-center px-4 py-2 bg-[#C62828] hover:bg-[#8E0000] text-white text-sm font-semibold rounded-lg transition">
                                <i class="fa-solid fa-plus mr-2"></i> Ajouter un extra
                            </a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($extras->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                {{ $extras->links() }}
            </div>
        @endif
    </div>
</div>
@endsection