@extends('public._page')
@section('titre', 'Politique de confidentialité')
@section('contenu')
<div class="texte">
    <h1 class="text-2xl font-extrabold text-slate-900">Politique de confidentialité</h1>
    <p class="text-sm">Dernière mise à jour : {{ \Carbon\Carbon::parse('2026-10-15')->translatedFormat('d F Y') }}</p>

    <p>L'application <strong>Mobembo Line</strong> permet de réserver et payer des billets de bus auprès d'agences de transport partenaires en République du Congo. Cette page explique quelles données nous collectons, pourquoi, et comment les supprimer.</p>

    <h2>1. Données collectées</h2>
    <ul>
        <li><strong>Compte</strong> : nom, prénom, numéro de téléphone et mot de passe (enregistré chiffré, jamais lisible).</li>
        <li><strong>Réservations</strong> : nom des passagers, catégorie (adulte ou enfant, âge d'un enfant), trajet, date, siège et référence du billet.</li>
        <li><strong>Paiements</strong> : opérateur (MTN Mobile Money ou Airtel Money), numéro utilisé pour payer, montant et référence de la transaction. Votre code secret Mobile Money est saisi sur votre téléphone, auprès de l'opérateur : l'application n'y a jamais accès.</li>
        <li><strong>Appareil photo</strong> : utilisé uniquement par les agents des agences pour scanner les QR codes des billets. Aucune photo n'est enregistrée.</li>
    </ul>

    <h2>2. Utilisation des données</h2>
    <ul>
        <li>créer et sécuriser votre compte (code de vérification par SMS ou appel) ;</li>
        <li>réserver vos places, encaisser le paiement et émettre vos billets ;</li>
        <li>permettre à l'agence de vérifier votre billet à l'embarquement ;</li>
        <li>tenir la comptabilité des ventes et répondre aux obligations légales.</li>
    </ul>
    <p>Nous ne vendons pas vos données et n'affichons pas de publicité ciblée.</p>

    <h2>3. Partage des données</h2>
    <ul>
        <li><strong>Agence de transport</strong> choisie : elle reçoit les informations de vos billets pour vous faire voyager.</li>
        <li><strong>MTN et Airtel</strong> : pour traiter le paiement.</li>
        <li><strong>Twilio</strong> : pour envoyer le code de vérification par SMS ou appel.</li>
        <li><strong>Google Cloud et Firebase</strong> : hébergement sécurisé du service.</li>
    </ul>

    <h2>4. Conservation</h2>
    <p>Les données du compte sont conservées tant que le compte existe. À la suppression du compte, il est effacé immédiatement ; les billets déjà achetés sont conservés, sans lien avec le compte, pour l'embarquement et les obligations comptables.</p>

    <h2>5. Sécurité</h2>
    <p>Les échanges sont chiffrés (HTTPS), les mots de passe sont chiffrés, et l'accès aux données des agences est limité à leur propre personnel.</p>

    <h2>6. Vos droits et la suppression du compte</h2>
    <p>Vous pouvez supprimer votre compte à tout moment depuis l'application (<em>Mon compte → Supprimer mon compte</em>) ou sur la page <a class="text-emerald-700 underline font-semibold" href="{{ route('compte.suppression') }}">{{ route('compte.suppression') }}</a>.</p>

    <h2>7. Contact</h2>
    <p>Pour toute question : <a class="text-emerald-700 underline font-semibold" href="mailto:{{ config('app.contact_email') }}">{{ config('app.contact_email') }}</a>.</p>
</div>
@endsection
