@extends('layouts.admin')

@section('title', 'Bagages et options')
@section('header_icon', 'fa-suitcase-rolling')
@section('header_title', 'Bagages et options')
@section('header_subtitle', 'Services payants proposés avec le billet')

@section('content')
@php $categories = \App\Http\Controllers\Admin\Canadian\ExtraController::CATEGORIES; @endphp
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="p-5 sm:px-6 border-b border-slate-100 flex items-center justify-between gap-3">
        <h2 class="text-base font-black text-slate-900">Options ({{ $extras->total() }})</h2>
        <a href="{{ route('canadian.extras.create') }}" class="inline-flex items-center gap-2 bg-[#124231] hover:bg-[#0c2f23] text-white text-sm font-bold py-2.5 px-5 rounded-xl"><i class="fa-solid fa-plus"></i> Nouvelle option</a>
    </div>
    @if($extras->isEmpty())
        @include('admin.canadian.partials.vide', ['icone' => 'fa-suitcase-rolling', 'titre' => 'Aucune option', 'texte' => 'Ajoutez par exemple un bagage en soute de 23 kg.', 'lien' => ['Ajouter une option', route('canadian.extras.create')]])
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-6">Option</th><th class="py-3 px-4">Catégorie</th><th class="py-3 px-4">Prix</th><th class="py-3 px-4">État</th><th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($extras as $e)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3.5 px-6"><div class="flex items-center gap-3"><span class="w-9 h-9 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center"><i class="fa-solid {{ $categories[$e->categorie][1] ?? 'fa-star' }}"></i></span><div><p class="font-bold text-slate-900">{{ $e->nom }}</p><p class="text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($e->description, 60) }}</p></div></div></td>
                            <td class="py-3.5 px-4">{{ $categories[$e->categorie][0] ?? $e->categorie }}</td>
                            <td class="py-3.5 px-4 font-bold whitespace-nowrap">{{ $e->prix > 0 ? number_format($e->prix, 0, ',', ' ') . ' F' : 'Inclus' }}</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $e->actif ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $e->actif ? 'Proposée' : 'Masquée' }}</span></td>
                            <td class="py-3.5 px-6">
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('canadian.extras.edit', $e->id) }}" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center"><i class="fa-solid fa-pen text-xs"></i></a>
                                    <form method="POST" action="{{ route('canadian.extras.destroy', $e->id) }}" onsubmit="return confirm('Supprimer l\'option {{ $e->nom }} ?')">@csrf @method('DELETE')
                                        <button class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-trash text-xs"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($extras->hasPages())<div class="px-6 py-4 border-t border-slate-100">{{ $extras->links() }}</div>@endif
    @endif
</div>
@endsection
