<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Administration - Mobembo Line</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="h-full flex overflow-hidden font-sans antialiased text-slate-900">

    <aside class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 bg-slate-950 text-white">
        <div class="flex items-center justify-center h-16 px-4 bg-slate-900 border-b border-slate-800">
            <span class="font-bold text-lg tracking-wide uppercase text-white">Mobembo <span class="text-amber-500">SuperAdmin</span></span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-6 flex flex-col">
            <a href="#" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg bg-amber-500 text-slate-950 font-semibold">
                <i class="fa-solid fa-building-shield w-6 text-lg"></i>
                Gestion des Agences
            </a>

            <!-- ⭐ Bouton de déconnexion (en bas de la sidebar) -->
            <div class="mt-auto pt-6 border-t border-slate-800">
                <form action="{{ route('unified.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg text-slate-300 hover:bg-red-900/40 hover:text-red-300 transition-all">
                        <i class="fa-solid fa-power-off w-6 text-lg"></i>
                        Se déconnecter
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col md:pl-64 overflow-hidden">
        <header class="flex items-center justify-between h-16 px-6 bg-white border-b border-slate-200 z-10">
            <h1 class="text-xl font-bold text-slate-800">Contrôle Global des Agences Partenaires</h1>
            <span class="bg-red-50 text-red-700 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider">
                Super Administrateur
            </span>
        </header>

        <main class="flex-1 overflow-y-auto bg-slate-50 p-6 md:p-8">

            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg">
                    <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i>{{ session('error') }}
                </div>
            @endif

            <!-- FORMULAIRE : AJOUTER UNE AGENCE -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
                <h2 class="text-lg font-bold text-slate-800 mb-4"><i class="fa-solid fa-plus-circle text-amber-500 mr-2"></i> Enregistrer une nouvelle agence</h2>
                <form action="{{ route('superadmin.agences.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom de l'agence</label>
                        <input type="text" name="nom_agence" required placeholder="Ex: Mikalou Express" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Date de partenariat</label>
                        <input type="date" name="date_partenariat" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-slate-900 text-amber-400 font-semibold py-2 px-4 rounded-lg hover:bg-slate-800 transition-colors">
                            Créer l'Agence
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABLEAU : LISTE DES AGENCES -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-bold text-slate-800">Liste des Agences ({{ $agences->count() }})</h2>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 text-xs uppercase tracking-wider">
                            <th class="py-3 px-6">Nom de l'agence</th>
                            <th class="py-3 px-6">Date Partenariat</th>
                            <th class="py-3 px-6">Agents Enregistrés</th>
                            <th class="py-3 px-6">Bus Attribués</th>
                            <th class="py-3 px-6">Statut Global</th>
                            <th class="py-3 px-6 text-right">Actions de Contrôle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-sm">
                        @forelse($agences as $agence)
                            <tr>
                                <td class="py-4 px-6 font-semibold text-slate-800">{{ $agence->nom_agence }}</td>
                                <td class="py-4 px-6 text-slate-600">{{ $agence->date_partenariat }}</td>
                                <td class="py-4 px-6"><span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full text-xs font-bold">{{ $agence->agents_count }} agents</span></td>
                                <td class="py-4 px-6"><span class="bg-purple-50 text-purple-700 px-2.5 py-1 rounded-full text-xs font-bold">{{ $agence->bus_count }} bus</span></td>
                                <td class="py-4 px-6">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $agence->statut === 'Actif' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $agence->statut }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <form action="{{ route('superadmin.agences.toggleStatus', $agence->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('{{ $agence->statut === 'Actif' ? 'Bloquer cette agence ? Son administrateur et ses agents seront déconnectés.' : 'Réactiver cette agence ?' }}');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-lg {{ $agence->statut === 'Actif' ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}">
                                            <i class="fa-solid {{ $agence->statut === 'Actif' ? 'fa-lock' : 'fa-lock-open' }} mr-1"></i>
                                            {{ $agence->statut === 'Actif' ? 'Bloquer l\'agence' : 'Activer l\'agence' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400">Aucune agence enregistrée pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </main>
    </div>
</body>
</html>