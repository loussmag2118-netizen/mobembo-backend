@extends('layouts.admin')

@section('content')

<div class="max-w-4xl mx-auto space-y-7">

    {{-- EN-TÊTE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.trajets.index') }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-emerald-700 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm transition-all">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Retour aux trajets</span>
                </a>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-teal-100 text-teal-600 border border-teal-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-route"></i>
                </span>
                <span>Ajouter une Nouvelle Ligne</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Configurez les agences de départ et d'arrivée, associez un bus et définissez les horaires.
            </p>
        </div>
    </div>

    {{-- CARTE FORMULAIRE --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-9">
        <form action="{{ route('admin.trajets.store') }}" method="POST" class="space-y-6">
            @csrf

            @include('admin.trajets._form')

            {{-- ACTIONS --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.trajets.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3.5 rounded-2xl text-sm font-bold transition-all">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white px-7 py-3.5 rounded-2xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2">
                    <i class="fa-solid fa-check text-amber-400 text-base"></i>
                    <span>Enregistrer la ligne</span>
                </button>
            </div>
        </form>
    </div>

</div>

@endsection