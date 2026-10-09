@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Codes Promo</h1>
            <p class="text-sm text-slate-500 mt-1">
                Gérez les codes promotionnels et réductions
            </p>
        </div>
        <a href="{{ route('canadian.promos.create') }}"
           class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-[#C62828] to-[#8E0000] hover:from-[#E53935] hover:to-[#C62828] text-white font-semibold rounded-xl shadow-lg shadow-red-500/20 transition">
            <i class="fa-solid fa-plus mr-2"></i> Créer un code
        </a>
    </div>

    {{-- Message succès --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Message erreur --}}
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Cartes statistiques --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2.5 bg-emerald-100 text-emerald-600 rounded-lg">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Actifs</p>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $actifs ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2.5 bg-amber-100 text-amber-600 rounded-lg">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Expirés</p>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $expires ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2.5 bg-sky-100 text-sky-600 rounded-lg">
                    <i class="fa-solid fa-chart-simple"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Utilisations</p>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $utilisations ?? 0 }}</p>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Code</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Réduction</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Période</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Utilisations</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500">Statut</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($promos as $promo)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <span class="font-mono font-bold text-sm text-[#C62828] bg-red-50 px-3 py-1.5 rounded-lg">
                                {{ $promo->code }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="font-bold text-slate-900">
                                @if($promo->type_reduction === 'pourcentage')
                                    -{{ $promo->valeur }}%
                                @else
                                    -{{ number_format($promo->valeur, 0, ',', ' ') }} FCFA
                                @endif
                            </span>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-600">
                            <div>Du {{ $promo->date_debut ? \Carbon\Carbon::parse($promo->date_debut)->format('d/m/Y') : '—' }}</div>
                            <div>Au {{ $promo->date_fin ? \Carbon\Carbon::parse($promo->date_fin)->format('d/m/Y') : '—' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs font-semibold">
                                {{ $promo->utilisations ?? 0 }} / {{ $promo->limite_utilisation ?? '∞' }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @if($promo->actif ?? false)
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block mr-1"></span>
                                    Actif
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    Inactif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ route('canadian.promos.edit', $promo->id) }}"
                                   class="text-sky-600 hover:text-sky-800 transition" title="Modifier">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('canadian.promos.toggle', $promo->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="{{ $promo->actif ?? false ? 'text-amber-600 hover:text-amber-800' : 'text-emerald-600 hover:text-emerald-800' }} transition"
                                            title="{{ $promo->actif ?? false ? 'Désactiver' : 'Activer' }}">
                                        <i class="fa-solid fa-power-off"></i>
                                    </button>
                                </form>
                                <form action="{{ route('canadian.promos.destroy', $promo->id) }}" method="POST"
                                      class="inline" onsubmit="return confirm('Supprimer ce code promo ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 transition" title="Supprimer">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-4">
                                <i class="fa-solid fa-tags text-2xl text-slate-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700 mb-2">Aucun code promo</h3>
                            <p class="text-sm text-slate-500 mb-4">
                                Créez votre premier code promotionnel
                            </p>
                            <a href="{{ route('canadian.promos.create') }}"
                               class="inline-flex items-center px-4 py-2 bg-[#C62828] hover:bg-[#8E0000] text-white text-sm font-semibold rounded-lg transition">
                                <i class="fa-solid fa-plus mr-2"></i> Créer un code
                            </a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($promos) && method_exists($promos, 'hasPages') && $promos->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                {{ $promos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection