@if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl">
        <p class="font-bold text-sm flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i> Veuillez corriger :</p>
        <ul class="list-disc list-inside text-sm mt-1.5 space-y-0.5">
            @foreach($errors->all() as $erreur)
                <li>{{ $erreur }}</li>
            @endforeach
        </ul>
    </div>
@endif
