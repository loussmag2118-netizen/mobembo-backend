<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Mobembo Line</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Couleurs du logo Mobembo Line
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        mobembo: {
                            fonce:  '#0B5C10',
                            vert:   '#126E17',
                            clair:  '#3FA535',
                            jaune:  '#FDD302',
                            orange: '#F28C1B',
                            rouge:  '#E2381B',
                        },
                    },
                },
            },
        };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Champ souligné : la ligne verte s'étire au focus */
        .champ-bloc { position: relative; }
        .champ {
            width: 100%;
            border: 0;
            border-bottom: 1.5px solid #E5E7EB;
            background: transparent;
            padding: 0.7rem 2.25rem 0.7rem 0;
            font-size: 0.95rem;
            color: #0F172A;
            outline: none;
        }
        .champ::placeholder { color: #A8B5AA; }
        .champ-bloc::after {
            content: '';
            position: absolute;
            left: 0; bottom: 0;
            height: 2px; width: 100%;
            background: linear-gradient(90deg, #3FA535, #126E17);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .25s ease;
        }
        .champ-bloc:focus-within::after { transform: scaleX(1); }
        .champ-bloc:focus-within .champ-icone { color: #126E17; }

        /* Apparition douce */
        @keyframes monter { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }
        .apparition { animation: monter .6s ease-out both; }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-full bg-white flex items-center justify-center p-4 sm:p-8">

    <main class="w-full max-w-5xl apparition">
        <div class="relative flex flex-col md:flex-row bg-white rounded-[28px] overflow-hidden shadow-[0_30px_80px_-25px_rgba(11,92,16,0.35)] ring-1 ring-slate-100 md:min-h-[600px]">

            {{-- ===================== Panneau de marque (gauche) ===================== --}}
            {{-- Volontairement neutre : la page sert à toutes les agences partenaires --}}
            <section class="relative h-60 md:h-auto md:w-[44%] shrink-0 overflow-hidden bg-gradient-to-br from-mobembo-clair via-mobembo-vert to-mobembo-fonce">

                {{-- Halos lumineux jaune et orange --}}
                <div class="absolute -top-32 -left-24 h-96 w-96 rounded-full bg-white/15 blur-3xl"></div>
                <div class="absolute top-16 right-10 h-40 w-40 rounded-full bg-mobembo-jaune/20 blur-3xl"></div>
                <div class="absolute -bottom-28 -left-16 h-72 w-72 rounded-full bg-mobembo-orange/25 blur-3xl"></div>

                {{-- Trame de points très discrète --}}
                <div class="absolute inset-0 opacity-[0.12] [background-image:radial-gradient(white_1px,transparent_1px)] [background-size:22px_22px]"></div>

                {{-- Liseré jaune / orange / rouge, comme la flamme du logo --}}
                <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-gradient-to-r from-mobembo-jaune via-mobembo-orange to-mobembo-rouge"></div>

                <div class="relative h-full flex flex-col p-6 pb-10 md:p-10 md:pr-24 text-white">
                    {{-- Logo (variante au texte blanc pour le fond vert) --}}
                    <div class="flex-1 grid place-items-center">
                        <img src="{{ asset('images/logo-mobembo-blanc.png') }}" alt="Mobembo Line — Vente &amp; achat de tickets"
                             class="w-48 md:w-72 h-auto drop-shadow-[0_12px_30px_rgba(0,0,0,0.25)]">
                    </div>
                </div>

                {{-- Courbe blanche (ordinateur) --}}
                <svg class="hidden md:block absolute top-0 right-0 h-full w-20 text-white" viewBox="0 0 100 600" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M100 0 L62 0 C 18 130, 12 250, 46 355 S 72 535, 42 600 L100 600 Z" fill="currentColor"/>
                </svg>
                {{-- Courbe blanche (téléphone) --}}
                <svg class="md:hidden absolute bottom-0 left-0 w-full h-8 text-white" viewBox="0 0 400 40" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0 40 L0 22 C 120 0, 280 38, 400 14 L400 40 Z" fill="currentColor"/>
                </svg>
            </section>

            {{-- ===================== Formulaire (droite) ===================== --}}
            <section class="flex-1 flex items-center px-6 pt-4 pb-10 sm:px-12 md:px-14 md:py-12">
                <div class="w-full max-w-sm mx-auto">

                    <h1 class="text-center text-3xl font-extrabold uppercase tracking-[0.12em] text-slate-900">Connexion</h1>

                    @if (session('success'))
                        <div role="status" class="mt-6 flex items-start gap-3 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-mobembo-fonce">
                            <i class="fa-solid fa-circle-check mt-0.5 shrink-0 text-mobembo-clair"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div role="alert" class="mt-6 flex items-start gap-3 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-800">
                            <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0 text-mobembo-rouge"></i>
                            <div>
                                <p class="font-semibold">Oups, la connexion n'a pas abouti.</p>
                                <p class="mt-0.5 text-red-700">{{ $errors->first() }}</p>
                            </div>
                        </div>
                    @endif

                    <form id="form-connexion" class="mt-8 space-y-7" action="{{ route('unified.login.submit') }}" method="POST">
                        @csrf

                        <div>
                            <label for="identifiant" class="block mb-2 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-700">
                                Téléphone ou e-mail
                            </label>
                            <div class="champ-bloc">
                                <input id="identifiant" name="identifiant" type="text" required
                                       placeholder="06 123 45 67 ou e-mail"
                                       value="{{ old('identifiant') }}"
                                       autocomplete="username"
                                       @if (! old('identifiant')) autofocus @endif
                                       class="champ">
                                <i class="champ-icone fa-regular fa-user absolute right-1 top-1/2 -translate-y-1/2 text-sm text-slate-400 transition-colors"></i>
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block mb-2 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-700">
                                Mot de passe
                            </label>
                            <div class="champ-bloc">
                                <input id="password" name="password" type="password" required
                                       placeholder="Votre mot de passe"
                                       autocomplete="current-password"
                                       @if (old('identifiant')) autofocus @endif
                                       class="champ">
                                <button type="button" id="voir-mdp" aria-label="Afficher le mot de passe" aria-pressed="false"
                                        class="absolute right-0 top-1/2 -translate-y-1/2 rounded-full p-1.5 text-slate-400 hover:text-mobembo-vert hover:bg-emerald-50 transition-colors">
                                    <i id="eye-icon" class="fa-solid fa-eye text-sm"></i>
                                </button>
                            </div>
                            <p id="alerte-majuscules" class="hidden mt-2 text-xs font-medium text-mobembo-orange">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                La touche Majuscules est activée.
                            </p>
                        </div>

                        <label for="remember" class="flex items-center gap-3 cursor-pointer select-none w-fit">
                            <input id="remember" name="remember" type="checkbox"
                                   class="h-[18px] w-[18px] rounded border-slate-300 accent-[#126E17]">
                            <span class="text-sm text-slate-600">Rester connecté sur cet appareil</span>
                        </label>

                        <button type="submit" id="bouton-connexion"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-lg border-0 bg-mobembo-vert px-6 py-3.5 text-sm font-semibold text-white hover:bg-mobembo-fonce focus:outline-none focus-visible:ring-2 focus-visible:ring-mobembo-clair focus-visible:ring-offset-2 transition-colors disabled:opacity-80 disabled:cursor-wait">
                            <span id="bouton-texte">Se connecter</span>
                            <i id="bouton-icone" class="hidden"></i>
                        </button>
                    </form>

                    <p class="mt-8 text-[11px] text-slate-400">
                        &copy; {{ date('Y') }} Mobembo Line — Tous droits réservés.
                    </p>
                </div>
            </section>
        </div>
    </main>

    <script>
        // Afficher / masquer le mot de passe
        const mdp = document.getElementById('password');
        const voirMdp = document.getElementById('voir-mdp');
        voirMdp.addEventListener('click', function () {
            const visible = mdp.type === 'password';
            mdp.type = visible ? 'text' : 'password';
            document.getElementById('eye-icon').className = 'fa-solid ' + (visible ? 'fa-eye-slash' : 'fa-eye') + ' text-sm';
            voirMdp.setAttribute('aria-pressed', visible);
            voirMdp.setAttribute('aria-label', visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
            mdp.focus();
        });

        // Avertir si la touche Majuscules est verrouillée
        const alerteMaj = document.getElementById('alerte-majuscules');
        ['keydown', 'keyup'].forEach(function (evt) {
            mdp.addEventListener(evt, function (e) {
                if (e.getModifierState) alerteMaj.classList.toggle('hidden', !e.getModifierState('CapsLock'));
            });
        });
        mdp.addEventListener('blur', function () { alerteMaj.classList.add('hidden'); });

        // État « connexion en cours » (évite le double envoi)
        document.getElementById('form-connexion').addEventListener('submit', function () {
            const bouton = document.getElementById('bouton-connexion');
            bouton.disabled = true;
            document.getElementById('bouton-texte').textContent = 'Connexion en cours…';
            document.getElementById('bouton-icone').className = 'fa-solid fa-circle-notch fa-spin text-xs';
        });
    </script>
</body>
</html>
