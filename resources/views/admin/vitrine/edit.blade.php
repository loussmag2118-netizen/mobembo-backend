@extends('layouts.admin')

@section('title', "Vitrine de l'agence")
@section('header_icon', 'fa-store')
@section('header_title', "Vitrine de l'agence")
@section('header_subtitle', "Logo, photos, slogan et couleurs de votre agence, dans l'application et dans votre administration")

@section('content')
@php
    $couleur = old('couleur', $agence->couleur ?? '#1B5E20');
    $couleur2 = old('couleur_secondaire', $agence->couleur_secondaire ?? '#F9A825');
    $photos = $agence->photos;
@endphp
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6" x-data="{ couleur: '{{ $couleur }}', couleur2: '{{ $couleur2 }}', slogan: @js(old('slogan', $agence->slogan ?? '')) }">

    <div class="xl:col-span-2 space-y-6">
        @if($errors->any())
            <div class="rounded-2xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm font-semibold text-rose-700">{{ $errors->first() }}</div>
        @endif

        {{-- Logo --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
            <h2 class="text-lg font-black text-slate-900">Logo</h2>
            <p class="text-xs text-slate-500 font-semibold mt-1">Carré de préférence (au moins 400 × 400). Il apparaît sur la page de l'agence, les billets et leur QR code.</p>
            <div class="mt-4 flex items-center gap-5">
                <div class="w-24 h-24 rounded-3xl border border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center">
                    @if($agence->logoMedia)
                        <img src="{{ $agence->logoMedia->url() }}" alt="Logo" class="w-full h-full object-cover">
                    @else
                        <i class="fa-regular fa-image text-3xl text-slate-300"></i>
                    @endif
                </div>
                <form action="{{ route('admin.vitrine.logo') }}" method="POST" enctype="multipart/form-data" data-compresser="600">
                    @csrf
                    <label class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#124231] text-white text-sm font-bold hover:bg-[#0c2f23] cursor-pointer">
                        <i class="fa-solid fa-upload"></i>
                        <span>{{ $agence->logoMedia ? 'Changer le logo' : 'Ajouter le logo' }}</span>
                        <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="hidden" required>
                    </label>
                </form>
            </div>
        </div>

        {{-- Photos --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Photos</h2>
                    <p class="text-xs text-slate-500 font-semibold mt-1">Vos bus, vos gares, vos équipes… Elles défilent en haut de votre page ({{ $photos->count() }} / {{ $photosMax }}).</p>
                </div>
                @if($photos->count() < $photosMax)
                    <form action="{{ route('admin.vitrine.photos.store') }}" method="POST" enctype="multipart/form-data" data-compresser="1600">
                        @csrf
                        <label class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#124231] text-white text-sm font-bold hover:bg-[#0c2f23] cursor-pointer">
                            <i class="fa-solid fa-plus"></i>
                            <span>Ajouter une photo</span>
                            <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="hidden" required>
                        </label>
                    </form>
                @endif
            </div>
            <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-4">
                @forelse($photos as $photo)
                    <div class="relative group rounded-2xl overflow-hidden border border-slate-200 aspect-[4/3] bg-slate-50">
                        <img src="{{ $photo->url() }}" alt="Photo" class="w-full h-full object-cover">
                        <form action="{{ route('admin.vitrine.photos.destroy', $photo->id) }}" method="POST"
                              class="absolute top-2 right-2" onsubmit="return confirm('Supprimer cette photo ?')">
                            @csrf
                            @method('DELETE')
                            <button class="w-8 h-8 rounded-full bg-white/90 text-rose-600 shadow hover:bg-white" title="Supprimer"><i class="fa-solid fa-trash-can text-xs"></i></button>
                        </form>
                    </div>
                @empty
                    <p class="col-span-full text-sm text-slate-400 font-semibold py-6 text-center">Aucune photo : l'application affichera la couleur de l'agence.</p>
                @endforelse
            </div>
        </div>

        {{-- Slogan et couleur --}}
        <form action="{{ route('admin.vitrine.update') }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            @csrf
            @method('PUT')
            <h2 class="text-lg font-black text-slate-900">Slogan et couleurs</h2>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Slogan</label>
                <input name="slogan" maxlength="160" x-model="slogan" placeholder="Ex. La route en toute confiance."
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold">
            </div>
            @include('partials.choix-couleurs')
            <p class="text-xs text-slate-500 font-semibold">Votre administration et votre page dans l'application prennent ces couleurs.</p>
            <button class="px-5 py-2.5 rounded-xl bg-[#124231] text-white text-sm font-bold hover:bg-[#0c2f23]">Enregistrer</button>
        </form>
    </div>

    {{-- Aperçu --}}
    <div>
        <div class="xl:sticky xl:top-28">
            <p class="text-xs font-black uppercase tracking-wider text-slate-500 mb-3">Aperçu dans l'application</p>
            <div class="mx-auto w-[280px] rounded-[2.2rem] border-[10px] border-slate-900 bg-[#F7F8FA] overflow-hidden shadow-xl">
                <div class="h-40 relative" :style="'background: linear-gradient(135deg, ' + couleur + ', ' + couleur2 + ')'">
                    @if($photos->first())
                        <img src="{{ $photos->first()->url() }}" class="absolute inset-0 w-full h-full object-cover">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-transparent to-black/40"></div>
                </div>
                <div class="-mt-9 flex justify-center relative">
                    <div class="w-[72px] h-[72px] rounded-[20px] bg-white border-4 border-white shadow overflow-hidden flex items-center justify-center">
                        @if($agence->logoMedia)
                            <img src="{{ $agence->logoMedia->url() }}" class="w-full h-full object-cover">
                        @else
                            <i class="fa-solid fa-bus text-2xl" :style="'color:' + couleur"></i>
                        @endif
                    </div>
                </div>
                <div class="px-4 pt-2 pb-5 text-center">
                    <p class="font-black text-slate-900">{{ $agence->nom_agence }}</p>
                    <p class="text-xs text-slate-500 mt-1 min-h-[16px]" x-text="slogan || 'Votre slogan apparaîtra ici.'"></p>
                    <div class="mt-4 rounded-xl py-2.5 text-white text-sm font-bold" :style="'background:' + couleur">Réserver un trajet</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Réduit l'image dans le navigateur avant l'envoi (taille max en pixels
    // dans data-compresser), puis envoie le formulaire automatiquement.
    document.querySelectorAll('form[data-compresser]').forEach(function (form) {
        const champ = form.querySelector('input[type=file]');
        champ.addEventListener('change', async function () {
            const fichier = champ.files[0];
            if (!fichier) return;
            const max = parseInt(form.dataset.compresser, 10);
            try {
                const image = await createImageBitmap(fichier);
                const echelle = Math.min(1, max / Math.max(image.width, image.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(image.width * echelle);
                canvas.height = Math.round(image.height * echelle);
                canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);
                const png = fichier.type === 'image/png';
                const blob = await new Promise(r => canvas.toBlob(r, png ? 'image/png' : 'image/jpeg', 0.85));
                if (blob && blob.size < fichier.size) {
                    const transfert = new DataTransfer();
                    transfert.items.add(new File([blob], png ? 'image.png' : 'image.jpg', { type: blob.type }));
                    champ.files = transfert.files;
                }
            } catch (e) { /* envoi de l'original si la réduction échoue */ }
            form.submit();
        });
    });
</script>
@endpush
