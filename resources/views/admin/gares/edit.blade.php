@extends('layouts.admin')

@section('content')

<div class="max-w-2xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Modifier la gare
            </h2>
            <p class="text-slate-500 text-sm mt-1">
                Les trajets associés seront automatiquement mis à jour.
            </p>
        </div>
        <a href="{{ route('admin.gares.index') }}"
           class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
            ← Retour
        </a>
    </div>

    <div class="bg-white shadow ring-1 ring-slate-200 rounded-xl p-6">
        <form action="{{ route('admin.gares.update', $gare->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            @include('admin.gares._form')

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('admin.gares.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2.5 rounded-lg text-sm font-medium">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium">
                    💾 Mettre à jour
                </button>
            </div>
        </form>
    </div>

</div>

@endsection