@extends('layouts.admin')

@section('title', 'Aéroports')
@section('header_icon', 'fa-plane-arrival')
@section('header_title', 'Aéroports')
@section('header_subtitle', 'Référentiel commun aux compagnies aériennes de la plateforme')

@section('content')
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="p-5 sm:px-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" class="relative sm:w-72">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Ville, code IATA ou pays…" class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-[#124231] focus:outline-none">
        </form>
        <a href="{{ route('canadian.villes.create') }}" class="inline-flex items-center justify-center gap-2 bg-[#124231] hover:bg-[#0c2f23] text-white text-sm font-bold py-2.5 px-5 rounded-xl"><i class="fa-solid fa-plus"></i> Ajouter un aéroport</a>
    </div>
    @if($villes->isEmpty())
        @include('admin.canadian.partials.vide', ['icone' => 'fa-plane-arrival', 'titre' => 'Aucun aéroport', 'texte' => 'Ajoutez les aéroports que vous desservez (ex. Brazzaville BZV, Pointe-Noire PNR).', 'lien' => ['Ajouter un aéroport', route('canadian.villes.create')]])
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-6">Code</th><th class="py-3 px-4">Ville</th><th class="py-3 px-4">Pays</th><th class="py-3 px-4">État</th><th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($villes as $v)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3.5 px-6 font-mono font-black text-lg text-slate-900">{{ $v->code_iata }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $v->nom }}</td>
                            <td class="py-3.5 px-4">{{ $v->drapeau }} {{ $v->pays }}</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $v->actif ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $v->actif ? 'Actif' : 'Désactivé' }}</span></td>
                            <td class="py-3.5 px-6">
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('canadian.villes.edit', $v->id) }}" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center"><i class="fa-solid fa-pen text-xs"></i></a>
                                    <form method="POST" action="{{ route('canadian.villes.destroy', $v->id) }}" onsubmit="return confirm('Supprimer l\'aéroport {{ $v->code_iata }} ?')">@csrf @method('DELETE')
                                        <button class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-trash text-xs"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($villes->hasPages())<div class="px-6 py-4 border-t border-slate-100">{{ $villes->links() }}</div>@endif
    @endif
</div>
@endsection
