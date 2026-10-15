{{-- Couleurs de l'agence. Le bloc parent (Alpine) définit `couleur` et `couleur2`. --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @foreach([['couleur', 'couleur', 'Couleur principale', 'Menu, boutons et accueil dans l\'application'], ['couleur_secondaire', 'couleur2', 'Couleur secondaire', 'Accents et dégradés']] as [$nom, $modele, $titre, $aide])
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">{{ $titre }}</label>
            <div class="flex items-center gap-3">
                <input type="color" x-model="{{ $modele }}" aria-label="{{ $titre }}"
                       class="w-12 h-11 shrink-0 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer bg-white">
                <input name="{{ $nom }}" x-model="{{ $modele }}" maxlength="7" pattern="#[0-9A-Fa-f]{6}"
                       class="w-28 px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold uppercase dark:text-slate-100">
            </div>
            <p class="text-[11px] text-slate-400 font-semibold mt-1">{{ $aide }}</p>
            @error($nom)<p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>@enderror
        </div>
    @endforeach
</div>
