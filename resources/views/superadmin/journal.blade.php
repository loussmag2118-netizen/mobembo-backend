@extends('layouts.admin')

@section('title', "Journal d'audit")
@section('header_icon', 'fa-list-check')
@section('header_title', "Journal d'audit")
@section('header_subtitle', 'Connexions, paiements, remboursements et modifications des administrations')

@section('content')
<div class="space-y-5">
    <form method="GET" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Agence</label>
            <select name="agence" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                <option value="">Toutes</option>
                @foreach($nomsAgences as $id => $nom)
                    <option value="{{ $id }}" @selected(request('agence') == $id)>{{ $nom }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Action contient</label>
            <input type="text" name="action" value="{{ request('action') }}" placeholder="ex. paiement, connexion"
                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Depuis le</label>
            <input type="date" name="du" value="{{ request('du') }}" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
        </div>
        <div class="flex gap-2">
            <button class="flex-1 px-4 py-2.5 rounded-xl bg-[#124231] text-white text-sm font-bold hover:bg-[#0c2f23]">Filtrer</button>
            <a href="{{ route('superadmin.journal') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold">Effacer</a>
        </div>
    </form>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        @include('superadmin.partials.journal-table')
        <div class="px-6 py-4 border-t border-slate-100">{{ $entrees->links() }}</div>
    </div>
</div>
@endsection
