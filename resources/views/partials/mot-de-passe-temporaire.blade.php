{{-- Mot de passe temporaire affiché une seule fois après création / réinitialisation --}}
@if(session('mot_de_passe_temporaire'))
    @php $mdp = session('mot_de_passe_temporaire'); @endphp
    <div x-data="{ copie: false }" class="rounded-2xl border-2 border-amber-300 bg-amber-50 p-5">
        <div class="flex items-start gap-3">
            <span class="w-10 h-10 shrink-0 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center"><i class="fa-solid fa-key"></i></span>
            <div class="flex-1">
                <p class="text-sm font-extrabold text-amber-900">Mot de passe temporaire de {{ $mdp['compte'] }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <code class="px-3 py-2 rounded-xl bg-white border border-amber-200 text-lg font-black tracking-wider text-slate-900">{{ $mdp['mot_de_passe'] }}</code>
                    <button type="button" class="px-3 py-2 rounded-xl bg-amber-600 text-white text-xs font-bold hover:bg-amber-700"
                            @click="navigator.clipboard.writeText('{{ $mdp['mot_de_passe'] }}'); copie = true">
                        <span x-text="copie ? 'Copié' : 'Copier'"></span>
                    </button>
                </div>
                <p class="mt-2 text-xs font-semibold text-amber-800">
                    Transmettez-le à la personne concernée de façon confidentielle. Il ne sera plus affiché après avoir quitté cette page.
                </p>
            </div>
        </div>
    </div>
@endif
