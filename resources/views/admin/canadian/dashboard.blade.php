@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('header_icon', 'fa-gauge-high')
@section('header_title', 'Tableau de bord')
@section('header_subtitle', $compagnie->nom_agence . ' · ' . now()->locale('fr')->isoFormat('dddd D MMMM YYYY'))

@section('content')
@php $aFaire = collect($demarrage)->reject(fn ($e) => $e[1]); @endphp
<div class="space-y-6">
    <div class="carte-agence relative overflow-hidden rounded-3xl bg-[#124231] text-white p-6 sm:p-8 shadow-sm">
        <i class="fa-solid fa-plane absolute -right-6 -bottom-10 text-[150px] text-white/10 -rotate-12"></i>
        <div class="relative flex flex-col sm:flex-row sm:items-center gap-5">
            <img src="{{ $compagnie->logoPublicUrl() }}" alt="" class="w-16 h-16 rounded-2xl bg-white object-contain p-1.5">
            <div class="flex-1">
                <p class="text-xs font-bold uppercase tracking-wider text-white/70">Compagnie aérienne</p>
                <h2 class="text-2xl font-black">Bonjour {{ auth()->user()->prenom }}</h2>
                <p class="text-sm text-white/80">{{ $stats['vols_a_venir'] }} vol(s) programmé(s) · {{ $stats['places_mois'] }} place(s) vendue(s) ce mois-ci</p>
            </div>
            <a href="{{ route('canadian.vols.create') }}" class="inline-flex items-center justify-center gap-2 bg-white text-slate-900 font-bold text-sm py-2.5 px-5 rounded-xl hover:bg-slate-100"><i class="fa-solid fa-plus"></i> Programmer un vol</a>
        </div>
    </div>

    @if($aFaire->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6">
            <h2 class="text-base font-black text-amber-900 mb-3"><i class="fa-solid fa-list-check mr-1"></i> Pour commencer à vendre</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($demarrage as [$etape, $fait, $lien])
                    <a href="{{ $lien }}" class="flex items-center gap-3 bg-white rounded-2xl px-4 py-3 border {{ $fait ? 'border-emerald-200' : 'border-amber-200 hover:border-amber-300' }}">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs {{ $fait ? 'bg-emerald-600 text-white' : 'bg-amber-100 text-amber-700' }}"><i class="fa-solid {{ $fait ? 'fa-check' : 'fa-arrow-right' }}"></i></span>
                        <span class="text-sm font-bold {{ $fait ? 'text-slate-400 line-through' : 'text-slate-800' }}">{{ $etape }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.canadian.partials.stat', ['titre' => 'Encaissé aujourd\'hui', 'valeur' => number_format($stats['chiffre_jour'], 0, ',', ' ') . ' F', 'icone' => 'fa-calendar-day', 'teinte' => 'emerald'])
        @include('admin.canadian.partials.stat', ['titre' => 'Encaissé ce mois', 'valeur' => number_format($stats['chiffre_mois'], 0, ',', ' ') . ' F', 'icone' => 'fa-sack-dollar', 'teinte' => 'sky'])
        @include('admin.canadian.partials.stat', ['titre' => 'En attente de paiement', 'valeur' => $stats['en_attente'], 'icone' => 'fa-hourglass-half', 'teinte' => 'amber'])
        @include('admin.canadian.partials.stat', ['titre' => 'À rembourser', 'valeur' => $stats['a_rembourser'], 'icone' => 'fa-rotate-left', 'teinte' => $stats['a_rembourser'] ? 'rose' : 'slate'])
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 sm:px-6 border-b border-slate-100 flex items-center justify-between"><h2 class="text-base font-black text-slate-900">Prochains vols</h2><a href="{{ route('canadian.vols.index') }}" class="text-sm font-bold text-[#124231] hover:underline">Tous les vols</a></div>
            @forelse($prochainsVols as $vol)
                @php $total = $vol->places_totales ?? $vol->places_disponibles; $vendues = max(0, $total - $vol->places_disponibles); @endphp
                <a href="{{ route('canadian.vols.show', $vol->id) }}" class="flex items-center gap-4 px-6 py-3.5 border-b border-slate-100 last:border-0 hover:bg-slate-50/70">
                    <div class="text-center w-14"><p class="text-lg font-black text-slate-900 leading-none">{{ $vol->date_depart->format('d') }}</p><p class="text-[11px] font-bold uppercase text-slate-400">{{ $vol->date_depart->locale('fr')->isoFormat('MMM') }}</p></div>
                    <div class="flex-1 min-w-0"><p class="font-bold text-slate-900">{{ $vol->ville_depart }} → {{ $vol->ville_arrivee }}</p><p class="text-xs text-slate-400 font-mono">{{ $vol->numero_vol }} · {{ $vol->date_depart->format('H:i') }}</p></div>
                    <div class="text-right"><p class="font-black text-slate-900">{{ $vendues }}/{{ $total }}</p><p class="text-[11px] text-slate-400">places vendues</p></div>
                </a>
            @empty
                @include('admin.canadian.partials.vide', ['icone' => 'fa-plane', 'titre' => 'Aucun vol à venir', 'texte' => 'Programmez un vol pour qu\'il apparaisse dans l\'application.'])
            @endforelse
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 sm:px-6 border-b border-slate-100 flex items-center justify-between"><h2 class="text-base font-black text-slate-900">Dernières réservations</h2><a href="{{ route('canadian.reservations.index') }}" class="text-sm font-bold text-[#124231] hover:underline">Toutes</a></div>
            @forelse($dernieres as $r)
                <a href="{{ route('canadian.reservations.show', $r->id) }}" class="flex items-center justify-between gap-3 px-6 py-3.5 border-b border-slate-100 last:border-0 hover:bg-slate-50/70">
                    <div class="min-w-0"><p class="font-bold text-slate-900 truncate">{{ $r->nom_passager ?: $r->reference }}</p><p class="text-xs text-slate-400">{{ $r->vol?->numero_vol }} · {{ $r->nombre_places }} place(s) · {{ $r->created_at?->diffForHumans() }}</p></div>
                    <div class="text-right shrink-0"><p class="font-black text-slate-900">{{ number_format($r->montant_total, 0, ',', ' ') }} F</p>@include('admin.canadian.partials.statut-reservation', ['statut' => $r->statut])</div>
                </a>
            @empty
                @include('admin.canadian.partials.vide', ['icone' => 'fa-ticket', 'titre' => 'Aucune réservation', 'texte' => 'Les réservations de l\'application s\'afficheront ici.'])
            @endforelse
        </div>
    </div>
</div>
@endsection
