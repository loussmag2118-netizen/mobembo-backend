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
                <span class="text-xs font-extrabold text-slate-400">/</span>
                <span class="text-xs font-black text-teal-700 bg-teal-50 px-2.5 py-1 rounded-full border border-teal-200/60">
                    Trajet #{{ $trajet->id }}
                </span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-teal-100 text-teal-600 border border-teal-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-pen-to-square"></i>
                </span>
                <span>Modifier le Trajet</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Ajustez les points de départ et d'arrivée, le bus assigné ou les départs planifiés.
            </p>
        </div>
    </div>

    {{-- CARTE FORMULAIRE --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-9">
        <form action="{{ route('admin.trajets.update', $trajet->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            @include('admin.trajets._form')

            {{-- ACTIONS --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.trajets.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3.5 rounded-2xl text-sm font-bold transition-all">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white px-7 py-3.5 rounded-2xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-amber-400 text-base"></i>
                    <span>Mettre à jour la ligne</span>
                </button>
            </div>
        </form>
    </div>

</div>

@endsection