@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div>
        <h1 class="text-2xl font-black text-slate-900">Paramètres Généraux</h1>
        <p class="text-sm text-slate-500 mt-1">
            Configurez les paramètres de Canadian Airways
        </p>
    </div>

    {{-- Message succès --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="border-b border-slate-200">
            <nav class="flex gap-2 px-6 pt-4 flex-wrap" id="tabs-nav">
                <button type="button" data-tab="general"
                        class="tab-btn px-4 py-3 text-sm font-semibold text-[#C62828] border-b-2 border-[#C62828] transition">
                    <i class="fa-solid fa-building mr-2"></i> Général
                </button>
                <button type="button" data-tab="reservations"
                        class="tab-btn px-4 py-3 text-sm font-semibold text-slate-500 border-b-2 border-transparent hover:text-slate-700 transition">
                    <i class="fa-solid fa-ticket mr-2"></i> Réservations
                </button>
                <button type="button" data-tab="paiements"
                        class="tab-btn px-4 py-3 text-sm font-semibold text-slate-500 border-b-2 border-transparent hover:text-slate-700 transition">
                    <i class="fa-solid fa-credit-card mr-2"></i> Paiements
                </button>
                <button type="button" data-tab="notifications"
                        class="tab-btn px-4 py-3 text-sm font-semibold text-slate-500 border-b-2 border-transparent hover:text-slate-700 transition">
                    <i class="fa-solid fa-bell mr-2"></i> Notifications
                </button>
            </nav>
        </div>

        <form action="{{ route('canadian.parametres.update') }}" method="POST" class="p-6">
            @csrf

            {{-- Onglet Général --}}
            <div id="tab-general" class="tab-content space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Nom de la compagnie
                        </label>
                        <input type="text" name="nom_compagnie"
                               value="{{ old('nom_compagnie', $parametres['nom_compagnie'] ?? 'Canadian Airways') }}"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Email de contact
                        </label>
                        <input type="email" name="email_contact"
                               value="{{ old('email_contact', $parametres['email_contact'] ?? 'contact@canadianairways.com') }}"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Téléphone
                        </label>
                        <input type="text" name="telephone"
                               value="{{ old('telephone', $parametres['telephone'] ?? '06 000 0000') }}"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Devise
                        </label>
                        <select name="devise"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                            @php $devise = old('devise', $parametres['devise'] ?? 'FCFA'); @endphp
                            <option value="FCFA" {{ $devise == 'FCFA' ? 'selected' : '' }}>FCFA (Franc CFA)</option>
                            <option value="EUR" {{ $devise == 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                            <option value="USD" {{ $devise == 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Adresse
                        </label>
                        <input type="text" name="adresse"
                               value="{{ old('adresse', $parametres['adresse'] ?? 'Aéroport Maya-Maya, Brazzaville, Congo') }}"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                    </div>
                </div>
            </div>

            {{-- Onglet Réservations --}}
            <div id="tab-reservations" class="tab-content space-y-5 hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Délai d'annulation (heures avant le vol)
                        </label>
                        <input type="number" name="delai_annulation"
                               value="{{ old('delai_annulation', $parametres['delai_annulation'] ?? 24) }}"
                               min="0"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Frais d'annulation (%)
                        </label>
                        <input type="number" name="frais_annulation"
                               value="{{ old('frais_annulation', $parametres['frais_annulation'] ?? 10) }}"
                               min="0" max="100"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                    <div>
                        <p class="font-semibold text-slate-800 text-sm">Annulation automatique</p>
                        <p class="text-xs text-slate-500">Annuler les réservations non payées</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="annulation_auto" value="1" class="sr-only peer"
                               {{ old('annulation_auto', $parametres['annulation_auto'] ?? '0') == '1' ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-checked:bg-[#C62828] rounded-full transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                    </label>
                </div>
            </div>

            {{-- Onglet Paiements --}}
            <div id="tab-paiements" class="tab-content space-y-5 hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Seuil minimum (FCFA)
                        </label>
                        <input type="number" name="paiement_min"
                               value="{{ old('paiement_min', $parametres['paiement_min'] ?? 1000) }}"
                               min="0"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Seuil maximum (FCFA)
                        </label>
                        <input type="number" name="paiement_max"
                               value="{{ old('paiement_max', $parametres['paiement_max'] ?? 500000) }}"
                               min="0"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                    <div>
                        <p class="font-semibold text-slate-800 text-sm">Paiement à l'embarquement</p>
                        <p class="text-xs text-slate-500">Autoriser les paiements en espèces</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="paiement_especes" value="1" class="sr-only peer"
                               {{ old('paiement_especes', $parametres['paiement_especes'] ?? '1') == '1' ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-checked:bg-[#C62828] rounded-full transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                    <div>
                        <p class="font-semibold text-slate-800 text-sm">Mobile Money actif</p>
                        <p class="text-xs text-slate-500">MTN MoMo & Airtel Money</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="paiement_mobile" value="1" class="sr-only peer"
                               {{ old('paiement_mobile', $parametres['paiement_mobile'] ?? '1') == '1' ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-checked:bg-[#C62828] rounded-full transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                    </label>
                </div>
            </div>

            {{-- Onglet Notifications --}}
            <div id="tab-notifications" class="tab-content space-y-5 hidden">
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                    <div>
                        <p class="font-semibold text-slate-800 text-sm">SMS de confirmation</p>
                        <p class="text-xs text-slate-500">Envoyer un SMS après chaque réservation</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="sms_confirmation" value="1" class="sr-only peer"
                               {{ old('sms_confirmation', $parametres['sms_confirmation'] ?? '1') == '1' ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-checked:bg-[#C62828] rounded-full transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                    <div>
                        <p class="font-semibold text-slate-800 text-sm">Rappel avant vol</p>
                        <p class="text-xs text-slate-500">SMS 24h avant le départ</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="rappel_vol" value="1" class="sr-only peer"
                               {{ old('rappel_vol', $parametres['rappel_vol'] ?? '1') == '1' ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-checked:bg-[#C62828] rounded-full transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Message de bienvenue
                    </label>
                    <textarea name="message_bienvenue" rows="3"
                              class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#C62828] focus:outline-none resize-none">{{ old('message_bienvenue', $parametres['message_bienvenue'] ?? 'Bienvenue chez Canadian Airways !') }}</textarea>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-200">
                <button type="reset"
                        class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-lg text-sm font-semibold transition">
                    Annuler
                </button>
                <button type="submit"
                        class="px-6 py-2.5 bg-gradient-to-r from-[#C62828] to-[#8E0000] hover:from-[#E53935] hover:to-[#C62828] text-white rounded-lg text-sm font-semibold shadow-lg shadow-red-500/20 transition">
                    <i class="fa-solid fa-save mr-2"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;

            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('text-[#C62828]', 'border-[#C62828]');
                b.classList.add('text-slate-500', 'border-transparent');
            });

            btn.classList.remove('text-slate-500', 'border-transparent');
            btn.classList.add('text-[#C62828]', 'border-[#C62828]');

            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            document.getElementById('tab-' + tab).classList.remove('hidden');
        });
    });
</script>
@endsection