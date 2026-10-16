{{-- Carte de chiffre clé : titre, valeur, icone, teinte (emerald, sky, amber, rose, slate…), note facultative --}}
<div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
    <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $titre }}</span>
        <span class="w-9 h-9 rounded-xl bg-{{ $teinte ?? 'slate' }}-50 text-{{ $teinte ?? 'slate' }}-600 flex items-center justify-center">
            <i class="fa-solid {{ $icone }}"></i>
        </span>
    </div>
    <p class="text-2xl font-black text-slate-900">{{ $valeur }}</p>
    @isset($note)<p class="text-xs text-slate-400 mt-1.5">{{ $note }}</p>@endisset
</div>
