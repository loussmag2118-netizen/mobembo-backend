@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Modifier l'élément</h1>
            <p class="text-sm text-slate-500">Mettez à jour les informations de cet enregistrement.</p>
        </div>
        <a href="{{ route('admin.trajets.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Retour
        </a>
    </div>

    <!-- Carte du formulaire -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 sm:p-8">
            <form action="{{ route('admin.trajets.update', $item->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Inclusion des champs du formulaire -->
                @include('admin.partials.form')

                <!-- Boutons d'action -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.trajets.index') }}" class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold rounded-lg transition text-sm">
                        Annuler
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-lg shadow-sm transition text-sm">
                        <i class="fa-solid fa-rotate-right mr-2"></i> Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
