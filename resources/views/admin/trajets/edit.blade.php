@extends('layouts.admin')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.trajets.index') }}"
           class="text-sm text-emerald-600 hover:underline font-medium inline-block mb-2">
            &larr; Retour à la liste des trajets
        </a>
        <h2 class="text-2xl font-bold text-slate-900">
            Modifier le trajet
        </h2>
        <p class="text-slate-500 text-sm mt-1">
            Modifiez les informations du trajet, le bus assigné et ses horaires.
        </p>
    </div>

    <form action="{{ route('admin.trajets.update', $trajet->id) }}" method="POST"
          class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        @csrf
        @method('PUT')

        @include('admin.trajets._form')

        <div class="flex items-center gap-3 pt-4 mt-5 border-t border-slate-200">
            <button type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                💾 Mettre à jour
            </button>
            <a href="{{ route('admin.trajets.index') }}"
               class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-6 py-3 rounded-lg font-semibold transition">
                Annuler
            </a>
        </div>
    </form>
</div>

@endsection