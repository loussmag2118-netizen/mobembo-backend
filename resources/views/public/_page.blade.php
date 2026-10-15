<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titre') — Mobembo Line</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; } .texte h2 { font-weight: 800; font-size: 1.1rem; margin-top: 1.75rem; color: #0f172a; } .texte p, .texte li { color: #475569; line-height: 1.65; margin-top: .5rem; } .texte ul { list-style: disc; padding-left: 1.25rem; }</style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-slate-100">
    <header class="bg-[#124231] text-white">
        <div class="max-w-3xl mx-auto px-5 py-6 flex items-center gap-3">
            <img src="{{ asset('images/Logo.png') }}" alt="" class="w-10 h-10 rounded-xl bg-white p-1">
            <div>
                <p class="font-extrabold text-lg leading-tight">Mobembo Line</p>
                <p class="text-xs text-emerald-100">Vente et achat de billets de bus</p>
            </div>
        </div>
    </header>
    <main class="max-w-3xl mx-auto px-5 py-8">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-10">
            @yield('contenu')
        </div>
        <p class="text-center text-xs text-slate-400 mt-6">
            <a class="underline" href="{{ route('confidentialite') }}">Politique de confidentialité</a> ·
            <a class="underline" href="{{ route('compte.suppression') }}">Supprimer mon compte</a>
        </p>
    </main>
</body>
</html>
