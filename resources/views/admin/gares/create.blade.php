@extends('layouts.admin')

@section('content')

<div class="max-w-2xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Ajouter une gare
            </h2>
            <p class="text-slate-500 text-sm mt-1">
                La gare apparaîtra immédiatement dans les formulaires de trajet.
            </p>
        </div>
        <a href="{{ route('admin.gares.index') }}"
           class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
            ← Retour
        </a>
    </div>

    <div class="bg-white shadow ring-1 ring-slate-200 rounded-xl p-6">
        <form action="{{ route('admin.gares.store') }}" method="POST" class="space-y-5">
            @csrf

            @include('admin.gares._form')

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('admin.gares.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2.5 rounded-lg text-sm font-medium">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-lg text-sm font-medium">
                    💾 Enregistrer la gare
                </button>
            </div>
        </form>
    </div>

</div>

@endsection