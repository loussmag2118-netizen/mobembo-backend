<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Mobembo Line</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .btn-green {
            background: #1B5E20;
            transition: all 0.2s;
        }
        .btn-green:hover {
            background: #2E7D32;
            transform: translateY(-1px);
        }
        .form-input {
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus {
            border-color: #2E7D32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.15);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="login-card p-8">

            {{-- Logo / Titre --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-50 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-green-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-green-900">Mobembo Line</h1>
                <p class="text-gray-500 text-sm mt-1">Connectez-vous à votre espace</p>
            </div>

            {{-- Message de succès (déconnexion) --}}
            @if (session('success'))
                <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Erreurs --}}
            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            {{-- Formulaire --}}
            <form method="POST" action="{{ route('unified.login.submit') }}" class="space-y-5">
                @csrf

                {{-- Identifiant --}}
                <div>
                    <label for="identifiant" class="block text-sm font-semibold text-gray-700 mb-1">
                        Téléphone ou Email
                    </label>
                    <input
                        type="text"
                        name="identifiant"
                        id="identifiant"
                        value="{{ old('identifiant') }}"
                        required
                        autofocus
                        placeholder="06 XX XX XX XX ou email"
                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none"
                    >
                </div>

                {{-- Mot de passe --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">
                        Mot de passe
                    </label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        placeholder="Votre mot de passe"
                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none"
                    >
                </div>

                {{-- Se souvenir --}}
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        name="remember"
                        id="remember"
                        class="w-4 h-4 text-green-800 rounded"
                    >
                    <label for="remember" class="ml-2 text-sm text-gray-600">
                        Se souvenir de moi
                    </label>
                </div>

                {{-- Bouton --}}
                <button
                    type="submit"
                    class="btn-green w-full text-white font-semibold py-3 rounded-xl shadow-lg"
                >
                    Se connecter
                </button>
            </form>

            {{-- Footer --}}
            <div class="mt-6 text-center text-xs text-gray-400">
                © {{ date('Y') }} Mobembo Line — Tous droits réservés
            </div>

        </div>
    </div>

</body>
</html>