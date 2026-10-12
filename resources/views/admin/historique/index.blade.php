@extends('layouts.admin')

@section('content')
<div class="space-y-7">

    {{-- EN-TÊTE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-3 rounded-2xl bg-slate-200 text-slate-700 border border-slate-300 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
                <span>Journal des Opérations</span>
            </h1>
            <p class="text-slate-500 text-base mt-2 ml-[64px]">
                Traçabilité des actions effectuées par les agents et administrateurs.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-4 py-2.5 rounded-2xl text-sm font-bold bg-slate-100 text-slate-700 border border-slate-200 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-slate-500"></i>
                Total : {{ method_exists($operations, 'total') ? $operations->total() : $operations->count() }} opération(s)
            </span>
            <button onclick="location.reload()" class="w-11 h-11 rounded-2xl bg-white border border-slate-200/80 text-slate-600 hover:text-slate-900 hover:bg-slate-50 flex items-center justify-center text-sm shadow-sm transition-all" title="Actualiser">
                <i class="fa-solid fa-rotate-right text-base"></i>
            </button>
        </div>
    </div>

    {{-- TABLEAU STYLÉ --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-black uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-8">Horodatage</th>
                        <th class="py-4 px-8">Agent / Utilisateur</th>
                        <th class="py-4 px-8">Action / Opération</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($operations as $op)
                        <tr class="hover:bg-slate-50/60 transition-colors group">
                            <td class="py-5 px-8">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-sm shrink-0 group-hover:bg-slate-200 transition-colors">
                                        <i class="fa-regular fa-clock"></i>
                                    </div>
                                    <span class="font-mono text-sm font-bold text-slate-600">
                                        {{ $op->created_at ? \Carbon\Carbon::parse($op->created_at)->format('d/m/Y H:i:s') : '—' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-5 px-8">
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-sm text-slate-700 font-bold">
                                    <i class="fa-solid fa-user text-slate-400 text-xs"></i>
                                    {{ $op->agent?->nom ?? $op->utilisateur?->nom ?? 'Agent #'.($op->agent_id ?? $op->utilisateur_id ?? '?') }}
                                </span>
                            </td>
                            <td class="py-5 px-8">
                                <span class="px-4 py-2 rounded-full text-sm font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                                    {{ $op->action ?? $op->description ?? 'Opération système' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-20 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 rounded-3xl bg-slate-100 flex items-center justify-center">
                                        <i class="fa-solid fa-clock-rotate-left text-4xl text-slate-300"></i>
                                    </div>
                                    <div>
                                        <p class="text-base font-bold text-slate-600">Aucune opération enregistrée pour le moment.</p>
                                        <p class="text-sm text-slate-400 mt-1">Les actions des agents apparaîtront ici.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($operations, 'links'))
            <div class="p-5 border-t border-slate-100">
                {{ $operations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection