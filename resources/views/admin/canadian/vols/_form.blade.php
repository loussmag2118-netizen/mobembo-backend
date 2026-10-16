@php
    $champ = 'w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#124231] focus:outline-none';
    $etiquette = 'block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5';
    $idDepart = old('aeroport_depart', $aeroports->firstWhere('code_iata', $vol->ville_depart_iata)?->id);
    $idArrivee = old('aeroport_arrivee', $aeroports->firstWhere('code_iata', $vol->ville_arrivee_iata)?->id);
@endphp

@include('admin.canadian.partials.erreurs')

@if($aeroports->count() < 2)
    <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl text-sm">
        <i class="fa-solid fa-circle-info mr-1"></i>
        Ajoutez au moins deux aéroports avant de programmer un vol.
        <a href="{{ route('canadian.villes.create') }}" class="font-bold underline">Ajouter un aéroport</a>
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        {{-- Itinéraire --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
            <h2 class="text-base font-black text-slate-900 mb-4 flex items-center gap-2"><i class="fa-solid fa-route text-slate-400"></i> Itinéraire</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $etiquette }}">Aéroport de départ *</label>
                    <select name="aeroport_depart" required class="{{ $champ }}">
                        <option value="">Choisir…</option>
                        @foreach($aeroports as $a)
                            <option value="{{ $a->id }}" @selected((int) $idDepart === $a->id)>{{ $a->nom }} ({{ $a->code_iata }}) — {{ $a->pays }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $etiquette }}">Aéroport d'arrivée *</label>
                    <select name="aeroport_arrivee" required class="{{ $champ }}">
                        <option value="">Choisir…</option>
                        @foreach($aeroports as $a)
                            <option value="{{ $a->id }}" @selected((int) $idArrivee === $a->id)>{{ $a->nom }} ({{ $a->code_iata }}) — {{ $a->pays }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-data="{ type: @js(old('type_vol', $vol->type_vol ?? 'direct')) }" class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $etiquette }}">Type de vol *</label>
                        <select name="type_vol" x-model="type" class="{{ $champ }}">
                            <option value="direct">Direct</option>
                            <option value="escale">Avec escale</option>
                        </select>
                    </div>
                    <div x-show="type === 'escale'">
                        <label class="{{ $etiquette }}">Escales</label>
                        <input name="escales" value="{{ old('escales', implode(', ', $vol->escales ?? [])) }}" placeholder="Ex : Libreville, Douala" class="{{ $champ }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Horaires --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
            <h2 class="text-base font-black text-slate-900 mb-4 flex items-center gap-2"><i class="fa-solid fa-clock text-slate-400"></i> Horaires (heure de Brazzaville)</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="{{ $etiquette }}">Date de départ *</label>
                    <input type="date" name="date_depart" required value="{{ old('date_depart', $vol->date_depart?->format('Y-m-d')) }}" class="{{ $champ }}">
                </div>
                <div>
                    <label class="{{ $etiquette }}">Heure de départ *</label>
                    <input type="time" name="heure_depart" required value="{{ old('heure_depart', $vol->date_depart?->format('H:i')) }}" class="{{ $champ }}">
                </div>
                <div>
                    <label class="{{ $etiquette }}">Date d'arrivée *</label>
                    <input type="date" name="date_arrivee" required value="{{ old('date_arrivee', $vol->date_arrivee?->format('Y-m-d')) }}" class="{{ $champ }}">
                </div>
                <div>
                    <label class="{{ $etiquette }}">Heure d'arrivée *</label>
                    <input type="time" name="heure_arrivee" required value="{{ old('heure_arrivee', $vol->date_arrivee?->format('H:i')) }}" class="{{ $champ }}">
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Vente --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2"><i class="fa-solid fa-ticket text-slate-400"></i> Vente</h2>
            <div>
                <label class="{{ $etiquette }}">Numéro de vol *</label>
                <input name="numero_vol" required maxlength="20" value="{{ old('numero_vol', $vol->numero_vol) }}" placeholder="Ex : CA101" class="{{ $champ }} font-mono font-bold uppercase">
            </div>
            <div>
                <label class="{{ $etiquette }}">Avion</label>
                <select name="flotte_id" class="{{ $champ }}">
                    <option value="">Non précisé</option>
                    @foreach($avions as $avion)
                        <option value="{{ $avion->id }}" @selected((int) old('flotte_id', $vol->flotte_id) === $avion->id)>
                            {{ $avion->modele ?: $avion->nom_appareil }}{{ $avion->immatriculation ? ' · ' . $avion->immatriculation : '' }} ({{ $avion->capacite_places }} sièges)
                        </option>
                    @endforeach
                </select>
                @if($avions->isEmpty())
                    <p class="text-xs text-slate-400 mt-1">Aucun avion actif. <a href="{{ route('canadian.flotte.create') }}" class="font-bold underline">Ajouter un avion</a></p>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $etiquette }}">Prix (FCFA) *</label>
                    <input type="number" name="prix" required min="1" step="1" value="{{ old('prix', $vol->prix !== null ? (int) $vol->prix : null) }}" class="{{ $champ }}">
                </div>
                <div>
                    <label class="{{ $etiquette }}">Places en vente *</label>
                    <input type="number" name="places_totales" required min="1" max="900" value="{{ old('places_totales', $vol->places_totales ?? ($vol->exists ? $vol->places_disponibles : null)) }}" class="{{ $champ }}">
                </div>
            </div>
            @if($vol->exists)
                <p class="text-xs text-slate-500 bg-slate-50 rounded-xl p-3">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Places restantes : <b>{{ $vol->places_disponibles }}</b>. Elles se recalculent automatiquement à partir des réservations.
                </p>
            @endif
            <div>
                <label class="{{ $etiquette }}">Statut *</label>
                <select name="statut" class="{{ $champ }}">
                    @foreach($statuts as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(old('statut', $vol->statut) === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">Seuls les vols « Programmé » à venir sont proposés aux clients.</p>
            </div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('canadian.vols.index') }}" class="flex-1 text-center py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm">Annuler</a>
            <button class="flex-1 py-3 rounded-xl bg-[#124231] hover:bg-[#0c2f23] text-white font-bold text-sm shadow-sm">{{ $vol->exists ? 'Enregistrer' : 'Créer le vol' }}</button>
        </div>
    </div>
</div>
