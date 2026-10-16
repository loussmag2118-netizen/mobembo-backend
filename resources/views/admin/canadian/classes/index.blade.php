@extends('layouts.admin')

@section('title', 'Classes tarifaires')
@section('header_icon', 'fa-layer-group')
@section('header_title', 'Classes tarifaires')
@section('header_subtitle', 'Prix d\'une classe = prix du vol × coefficient (ex. Affaires × 2,5)')

@section('content')
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="p-5 sm:px-6 border-b border-slate-100 flex items-center justify-between gap-3">
        <h2 class="text-base font-black text-slate-900">Classes ({{ $classes->total() }})</h2>
        <a href="{{ route('canadian.classes.create') }}" class="inline-flex items-center gap-2 bg-[#124231] hover:bg-[#0c2f23] text-white text-sm font-bold py-2.5 px-5 rounded-xl"><i class="fa-solid fa-plus"></i> Nouvelle classe</a>
    </div>
    @if($classes->isEmpty())
        @include('admin.canadian.partials.vide', ['icone' => 'fa-layer-group', 'titre' => 'Aucune classe', 'texte' => 'Créez au moins la classe Économique (coefficient 1).', 'lien' => ['Créer une classe', route('canadian.classes.create')]])
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-6">Classe</th><th class="py-3 px-4">Coefficient</th><th class="py-3 px-4">Exemple (vol à 100 000 F)</th><th class="py-3 px-4">État</th><th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($classes as $c)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3.5 px-6"><p class="font-bold text-slate-900">{{ $c->nom }}</p><p class="text-xs text-slate-400 font-mono">{{ $c->code }}{{ $c->description ? ' · ' . \Illuminate\Support\Str::limit($c->description, 60) : '' }}</p></td>
                            <td class="py-3.5 px-4 font-bold">× {{ rtrim(rtrim(number_format($c->multiplicateur, 2, ',', ''), '0'), ',') }}</td>
                            <td class="py-3.5 px-4">{{ number_format(100000 * $c->multiplicateur, 0, ',', ' ') }} F</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $c->actif ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $c->actif ? 'Proposée' : 'Masquée' }}</span></td>
                            <td class="py-3.5 px-6">
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('canadian.classes.edit', $c->id) }}" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center"><i class="fa-solid fa-pen text-xs"></i></a>
                                    <form method="POST" action="{{ route('canadian.classes.destroy', $c->id) }}" onsubmit="return confirm('Supprimer la classe {{ $c->nom }} ?')">@csrf @method('DELETE')
                                        <button class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-trash text-xs"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($classes->hasPages())<div class="px-6 py-4 border-t border-slate-100">{{ $classes->links() }}</div>@endif
    @endif
</div>
@endsection
