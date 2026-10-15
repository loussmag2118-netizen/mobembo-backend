@extends('public._page')
@section('titre', 'Supprimer mon compte')
@section('contenu')
@if(session('supprime'))
    <div class="text-center py-6">
        <div class="mx-auto w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-black">✓</div>
        <h1 class="mt-4 text-2xl font-extrabold text-slate-900">Compte supprimé</h1>
        <p class="mt-2 text-slate-500">Votre compte et vos accès ont été supprimés.</p>
    </div>
@else
    <h1 class="text-2xl font-extrabold text-slate-900">Supprimer mon compte Mobembo Line</h1>
    <p class="mt-2 text-slate-500">Vous pouvez aussi le faire dans l'application : <strong>Mon compte → Supprimer mon compte</strong>.</p>

    <div class="mt-5 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900">
        <p class="font-bold">Ce qui sera supprimé</p>
        <p class="mt-1">Votre compte (nom, téléphone, mot de passe) et vos accès, immédiatement et définitivement.</p>
        <p class="font-bold mt-3">Ce qui est conservé</p>
        <p class="mt-1">Les billets déjà achetés, sans lien avec votre compte, pour l'embarquement et la comptabilité.</p>
    </div>

    <form method="POST" action="{{ route('compte.supprimer') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Application utilisée</label>
            <select name="espace" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 font-semibold">
                <option value="bus" @selected(old('espace') !== 'canadian')>Mobembo Line (bus)</option>
                <option value="canadian" @selected(old('espace') === 'canadian')>Canadian Airways (vols)</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Numéro de téléphone</label>
            <input name="telephone" value="{{ old('telephone') }}" inputmode="numeric" placeholder="061234567" required
                   class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 font-semibold">
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Mot de passe</label>
            <input type="password" name="mot_de_passe" required class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50">
        </div>
        <label class="flex items-start gap-3 text-sm text-slate-600">
            <input type="checkbox" name="confirmation" value="1" class="mt-1 w-4 h-4">
            <span>Je comprends que la suppression est définitive.</span>
        </label>
        @if($errors->any())
            <p class="text-sm font-bold text-rose-600">{{ $errors->first() }}</p>
        @endif
        <button class="w-full py-3.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold">Supprimer définitivement mon compte</button>
    </form>
@endif
@endsection
