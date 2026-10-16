@extends('layouts.admin')

@section('title', 'Flotte')
@section('header_icon', 'fa-plane')
@section('header_title', 'Flotte')
@section('header_subtitle', 'Vos avions : leur capacité limite les places mises en vente sur chaque vol')

@section('content')
@php $styles = ['Actif' => 'bg-emerald-50 text-emerald-700', 'Maintenance' => 'bg-amber-50 text-amber-700', 'Inactif' => 'bg-slate-100 text-slate-600']; @endphp
<div class="space-y-6">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.canadian.partials.stat', ['titre' => 'Avions', 'valeur' => $parStatut->sum(), 'icone' => 'fa-plane', 'teinte' => 'slate'])
        @include('admin.canadian.partials.stat', ['titre' => 'En service', 'valeur' => $parStatut['Actif'] ?? 0, 'icone' => 'fa-circle-check', 'teinte' => 'emerald'])
        @include('admin.canadian.partials.stat', ['titre' => 'En maintenance', 'valeur' => $parStatut['Maintenance'] ?? 0, 'icone' => 'fa-screwdriver-wrench', 'teinte' => 'amber'])
        @include('admin.canadian.partials.stat', ['titre' => 'Sièges en service', 'valeur' => number_format($sieges, 0, ',', ' '), 'icone' => 'fa-chair', 'teinte' => 'sky'])
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 sm:px-6 border-b border-slate-100 flex items-center justify-between gap-3">
            <h2 class="text-base font-black text-slate-900">Avions ({{ $flottes->total() }})</h2>
            <a href="{{ route('canadian.flotte.create') }}" class="inline-flex items-center gap-2 bg-[#124231] hover:bg-[#0c2f23] text-white text-sm font-bold py-2.5 px-5 rounded-xl shadow-sm"><i class="fa-solid fa-plus"></i> Ajouter un avion</a>
        </div>
        @if($flottes->isEmpty())
            @include('admin.canadian.partials.vide', ['icone' => 'fa-plane', 'titre' => 'Aucun avion', 'texte' => 'Ajoutez vos avions pour les associer à vos vols.', 'lien' => ['Ajouter un avion', route('canadian.flotte.create')]])
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-6">Avion</th><th class="py-3 px-4">Immatriculation</th><th class="py-3 px-4">Sièges</th><th class="py-3 px-4">Vols à venir</th><th class="py-3 px-4">État</th><th class="py-3 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($flottes as $f)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3.5 px-6"><p class="font-bold text-slate-900">{{ $f->modele ?: $f->nom_appareil }}</p><p class="text-xs text-slate-400">{{ $f->annee ? 'Mis en service en ' . $f->annee : 'Année non précisée' }}</p></td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-700">{{ $f->immatriculation ?: '—' }}</td>
                                <td class="py-3.5 px-4 font-bold">{{ $f->capacite_places }}</td>
                                <td class="py-3.5 px-4">{{ $f->vols_a_venir }}</td>
                                <td class="py-3.5 px-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $styles[$f->statut] ?? 'bg-slate-100 text-slate-600' }}">{{ \App\Http\Controllers\Admin\Canadian\FlotteController::STATUTS[$f->statut] ?? $f->statut }}</span></td>
                                <td class="py-3.5 px-6">
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('canadian.flotte.edit', $f->id) }}" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center"><i class="fa-solid fa-pen text-xs"></i></a>
                                        <form method="POST" action="{{ route('canadian.flotte.destroy', $f->id) }}" onsubmit="return confirm('Retirer cet avion de la flotte ?')">
                                            @csrf @method('DELETE')
                                            <button class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-trash text-xs"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($flottes->hasPages())<div class="px-6 py-4 border-t border-slate-100">{{ $flottes->links() }}</div>@endif
        @endif
    </div>
</div>
@endsection
