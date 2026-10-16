@extends('layouts.admin')

@section('title', 'Codes promo')
@section('header_icon', 'fa-tags')
@section('header_title', 'Codes promo')
@section('header_subtitle', 'Réductions que vos clients saisissent au moment de payer')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @include('admin.canadian.partials.stat', ['titre' => 'Utilisables aujourd\'hui', 'valeur' => $stats['actifs'], 'icone' => 'fa-circle-check', 'teinte' => 'emerald'])
        @include('admin.canadian.partials.stat', ['titre' => 'Expirés', 'valeur' => $stats['expires'], 'icone' => 'fa-clock-rotate-left', 'teinte' => 'slate'])
        @include('admin.canadian.partials.stat', ['titre' => 'Utilisations', 'valeur' => $stats['utilisations'], 'icone' => 'fa-receipt', 'teinte' => 'sky'])
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 sm:px-6 border-b border-slate-100 flex items-center justify-between gap-3">
            <h2 class="text-base font-black text-slate-900">Codes ({{ $promos->total() }})</h2>
            <a href="{{ route('canadian.promos.create') }}" class="inline-flex items-center gap-2 bg-[#124231] hover:bg-[#0c2f23] text-white text-sm font-bold py-2.5 px-5 rounded-xl"><i class="fa-solid fa-plus"></i> Nouveau code</a>
        </div>
        @if($promos->isEmpty())
            @include('admin.canadian.partials.vide', ['icone' => 'fa-tags', 'titre' => 'Aucun code promo', 'texte' => 'Créez un code pour une opération commerciale (ex. RENTREE10).', 'lien' => ['Créer un code', route('canadian.promos.create')]])
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-6">Code</th><th class="py-3 px-4">Réduction</th><th class="py-3 px-4">Validité</th><th class="py-3 px-4">Utilisations</th><th class="py-3 px-4">État</th><th class="py-3 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($promos as $promo)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3.5 px-6 font-mono font-black text-slate-900">{{ $promo->code }}</td>
                                <td class="py-3.5 px-4 font-bold">{{ $promo->type === 'pourcentage' ? rtrim(rtrim(number_format($promo->valeur, 2, ',', ''), '0'), ',') . ' %' : number_format($promo->valeur, 0, ',', ' ') . ' F' }}</td>
                                <td class="py-3.5 px-4 text-xs text-slate-600 whitespace-nowrap">{{ $promo->date_debut?->format('d/m/Y') }} → {{ $promo->date_fin?->format('d/m/Y') }}</td>
                                <td class="py-3.5 px-4">{{ $promo->utilisations_count }}{{ $promo->utilisation_max ? ' / ' . $promo->utilisation_max : '' }}</td>
                                <td class="py-3.5 px-4">{!! $promo->statut_badge !!}</td>
                                <td class="py-3.5 px-6">
                                    <div class="flex justify-end gap-1.5">
                                        <form method="POST" action="{{ route('canadian.promos.toggle', $promo->id) }}">@csrf @method('PATCH')
                                            <button title="{{ $promo->actif ? 'Désactiver' : 'Activer' }}" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center"><i class="fa-solid {{ $promo->actif ? 'fa-pause' : 'fa-play' }} text-xs"></i></button>
                                        </form>
                                        <a href="{{ route('canadian.promos.edit', $promo->id) }}" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center"><i class="fa-solid fa-pen text-xs"></i></a>
                                        <form method="POST" action="{{ route('canadian.promos.destroy', $promo->id) }}" onsubmit="return confirm('Supprimer le code {{ $promo->code }} ?')">@csrf @method('DELETE')
                                            <button class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-trash text-xs"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($promos->hasPages())<div class="px-6 py-4 border-t border-slate-100">{{ $promos->links() }}</div>@endif
        @endif
    </div>
</div>
@endsection
