@extends('layouts.admin')

@section('title', 'Passagers')
@section('header_icon', 'fa-users')
@section('header_title', 'Passagers')
@section('header_subtitle', 'Voyageurs des réservations payées : recherchez par nom, passeport ou numéro de vol')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <form method="GET" class="p-5 sm:px-6 border-b border-slate-100 flex flex-col sm:flex-row gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom ou passeport…" class="sm:w-60 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-[#124231] focus:outline-none">
            <input type="text" name="vol" value="{{ request('vol') }}" placeholder="N° de vol" class="sm:w-36 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase">
            <select name="type" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600">
                <option value="">Tous</option>
                <option value="adulte" @selected(request('type') === 'adulte')>Adultes</option>
                <option value="enfant" @selected(request('type') === 'enfant')>Enfants</option>
                <option value="bebe" @selected(request('type') === 'bebe')>Bébés</option>
            </select>
            <button class="bg-[#124231] hover:bg-[#0c2f23] text-white text-sm font-bold py-2 px-4 rounded-xl">Rechercher</button>
        </form>

        @if($passagers->isEmpty())
            @include('admin.canadian.partials.vide', ['icone' => 'fa-user-slash', 'titre' => 'Aucun passager', 'texte' => 'Les passagers des réservations payées apparaissent ici.'])
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-6">Passager</th><th class="py-3 px-4">Type</th><th class="py-3 px-4">Passeport</th><th class="py-3 px-4">Vol</th><th class="py-3 px-6">Réservation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($passagers as $p)
                            @php $vol = $p->reservation?->vol; @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3.5 px-6"><p class="font-bold text-slate-900">{{ trim($p->prenom . ' ' . $p->nom) }}</p><p class="text-xs text-slate-400">{{ $p->nationalite ?: '' }}{{ $p->date_naissance ? ' · ' . \Carbon\Carbon::parse($p->date_naissance)->format('d/m/Y') : '' }}</p></td>
                                <td class="py-3.5 px-4">{{ ucfirst($p->type) }}</td>
                                <td class="py-3.5 px-4 font-mono text-xs">{{ $p->numero_passeport ?: '—' }}</td>
                                <td class="py-3.5 px-4">@if($vol)<p class="font-mono font-bold">{{ $vol->numero_vol }}</p><p class="text-xs text-slate-400">{{ $vol->date_depart?->format('d/m/Y H:i') }}</p>@else — @endif</td>
                                <td class="py-3.5 px-6">@if($p->reservation)<a href="{{ route('canadian.reservations.show', $p->reservation_id) }}" class="font-mono font-bold text-slate-700 hover:underline">{{ $p->reservation->reference }}</a>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($passagers->hasPages())<div class="px-6 py-4 border-t border-slate-100">{{ $passagers->links() }}</div>@endif
        @endif
    </div>
</div>
@endsection
