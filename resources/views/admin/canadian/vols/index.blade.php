@extends('layouts.admin')

@section('title', 'Vols')
@section('header_icon', 'fa-plane-departure')
@section('header_title', 'Vols')
@section('header_subtitle', 'Programmez vos vols : ils sont proposés dans l\'application tant qu\'ils sont programmés et à venir')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.canadian.partials.stat', ['titre' => 'Total', 'valeur' => $parStatut->sum(), 'icone' => 'fa-plane', 'teinte' => 'slate'])
        @include('admin.canadian.partials.stat', ['titre' => 'Programmés', 'valeur' => $parStatut['programme'] ?? 0, 'icone' => 'fa-calendar-check', 'teinte' => 'emerald'])
        @include('admin.canadian.partials.stat', ['titre' => 'En vol', 'valeur' => $parStatut['en_vol'] ?? 0, 'icone' => 'fa-plane-up', 'teinte' => 'sky'])
        @include('admin.canadian.partials.stat', ['titre' => 'Annulés', 'valeur' => $parStatut['annule'] ?? 0, 'icone' => 'fa-ban', 'teinte' => 'rose'])
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 sm:px-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                <div class="relative sm:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Numéro ou ville…"
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-[#124231] focus:outline-none">
                </div>
                <select name="statut" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Http\Controllers\Admin\Canadian\VolController::STATUTS as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(request('statut') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('canadian.vols.create') }}" class="inline-flex items-center justify-center gap-2 bg-[#124231] hover:bg-[#0c2f23] text-white text-sm font-bold py-2.5 px-5 rounded-xl shadow-sm">
                <i class="fa-solid fa-plus"></i> Nouveau vol
            </a>
        </div>

        @if($vols->isEmpty())
            @include('admin.canadian.partials.vide', [
                'icone' => 'fa-plane-circle-xmark', 'titre' => 'Aucun vol',
                'texte' => request()->hasAny(['q', 'statut']) ? 'Aucun vol ne correspond à ces critères.' : 'Ajoutez d\'abord vos aéroports et votre flotte, puis programmez votre premier vol.',
                'lien' => ['Programmer un vol', route('canadian.vols.create')],
            ])
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-6">Vol</th>
                            <th class="py-3 px-4">Trajet</th>
                            <th class="py-3 px-4">Départ</th>
                            <th class="py-3 px-4">Remplissage</th>
                            <th class="py-3 px-4">Prix</th>
                            <th class="py-3 px-4">Statut</th>
                            <th class="py-3 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($vols as $vol)
                            @php
                                $total = $vol->places_totales ?? $vol->places_disponibles;
                                $vendues = max(0, $total - $vol->places_disponibles);
                                $taux = $total > 0 ? round($vendues / $total * 100) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3.5 px-6">
                                    <a href="{{ route('canadian.vols.show', $vol->id) }}" class="font-mono font-black text-slate-900 hover:underline">{{ $vol->numero_vol }}</a>
                                    <p class="text-xs text-slate-400">{{ $vol->avion ?: 'Avion non précisé' }}</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-800">{{ $vol->ville_depart }} <i class="fa-solid fa-arrow-right text-[10px] text-slate-400 mx-1"></i> {{ $vol->ville_arrivee }}</p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $vol->ville_depart_iata }} – {{ $vol->ville_arrivee_iata }} · {{ $vol->duree_formatee }}{{ $vol->type_vol === 'escale' ? ' · avec escale' : '' }}</p>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <p class="font-semibold text-slate-700">{{ $vol->date_depart?->format('d/m/Y') }}</p>
                                    <p class="text-xs text-slate-400">{{ $vol->date_depart?->format('H:i') }} → {{ $vol->date_arrivee?->format('H:i') }}</p>
                                </td>
                                <td class="py-3.5 px-4 min-w-[130px]">
                                    <div class="flex items-center justify-between text-xs font-bold text-slate-600 mb-1">
                                        <span>{{ $vendues }}/{{ $total }}</span><span>{{ $taux }} %</span>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-[#124231]" style="width: {{ $taux }}%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800 whitespace-nowrap">{{ number_format($vol->prix, 0, ',', ' ') }} F</td>
                                <td class="py-3.5 px-4">@include('admin.canadian.partials.statut-vol', ['statut' => $vol->statut])</td>
                                <td class="py-3.5 px-6">
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('canadian.vols.show', $vol->id) }}" title="Détail" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center"><i class="fa-solid fa-eye text-xs"></i></a>
                                        <a href="{{ route('canadian.vols.edit', $vol->id) }}" title="Modifier" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center"><i class="fa-solid fa-pen text-xs"></i></a>
                                        <form method="POST" action="{{ route('canadian.vols.dupliquer', $vol->id) }}">
                                            @csrf
                                            <button title="Dupliquer (une semaine plus tard)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center"><i class="fa-solid fa-copy text-xs"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('canadian.vols.destroy', $vol->id) }}" onsubmit="return confirm('Supprimer le vol {{ $vol->numero_vol }} ?')">
                                            @csrf @method('DELETE')
                                            <button title="Supprimer" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-trash text-xs"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($vols->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">{{ $vols->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection
