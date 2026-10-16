{{-- État vide : icone, titre, texte, lien facultatif [libellé, url] --}}
<div class="py-14 px-6 text-center">
    <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-3">
        <i class="fa-solid {{ $icone }}"></i>
    </div>
    <p class="font-bold text-slate-700">{{ $titre }}</p>
    <p class="text-sm text-slate-400 mt-1 max-w-md mx-auto">{{ $texte }}</p>
    @isset($lien)
        <a href="{{ $lien[1] }}" class="inline-flex items-center gap-2 mt-4 bg-[#124231] hover:bg-[#0c2f23] text-white text-sm font-bold py-2.5 px-5 rounded-xl">
            <i class="fa-solid fa-plus"></i> {{ $lien[0] }}
        </a>
    @endisset
</div>
