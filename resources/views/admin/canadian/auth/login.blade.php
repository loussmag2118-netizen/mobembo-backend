<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Canadian Airways</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.8; }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
    </style>
</head>
<body class="h-full flex items-center justify-center font-sans antialiased text-white overflow-hidden">
    <!-- Fond dégradé + formes animées -->
    <div class="absolute inset-0 bg-gradient-to-br from-red-900 via-slate-900 to-slate-950"></div>

    <!-- Cercles décoratifs -->
    <div class="absolute top-20 left-20 w-96 h-96 bg-red-600/20 rounded-full blur-3xl animate-pulse-slow"></div>
    <div class="absolute bottom-20 right-20 w-80 h-80 bg-sky-600/20 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 2s;"></div>

    <!-- Avions décoratifs -->
    <div class="absolute top-1/4 left-10 text-red-500/30 text-6xl animate-float">
        <i class="fa-solid fa-plane"></i>
    </div>
    <div class="absolute bottom-1/4 right-10 text-sky-500/30 text-5xl animate-float" style="animation-delay: 3s;">
        <i class="fa-solid fa-plane-departure"></i>
    </div>

    <!-- Carte de connexion -->
    <div class="relative z-10 w-full max-w-md mx-4">
        <div class="glass border border-white/10 rounded-3xl shadow-2xl p-8 md:p-10">

            <!-- En-tête avec logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-red-600 to-red-800 rounded-2xl shadow-lg shadow-red-900/50 mb-4">
                    <i class="fa-solid fa-plane text-3xl text-white"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-1">Canadian Airways</h1>
                <p class="text-sm text-slate-400">Espace administration</p>
            </div>

            <!-- Erreurs -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 text-red-300 text-sm rounded-xl flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Formulaire -->
            <form action="{{ route('canadian.login.submit') }}" method="POST" class="space-y-5">
                @csrf

                <!-- ⭐ Email OU Téléphone -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2 uppercase tracking-wider">
                        Email ou téléphone
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input
                            type="text"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            placeholder="email@canadianairways.com ou 06XXXXXXXX"
                            class="w-full pl-11 pr-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                    </div>
                </div>

                <!-- Mot de passe -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2 uppercase tracking-wider">
                        Mot de passe
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full pl-11 pr-12 py-3 bg-slate-900/50 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                        <button
                            type="button"
                            onclick="togglePassword()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition">
                            <i id="eye-icon" class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- Bouton -->
                <button
                    type="submit"
                    class="w-full py-3.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-semibold rounded-xl shadow-lg shadow-red-900/50 transition transform hover:scale-[1.02] active:scale-[0.98]">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i>
                    Se connecter
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-white/5 text-center">
                <p class="text-xs text-slate-500">
                    © {{ date('Y') }} Canadian Airways — Tous droits réservés
                </p>
            </div>
        </div>

        <!-- Retour accueil -->
        <div class="mt-6 text-center">
            <a href="/" class="text-xs text-slate-400 hover:text-white transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Retour à l'accueil
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>