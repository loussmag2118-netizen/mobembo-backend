@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- En-tête --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('canadian.vols.index') }}"
           class="p-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-black text-slate-900">
                Vol <span class="font-mono text-[#C62828]">{{ $vol->numero_vol }}</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $vol->origine }} → {{ $vol->destination }}
            </p>
        </div>
        <a href="{{ route('canadian.vols.edit', $vol->id) }}"
           class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-[#C62828] to-[#8E0000] hover:from-[#E53935] hover:to-[#C62828] text-white font-semibold rounded-xl shadow-lg shadow-red-500/20 transition">
            <i class="fa-solid fa-pen mr-2"></i> Modifier
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Bannière vol --}}
    <div class="bg-gradient-to-br from-[#C62828] to-[#8E0000] rounded-2xl p-8 text-white shadow-lg shadow-red-500/20 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <i class="fa-solid fa-plane absolute right-8 top-1/2 -translate-y-1/2 text-8xl"></i>
        </div>
        <div class="relative flex items-center justify-between flex-wrap gap-6">
            <div>
                <p class="text-xs uppercase tracking-wider text-white/70 mb-1">Départ</p>
                <p class="text-2xl font-black">{{ $vol->origine }}</p>
                <p class="text-sm text-white/80 mt-1">
                    {{ $vol->date_depart ? $vol->date_depart->format('d/m/Y H:i') : '—' }}
                </p>
                @if($vol->ville_depart)
                    <p class="text-xs text-white/60 mt-0.5">
                        {{ $vol->ville_depart }} @if($vol->ville_depart_iata) ({{ $vol->ville_depart_iata }}) @endif
                    </p>
                @endif
            </div>
            <div class="flex flex-col items-center">
                <i class="fa-solid fa-plane text-3xl opacity-80"></i>
                <div class="w-24 h-px bg-white/40 my-2"></div>
                <p class="text-xs text-white/70">{{ $vol->type_vol === 'escale' ? 'Avec escale' : 'Direct' }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs uppercase tracking-wider text-white/70 mb-1">Arrivée</p>
                <p class="text-2xl font-black">{{ $vol->destination }}</p>
                <p class="text-sm text-white/80 mt-1">
                    {{ $vol->date_arrivee ? $vol->date_arrivee->format('d/m/Y H:i') : '—' }}
                </p>
                @if($vol->ville_arrivee)
                    <p class="text-xs text-white/60 mt-0.5">
                        {{ $vol->ville_arrivee }} @if($vol->ville_arrivee_iata) ({{ $vol->ville_arrivee_iata }}) @endif
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Stats rapides --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Prix unitaire</p>
            <p class="text-xl font-black text-slate-900">
                {{ number_format($vol->prix ?? 0, 0, ',', ' ') }}
            </p>
            <p class="text-xs text-slate-500 mt-1">FCFA</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Places</p>
            <p class="text-xl font-black text-slate-900">
                {{ $vol->places_disponibles ?? 0 }}
            </p>
            <p class="text-xs text-slate-500 mt-1">Disponibles</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Type</p>
            <p class="text-xl font-black text-slate-900">
                {{ $vol->type_vol === 'escale' ? 'Escale' : 'Direct' }}
            </p>
            <p class="text-xs text-slate-500 mt-1">Catégorie</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Statut</p>
            @php
                $badges = [
                    'programme' => 'bg-emerald-50 text-emerald-700',
                    'en_vol'    => 'bg-sky-50 text-sky-700',
                    'atterri'   => 'bg-slate-100 text-slate-700',
                    'annule'    => 'bg-red-50 text-[#C62828]',
                ];
                $labels = [
                    'programme' => 'Programmé',
                    'en_vol'    => 'En vol',
                    'atterri'   => 'Atterri',
                    'annule'    => 'Annulé',
                ];
            @endphp
            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $badges[$vol->statut] ?? 'bg-slate-100 text-slate-700' }}">
                {{ $labels[$vol->statut] ?? $vol->statut }}
            </span>
        </div>
    </div>

    {{-- Informations détaillées --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-[#C62828]"></i>
                Détails du vol
            </h2>
        </div>
        <div class="divide-y divide-slate-100">
            <div class="px-6 py-4 flex justify-between">
                <span class="text-sm text-slate-500">Compagnie</span>
                <span class="text-sm font-semibold text-slate-900">
                    {{ $vol->compagnie ?? 'Canadian Airways' }}
                </span>
            </div>
            <div class="px-6 py-4 flex justify-between">
                <span class="text-sm text-slate-500">Numéro de vol</span>
                <span class="text-sm font-mono font-semibold text-[#C62828]">
                    {{ $vol->numero_vol }}
                </span>
            </div>
            <div class="px-6 py-4 flex justify-between">
                <span class="text-sm text-slate-500">Durée</span>
                <span class="text-sm font-semibold text-slate-900">
                    {{ $vol->duree_formatee ?: '—' }}
                </span>
            </div>
            <div class="px-6 py-4 flex justify-between">
                <span class="text-sm text-slate-500">Créé le</span>
                <span class="text-sm font-semibold text-slate-900">
                    {{ $vol->created_at ? $vol->created_at->format('d/m/Y à H:i') : '—' }}
                </span>
            </div>
            <div class="px-6 py-4 flex justify-between">
                <span class="text-sm text-slate-500">Dernière mise à jour</span>
                <span class="text-sm font-semibold text-slate-900">
                    {{ $vol->updated_at ? $vol->updated_at->format('d/m/Y à H:i') : '—' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Actions dangereuses --}}
    <div class="bg-white rounded-2xl border border-red-200 shadow-sm p-6">
        <h3 class="text-sm font-bold text-red-800 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Zone dangereuse
        </h3>
        <div class="flex flex-wrap gap-3">
            <form action="{{ route('canadian.vols.dupliquer', $vol->id) }}" method="POST">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-violet-50 hover:bg-violet-100 text-violet-700 rounded-lg text-sm font-semibold transition">
                    <i class="fa-solid fa-copy mr-2"></i> Dupliquer ce vol
                </button>
            </form>
            <form action="{{ route('canadian.vols.destroy', $vol->id) }}" method="POST"
                  onsubmit="return confirm('Supprimer définitivement ce vol ?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 bg-red-50 hover:bg-red-100 text-[#C62828] rounded-lg text-sm font-semibold transition">
                    <i class="fa-solid fa-trash mr-2"></i> Supprimer le vol
                </button>
            </form>
        </div>
    </div>
</div>
@endsection