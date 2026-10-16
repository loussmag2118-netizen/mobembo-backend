@extends('layouts.admin')

@section('title', 'Notifications clients')
@section('header_icon', 'fa-bell')
@section('header_title', 'Notifications clients')
@section('header_subtitle', 'Messages affichés dans l\'application de vos clients (retard, porte d\'embarquement, offre…)')

@section('content')
@php
    $champ = 'w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#124231] focus:outline-none';
    $etiquette = 'block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5';
    $types = ['info' => ['Information', 'bg-sky-50 text-sky-700'], 'warning' => ['Alerte', 'bg-amber-50 text-amber-700'], 'success' => ['Confirmation', 'bg-emerald-50 text-emerald-700'], 'promo' => ['Offre', 'bg-violet-50 text-violet-700']];
@endphp
<div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
    <form method="POST" action="{{ route('canadian.notifications.store') }}" class="xl:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4 self-start" x-data="{ cible: @js(old('cible', 'tous')) }">
        @csrf
        <h2 class="text-base font-black text-slate-900">Nouveau message</h2>
        @include('admin.canadian.partials.erreurs')
        <div>
            <label class="{{ $etiquette }}">Destinataires *</label>
            <select name="cible" x-model="cible" class="{{ $champ }}">
                <option value="tous">Tous mes clients ({{ $clients->count() }})</option>
                <option value="vol">Les passagers d'un vol</option>
                <option value="client">Un client</option>
            </select>
        </div>
        <div x-show="cible === 'vol'">
            <label class="{{ $etiquette }}">Numéro du vol</label>
            <input name="numero_vol" value="{{ old('numero_vol') }}" placeholder="Ex : CA101" class="{{ $champ }} font-mono uppercase">
        </div>
        <div x-show="cible === 'client'">
            <label class="{{ $etiquette }}">Client</label>
            <select name="client_id" class="{{ $champ }}">
                <option value="">Choisir…</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" @selected((int) old('client_id') === $c->id)>{{ trim(($c->prenom ?? '') . ' ' . ($c->nom ?? '')) ?: $c->name }} — {{ $c->telephone }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $etiquette }}">Type *</label>
            <select name="type" class="{{ $champ }}">
                @foreach($types as $cle => [$libelle])
                    <option value="{{ $cle }}" @selected(old('type', 'info') === $cle)>{{ $libelle }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $etiquette }}">Titre *</label>
            <input name="titre" required maxlength="120" value="{{ old('titre') }}" placeholder="Ex : Changement de porte" class="{{ $champ }}">
        </div>
        <div>
            <label class="{{ $etiquette }}">Message *</label>
            <textarea name="message" required maxlength="1000" rows="4" class="{{ $champ }}">{{ old('message') }}</textarea>
        </div>
        <button class="w-full py-3 rounded-xl bg-[#124231] hover:bg-[#0c2f23] text-white font-bold text-sm"><i class="fa-solid fa-paper-plane mr-1"></i> Envoyer</button>
    </form>

    <div class="xl:col-span-3 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden self-start">
        <div class="p-5 sm:px-6 border-b border-slate-100"><h2 class="text-base font-black text-slate-900">Messages envoyés ({{ $historique->total() }})</h2></div>
        @forelse($historique as $n)
            <div class="px-6 py-4 border-b border-slate-100 last:border-0 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $types[$n->type][1] ?? 'bg-slate-100 text-slate-600' }}">{{ $types[$n->type][0] ?? $n->type }}</span>
                        <p class="font-bold text-slate-900">{{ $n->titre }}</p>
                    </div>
                    <p class="text-sm text-slate-600 mt-1">{{ $n->message }}</p>
                    <p class="text-xs text-slate-400 mt-1">À {{ $n->canadianUser ? (trim(($n->canadianUser->prenom ?? '') . ' ' . ($n->canadianUser->nom ?? '')) ?: $n->canadianUser->name) : '—' }} · {{ $n->created_at?->format('d/m/Y H:i') }} · {{ $n->lu ? 'lu' : 'non lu' }}</p>
                </div>
                <form method="POST" action="{{ route('canadian.notifications.destroy', $n->id) }}" onsubmit="return confirm('Supprimer ce message ?')">@csrf @method('DELETE')
                    <button class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-trash text-xs"></i></button>
                </form>
            </div>
        @empty
            @include('admin.canadian.partials.vide', ['icone' => 'fa-bell-slash', 'titre' => 'Aucun message', 'texte' => 'Les messages envoyés à vos clients apparaîtront ici.'])
        @endforelse
        @if($historique->hasPages())<div class="px-6 py-4 border-t border-slate-100">{{ $historique->links() }}</div>@endif
    </div>
</div>
@endsection
