@extends('layouts.admin')

@section('title', 'Agences Partenaires')
@section('header_icon', 'fa-shield-halved')
@section('header_title', 'Supervision Réseau des Agences')
@section('header_subtitle', 'Contrôle des accès, blocage/activation et création des agences de transport')

@section('content')
<div class="space-y-6" x-data="{ formOpen: @json($errors->any()), searchQuery: '' }">

            @if($errors->any())
                <div class="p-5 bg-red-50 border border-red-200 text-red-700 rounded-2xl shadow-sm">
                    <div class="font-bold mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                        <span>Erreur lors de l'enregistrement de l'agence :</span>
                    </div>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 1. CARTES STATISTIQUES GLOBALES DU RÉSEAU --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <div class="card-hover bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Agences Partenaires</span>
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-building"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-slate-900">{{ $totalAgences }}</p>
                    <p class="text-xs text-slate-400 mt-2">Compagnies conventionnées</p>
                </div>

                <div class="card-hover bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Agences Actives</span>
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-emerald-600">{{ $agencesActives ?? 0 }}</p>
                    <p class="text-xs text-slate-400 mt-2">Autorisées sur la plateforme</p>
                </div>

                <div class="card-hover bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Flotte Nationale</span>
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-bus"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-slate-900">{{ $totalBus ?? 0 }}</p>
                    <p class="text-xs text-slate-400 mt-2">Bus enregistrés au total</p>
                </div>

                <div class="card-hover bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Agents Réseau</span>
                        <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-slate-900">{{ $totalAgents ?? 0 }}</p>
                    <p class="text-xs text-slate-400 mt-2">Guichetiers et contrôleurs</p>
                </div>

            </div>

            {{-- 2. BOUTON ACTION & FORMULAIRE COLLAPSIBLE D'AJOUT --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-7">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-plus-circle text-emerald-600"></i>
                            <span>Nouveau partenaire (bus ou aérien)</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Créez l'agence et générez immédiatement les identifiants de son administrateur.
                        </p>
                    </div>

                    <button @click="formOpen = !formOpen" 
                            type="button"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl font-bold text-sm transition-all shadow-sm"
                            :class="formOpen ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-[#124231] text-white hover:bg-[#0c2f23]'">
                        <i :class="formOpen ? 'fa-solid fa-chevron-up' : 'fa-solid fa-plus'"></i>
                        <span x-text="formOpen ? 'Masquer le formulaire' : '+ Enregistrer une agence'"></span>
                    </button>
                </div>

                <!-- FORMULAIRE ACCORDÉON -->
                <div x-show="formOpen" x-transition class="mt-6 pt-6 border-t border-slate-100" style="display: none;">
                    <form action="{{ route('superadmin.agences.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Section Agence -->
                        <div class="mb-6">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-building text-slate-400"></i> Informations du partenaire
                            </h3>
                            {{-- Type de partenaire : chacun aura l'espace de gestion de son métier --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4" x-data="{ type: @js(old('type_transport', 'bus')) }">
                                @foreach(['bus' => ['Compagnie de bus', 'fa-bus', 'Agences, bus, trajets, départs, agents d\'embarquement'], 'aerien' => ['Compagnie aérienne', 'fa-plane', 'Vols, flotte, classes tarifaires, réservations PNR']] as $valeur => [$titre, $icone, $detail])
                                    <label class="relative flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                           :class="type === '{{ $valeur }}' ? 'border-emerald-600 bg-emerald-50/60' : 'border-slate-200 hover:border-slate-300'">
                                        <input type="radio" name="type_transport" value="{{ $valeur }}" x-model="type" class="sr-only">
                                        <span class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0"
                                              :class="type === '{{ $valeur }}' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'">
                                            <i class="fa-solid {{ $icone }}"></i>
                                        </span>
                                        <span>
                                            <span class="block text-sm font-black text-slate-900">{{ $titre }}</span>
                                            <span class="block text-xs text-slate-500 mt-0.5">{{ $detail }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Nom du partenaire <span class="text-red-500">*</span></label>
                                    <input type="text" name="nom_agence" value="{{ old('nom_agence') }}" required placeholder="Ex: Mikalou Express" @input="window.dispatchEvent(new CustomEvent('nom-agence', { detail: $event.target.value }))" 
                                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Date de partenariat <span class="text-red-500">*</span></label>
                                    <input type="date" name="date_partenariat" value="{{ old('date_partenariat', date('Y-m-d')) }}" required 
                                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- Section Identité visuelle -->
                        <div class="mb-6 pt-5 border-t border-slate-100"
                             x-data="{ couleur: '{{ old('couleur', '#124231') }}', couleur2: '{{ old('couleur_secondaire', '#F9A825') }}', apercu: null, nom: @js(old('nom_agence', '')) }"
                             @nom-agence.window="nom = $event.detail">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1 flex items-center gap-2">
                                <i class="fa-solid fa-palette text-slate-400"></i> Identité visuelle
                            </h3>
                            <p class="text-xs text-slate-400 mb-4">L'administration de l'agence et sa page dans l'application prendront son logo et ses couleurs. L'agence pourra les modifier ensuite.</p>
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Logo de l'entreprise</label>
                                    <label class="flex items-center gap-3 p-3 bg-slate-50 border border-dashed border-slate-300 rounded-xl cursor-pointer hover:bg-white transition">
                                        <span class="w-14 h-14 rounded-xl bg-white border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                                            <template x-if="apercu"><img :src="apercu" class="w-full h-full object-cover" alt=""></template>
                                            <template x-if="!apercu"><i class="fa-solid fa-image text-slate-300 text-xl"></i></template>
                                        </span>
                                        <span class="text-xs font-semibold text-slate-500">JPG, PNG ou WebP, 2 Mo max.<br><span class="text-slate-700 font-bold">Choisir une image</span></span>
                                        <input type="file" name="logo" accept="image/jpeg,image/png,image/webp" class="hidden"
                                               @change="const f = $event.target.files[0]; apercu = f ? URL.createObjectURL(f) : null">
                                    </label>
                                    @error('logo')<p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="lg:col-span-1">
                                    @include('partials.choix-couleurs')
                                </div>
                                {{-- Aperçu du menu de l'administration --}}
                                <div class="rounded-2xl border border-slate-200 overflow-hidden">
                                    <div class="h-1.5" :style="'background: linear-gradient(90deg, ' + couleur + ', ' + couleur2 + ')'"></div>
                                    <div class="p-3 flex items-center gap-2.5 border-b border-slate-100">
                                        <span class="w-9 h-9 rounded-xl bg-white border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                                            <template x-if="apercu"><img :src="apercu" class="w-full h-full object-cover" alt=""></template>
                                            <template x-if="!apercu"><i class="fa-solid fa-bus text-sm" :style="'color:' + couleur"></i></template>
                                        </span>
                                        <span class="text-sm font-extrabold text-slate-900 truncate" x-text="nom || 'Nom de l\'agence'"></span>
                                    </div>
                                    <div class="p-3 space-y-1.5">
                                        <div class="px-3 py-2 rounded-xl text-white text-xs font-bold flex items-center gap-2" :style="'background: linear-gradient(135deg, ' + couleur + ', ' + couleur2 + ')'"><i class="fa-solid fa-gauge-high"></i> Tableau de bord</div>
                                        <div class="px-3 py-2 rounded-xl text-slate-500 text-xs font-bold flex items-center gap-2"><i class="fa-solid fa-calendar-days"></i> Planifications</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section Compte Admin -->
                        <div class="mb-6 pt-5 border-t border-slate-100">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-user-shield text-slate-400"></i> Compte Administrateur Assigné
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Nom de l'admin <span class="text-red-500">*</span></label>
                                    <input type="text" name="admin_nom" value="{{ old('admin_nom') }}" required placeholder="Ex: Moukala" 
                                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Prénom de l'admin <span class="text-red-500">*</span></label>
                                    <input type="text" name="admin_prenom" value="{{ old('admin_prenom') }}" required placeholder="Ex: Jean" 
                                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Téléphone de connexion <span class="text-red-500">*</span></label>
                                    <input type="text" name="admin_telephone" value="{{ old('admin_telephone') }}" required placeholder="Ex: 061234567" 
                                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
                                    <span class="text-[11px] text-slate-400 mt-1 block">Format : 05 ou 06 suivi de 7 chiffres</span>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Mot de passe initial <span class="text-red-500">*</span></label>
                                    <input type="password" name="admin_password" required placeholder="Au moins 8 caractères" 
                                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
                                    <span class="text-[11px] text-slate-400 mt-1 block">Min. 8 caractères sécurisés</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-3">
                            <button type="submit" 
                                    class="bg-[#124231] hover:bg-[#0c2f23] text-white font-bold py-3 px-7 rounded-2xl shadow-lg shadow-black/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
                                <i class="fa-solid fa-plus-circle text-white"></i>
                                <span>Créer l'Agence et Activer le Compte Admin</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. TABLEAU DES AGENCES AVEC RECHERCHE INSTANTANÉE --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-6 sm:px-8 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-slate-700"></i>
                            <span>Liste des Agences Partenaires ({{ $agences->total() }})</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Contrôlez les statuts et l'accès au réseau</p>
                        <div class="flex flex-wrap gap-1.5 mt-3">
                            @foreach(['' => 'Tous', 'bus' => 'Bus', 'aerien' => 'Aérien'] as $cle => $libelle)
                                <a href="{{ route('superadmin.agences.index', array_filter(['type' => $cle])) }}"
                                   class="text-xs font-bold px-3 py-1.5 rounded-full border transition-all {{ ($type ?? '') === $cle ? 'bg-[#124231] text-white border-[#124231]' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' }}">
                                    {{ $libelle }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Champ de recherche instantané (Alpine.js) -->
                    <div class="relative w-full sm:w-72">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                        <input type="text" 
                               x-model="searchQuery" 
                               placeholder="Rechercher une agence..." 
                               class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-emerald-600 focus:outline-none transition-all">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3.5 px-6">Partenaire</th>
                                <th class="py-3.5 px-6">Administrateur</th>
                                <th class="py-3.5 px-6">Agents Inscrits</th>
                                <th class="py-3.5 px-6">Bus / Vols</th>
                                <th class="py-3.5 px-6">Statut Réseau</th>
                                <th class="py-3.5 px-6 text-right">Action de Contrôle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($agences as $agence)
                                @php
                                    $monogram = strtoupper(substr($agence->nom_agence, 0, 2));
                                    $admin = $agence->admin;
                                @endphp
                                <tr x-show="searchQuery === '' || '{{ strtolower($agence->nom_agence) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($admin?->telephone ?? '') }}'.includes(searchQuery.toLowerCase())"
                                    class="hover:bg-slate-50/80 transition-colors">
                                    
                                    {{-- Agence --}}
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            @if($agence->logoMedia)
                                                <img src="{{ $agence->logoMedia->url() }}" alt="" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shrink-0">
                                            @else
                                                <div class="w-10 h-10 rounded-xl text-white font-extrabold text-xs flex items-center justify-center shrink-0 shadow-inner"
                                                     style="background: linear-gradient(135deg, {{ $agence->identite()['fond'] }}, {{ $agence->identite()['fonce'] }})">
                                                    {{ $monogram }}
                                                </div>
                                            @endif
                                            <div>
                                                <a href="{{ route('superadmin.agences.show', $agence) }}" class="font-extrabold text-slate-900 hover:text-emerald-700 hover:underline">{{ $agence->nom_agence }}</a>
                                                <div class="text-[11px] text-slate-400 flex items-center gap-1.5 flex-wrap">
                                                    @if($agence->estAerien())
                                                        <span class="font-bold text-sky-700 bg-sky-50 px-1.5 py-0.5 rounded"><i class="fa-solid fa-plane text-[9px]"></i> Aérien</span>
                                                    @else
                                                        <span class="font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded"><i class="fa-solid fa-bus text-[9px]"></i> Bus</span>
                                                    @endif
                                                    <span>Partenaire depuis le {{ \Carbon\Carbon::parse($agence->date_partenariat)->format('d/m/Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Administrateur --}}
                                    <td class="py-4 px-6">
                                        @if($admin)
                                            <div class="font-bold text-xs text-slate-800">{{ $admin->prenom }} {{ $admin->nom }}</div>
                                            <div class="text-[11px] text-slate-500 font-mono flex items-center gap-1 mt-0.5">
                                                <i class="fa-solid fa-phone text-[9px] text-slate-400"></i>
                                                <span>{{ $admin->telephone }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Aucun admin</span>
                                        @endif
                                    </td>

                                    {{-- Agents --}}
                                    <td class="py-4 px-6">
                                        @if($agence->estAerien())
                                            <span class="text-xs text-slate-400">—</span>
                                        @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                            <i class="fa-solid fa-user-tie text-[10px]"></i>
                                            {{ $agence->agents_count }} agents
                                        </span>
                                        @endif
                                    </td>

                                    {{-- Bus --}}
                                    <td class="py-4 px-6">
                                        @if($agence->estAerien())
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700">
                                                <i class="fa-solid fa-plane text-[10px]"></i>
                                                {{ $agence->vols_count }} vols
                                            </span>
                                        @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700">
                                            <i class="fa-solid fa-bus text-[10px]"></i>
                                            {{ $agence->bus_count }} bus
                                        </span>
                                        @endif
                                    </td>

                                    {{-- Statut Réseau --}}
                                    <td class="py-4 px-6">
                                        @if($agence->statut === 'Actif')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                Actif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-red-50 text-red-700 border border-red-200/60">
                                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                                Bloqué
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="py-4 px-6 text-right">
                                        <form action="{{ route('superadmin.agences.toggleStatus', $agence->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('{{ $agence->statut === 'Actif' ? 'Bloquer cette agence ? Les accès de son administrateur et de ses agents seront révoqués.' : 'Réactiver cette agence sur le réseau ?' }}');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-1.5 rounded-xl transition-all {{ $agence->statut === 'Actif' ? 'bg-red-50 text-red-600 hover:bg-red-100 hover:shadow-sm' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:shadow-sm' }}">
                                                <i class="fa-solid {{ $agence->statut === 'Actif' ? 'fa-lock' : 'fa-lock-open' }} text-[11px]"></i>
                                                <span>{{ $agence->statut === 'Actif' ? 'Bloquer' : 'Activer' }}</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-building-circle-xmark text-4xl mb-3 text-slate-300"></i>
                                        <p class="text-sm font-semibold text-slate-600">Aucune agence enregistrée.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($agences, 'links') && $agences->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
                        {{ $agences->links() }}
                    </div>
                @endif
            </div>

</div>
@endsection
