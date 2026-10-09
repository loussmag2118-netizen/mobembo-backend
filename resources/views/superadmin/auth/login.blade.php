<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Super Admin - Mobembo Line</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        mobembo: {
                            dark: '#064E3B',
                            primary: '#059669',
                            accent: '#34D399',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex items-center justify-center font-sans antialiased bg-slate-950">

    <div class="max-w-md w-full mx-4 bg-slate-900 border border-emerald-900/50 rounded-2xl p-8 shadow-2xl shadow-emerald-950/50">
        <div class="text-center mb-8">
            <div class="inline-block p-1.5 bg-slate-950 border border-emerald-800/60 rounded-2xl mb-3 shadow-lg">
                <img src="{{ url('images/Logo.jpeg') }}" alt="Logo Mobembo Line" class="w-16 h-16 object-cover rounded-xl">
            </div>
            <h1 class="text-2xl font-bold text-white">Mobembo <span class="text-emerald-400">SuperAdmin</span></h1>
            <p class="text-sm text-slate-400 mt-1">Portail de contrôle global des agences</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 text-sm rounded-lg flex items-center">
                <i class="fa-solid fa-circle-exclamation mr-2"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('superadmin.login.submit') }}" method="POST" class="space-y-6" autocomplete="off">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Numéro de téléphone</label>
                <div class="relative">
                    <span class="absolute left-3 top-3.5 text-slate-500"><i class="fa-solid fa-phone"></i></span>
                    <input type="text" name="telephone" required autocomplete="off" placeholder="Ex: 064313808"
                           value="{{ old('telephone') }}"
                           class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-lg text-white placeholder-slate-600 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Mot de passe</label>
                <div class="relative">
                    <span class="absolute left-3 top-3.5 text-slate-500"><i class="fa-solid fa-lock"></i></span>
                    <input id="mot_de_passe" type="password" name="mot_de_passe" required
                           autocomplete="new-password" placeholder="••••••••"
                           class="w-full pl-10 pr-11 py-3 bg-slate-950 border border-slate-800 rounded-lg text-white placeholder-slate-600 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">

                    <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-3.5 text-slate-500 hover:text-emerald-400 focus:outline-none transition-colors">
                        <i id="eye-icon" class="fa-solid fa-eye-slash"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-4 rounded-lg transition-all transform hover:scale-[1.02] shadow-lg shadow-emerald-600/30">
                Se connecter au Portail
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-xs text-slate-600">
                © {{ date('Y') }} Mobembo Line - Système de Gestion Global
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("mot_de_passe");
            const eyeIcon = document.getElementById("eye-icon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            }
        }
    </script>

</body>
</html>