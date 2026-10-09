@extends('layouts.admin')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-slate-800">
        <i class="fa-solid fa-bell text-sky-600 mr-2"></i>
        Notifications & SMS
    </h1>
</div>

@if(session('success'))
    <div class="mb-5 p-4 rounded-lg bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm">
        <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
    </div>
@endif

<!-- ═══════════════════════════════════════════════════════ -->
<!-- ENVOI DE NOTIFICATION                                  -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="bg-white shadow ring-1 ring-slate-200 rounded-xl p-6 mb-6">
    <h2 class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">
        <i class="fa-solid fa-paper-plane text-sky-600 mr-2"></i>
        Envoyer une notification
    </h2>

    <form action="{{ route('canadian.notifications.store') }}" method="POST" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Client -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    Destinataire
                </label>
                <select name="client_id" required
                        class="w-full rounded-lg border-slate-300 py-2.5 px-3 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-sky-600 sm:text-sm">
                    <option value="">-- Tous les clients --</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->nom }} {{ $client->prenom ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Type -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    Type de notification
                </label>
                <select name="type" required
                        class="w-full rounded-lg border-slate-300 py-2.5 px-3 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-sky-600 sm:text-sm">
                    <option value="info"    {{ old('type') == 'info'    ? 'selected' : '' }}>ℹ️ Information</option>
                    <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>✅ Succès</option>
                    <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>⚠️ Avertissement</option>
                    <option value="promo"   {{ old('type') == 'promo'   ? 'selected' : '' }}>🎁 Promotion</option>
                </select>
            </div>
        </div>

        <!-- Titre -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Titre
            </label>
            <input type="text" name="titre" value="{{ old('titre') }}"
                   placeholder="Ex: Promo spéciale"
                   required
                   class="w-full rounded-lg border-slate-300 py-2.5 px-3 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-sky-600 sm:text-sm">
        </div>

        <!-- Message -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Message
            </label>
            <textarea name="message" rows="3"
                      placeholder="Écrivez le contenu de la notification..."
                      required
                      class="w-full rounded-lg border-slate-300 py-2.5 px-3 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-sky-600 sm:text-sm">{{ old('message') }}</textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium">
                <i class="fa-solid fa-paper-plane mr-2"></i> Envoyer
            </button>
        </div>
    </form>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- HISTORIQUE                                             -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="bg-white shadow ring-1 ring-slate-200 rounded-xl overflow-hidden">
    <div class="p-6 pb-4 border-b border-slate-100">
        <h2 class="text-lg font-bold text-slate-800">
            <i class="fa-solid fa-clock-rotate-left text-sky-600 mr-2"></i>
            Historique récent ({{ $historique->count() }})
        </h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Titre</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Message</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Type</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500">Date</th>
                    <th class="p-4 text-xs font-bold uppercase text-slate-500 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($historique as $notif)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 text-sm font-semibold text-slate-800">
                            {{ $notif->titre ?? '—' }}
                        </td>
                        <td class="p-4 text-sm text-slate-600">
                            {{ Str::limit($notif->message ?? '', 80) }}
                        </td>
                        <td class="p-4 text-sm">
                            @php
                                $badges = [
                                    'info'    => ['bg-blue-50 text-blue-700',     'ℹ️ Info'],
                                    'success' => ['bg-emerald-50 text-emerald-700','✅ Succès'],
                                    'warning' => ['bg-amber-50 text-amber-700',   '⚠️ Alerte'],
                                    'promo'   => ['bg-purple-50 text-purple-700', '🎁 Promo'],
                                ];
                                [$class, $label] = $badges[$notif->type ?? 'info'] ?? ['bg-slate-100 text-slate-700', '•'];
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $class }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="p-4 text-sm text-slate-500">
                            {{ $notif->created_at ? $notif->created_at->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="p-4 text-right">
                            <form action="{{ route('canadian.notifications.destroy', $notif->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Supprimer cette notification ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                    <i class="fa-solid fa-trash-can mr-1"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-400">
                            <i class="fa-solid fa-bell-slash text-4xl mb-3 text-slate-300"></i>
                            <p class="text-base">Aucune notification envoyée pour le moment.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection