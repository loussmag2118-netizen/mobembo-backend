@extends('layouts.admin')

@section('content')

<div class="max-w-3xl mx-auto space-y-7">

    {{-- EN-TÊTE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.bus.index') }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-emerald-700 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm transition-all">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Retour à la flotte</span>
                </a>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-blue-100 text-blue-600 border border-blue-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-bus"></i>
                </span>
                <span>Ajouter un Véhicule à la Flotte</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Enregistrez un nouveau bus pour vos planifications et assignations de lignes.
            </p>
        </div>
    </div>

    {{-- CARTE DU FORMULAIRE --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-9">
        <form action="{{ route('admin.bus.store') }}" method="POST" class="space-y-6">
            @csrf

            @include('admin.bus._form')

            {{-- ACTIONS --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.bus.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3.5 rounded-2xl text-sm font-bold transition-all">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white px-7 py-3.5 rounded-2xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2">
                    <i class="fa-solid fa-check text-amber-400 text-base"></i>
                    <span>Enregistrer le bus</span>
                </button>
            </div>
        </form>
    </div>

</div>

@endsection