<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Mobembo Line</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-gradient-to-br from-emerald-950 via-slate-900 to-emerald-900 relative overflow-hidden">

    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-96 h-96 bg-emerald-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md z-10">
        <div class="bg-white rounded-3xl shadow-2xl shadow-emerald-950/40 border border-emerald-100 p-8 sm:p-10 relative overflow-hidden">

            <div class="absolute -top-10 -right-10 w-28 h-28 bg-emerald-500/10 rounded-full blur-xl pointer-events-none"></div>

            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center mb-4">
                    <img src="{{ asset('images/Logo.png') }}" alt="Logo Mobembo Line" class="w-20 h-20 object-contain rounded-2xl shadow-md border-2 border-emerald-500">
                </div>
                <h1 class="font-black text-2xl tracking-wider uppercase text-slate-900">
                    Mobembo <span class="text-emerald-600">Line</span>
                </h1>
                <h2 class="mt-3 text-lg font-bold text-slate-800">
                    Accès au portail Agence
                </h2>
                <p class="text-xs font-medium text-slate-500 mt-1">
                    Saisissez vos identifiants pour gérer votre flotte
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl text-sm text-red-700 flex items-center space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-red-500 text-base shrink-0"></i>
                    <span class="font-medium">{{ $errors->first() }}</span>
                </div>
            @endif

            <form class="space-y-5" action="{{ route('admin.login.submit') }}" method="POST">
                @csrf

                <!-- Champ Téléphone -->
                <div>
                    <label for="telephone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Numéro de téléphone
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-600">
                            <i class="fa-solid fa-phone text-sm"></i>
                        </div>
                        <input id="telephone" name="telephone" type="tel" required
                               placeholder="064313808"
                               value="{{ old('telephone') }}"
                               autocomplete="username"
                               class="block w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-slate-900 text-sm placeholder:text-slate-400 focus:bg-white focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15 focus:outline-none transition-all">
                    </div>
                </div>

                <!-- Champ Mot de Passe -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Mot de passe
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-600">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input id="password" name="password" type="password" required
                               placeholder="••••••••"
                               autocomplete="current-password"
                               class="block w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-11 text-slate-900 text-sm placeholder:text-slate-400 focus:bg-white focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15 focus:outline-none transition-all">

                        <div class="absolute inset-y-0 right-0 flex items-center pr-3.5">
                            <button type="button" onclick="togglePassword()" class="text-slate-400 hover:text-emerald-700 focus:outline-none p-1 transition-colors">
                                <i id="eye-icon" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bouton Soumettre -->
                <div class="pt-2">
                    <button type="submit"
                            class="w-full flex justify-center items-center rounded-xl bg-emerald-600 py-3.5 px-4 text-sm font-bold text-white shadow-lg shadow-emerald-600/30 hover:bg-emerald-700 active:scale-[0.98] transition-all duration-200">
                        <span>Se connecter</span>
                        <i class="fa-solid fa-arrow-right-long ml-2 text-xs text-white"></i>
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-xs font-medium text-emerald-100/70 mt-8">
            &copy; Mobembo Line — Tous droits réservés.
        </p>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const eyeIcon = document.getElementById("eye-icon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>