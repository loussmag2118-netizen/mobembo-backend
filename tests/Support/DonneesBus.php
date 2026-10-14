<?php

namespace Tests\Support;

use App\Models\PlanificationVoyage;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;

/**
 * Données de test du transport par bus (agence, gares, bus, trajet,
 * horaire, départ, comptes).
 */
trait DonneesBus
{
    // ------------------------------------------------------------------
    // Données de test
    // ------------------------------------------------------------------

    protected function creerDepart(array $surcharge = []): PlanificationVoyage
    {
        $superAdmin = DB::table('super_admins')->insertGetId([
            'nom' => 'Super', 'email' => uniqid('sa').'@test.cg', 'mot_de_passe' => bcrypt('x'),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $agence = DB::table('agences')->insertGetId([
            'nom_agence' => uniqid('Agence '), 'statut' => 'Actif', 'date_partenariat' => now()->toDateString(),
            'superadmin_id' => $superAdmin, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $gareA = DB::table('gares')->insertGetId([
            'agence_id' => $agence, 'nom_gare' => 'Gare de Mpila', 'ville' => 'Brazzaville',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $gareB = DB::table('gares')->insertGetId([
            'agence_id' => $agence, 'nom_gare' => 'Agence de Nkayi', 'ville' => 'Nkayi', 'quartier' => 'Centre',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $bus = DB::table('bus')->insertGetId([
            'agence_id' => $agence, 'immatriculation' => 'BZ-123-AA', 'modele' => 'Test',
            'capacite_totale' => 50, 'statut' => 'Disponible', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $trajet = DB::table('trajets')->insertGetId([
            'agence_id' => $agence, 'ville_depart' => 'Brazzaville', 'ville_arrivee' => 'Nkayi',
            'gare_depart_id' => $gareA, 'gare_arrivee_id' => $gareB, 'bus_id' => $bus,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $vague = DB::table('vague_departs')->insertGetId([
            'agence_id' => $agence, 'trajet_id' => $trajet, 'heure_depart' => '07:30:00',
            'nom_vague' => 'Matin', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $places = $surcharge['nombre_tickets_total'] ?? 3;
        $id = DB::table('planification_voyages')->insertGetId(array_merge([
            'agence_id' => $agence, 'trajet_id' => $trajet, 'bus_id' => $bus, 'vague_depart_id' => $vague,
            'date_voyage' => now()->addDays(3)->toDateString(), 'nombre_tickets_total' => $places,
            'prix' => 7000, 'places_disponibles' => $places, 'statut_voyage' => 'Programmation',
            'nombre_places_total' => $places, 'nombre_places_restantes' => $places,
            'created_at' => now(), 'updated_at' => now(),
        ], $surcharge));

        return PlanificationVoyage::findOrFail($id);
    }

    protected function passagers(int $n): array
    {
        return array_map(fn ($i) => ['nom' => "NOM{$i}", 'prenom' => "Prenom{$i}"], range(1, $n));
    }

    protected function client(): Utilisateur
    {
        return Utilisateur::create([
            'nom' => 'Client', 'prenom' => 'Test', 'telephone' => (string) random_int(60000000, 69999999),
            'role' => 'Client', 'mot_de_passe' => bcrypt('Secret123'), 'statut' => 'actif', 'compte_confirme' => true,
        ]);
    }

    protected function personnel(string $role, int $agenceId): Utilisateur
    {
        return Utilisateur::create([
            'nom' => $role, 'prenom' => 'Test', 'telephone' => (string) random_int(50000000, 59999999),
            'email' => uniqid(strtolower($role)) . '@test.cg', 'role' => $role, 'agence_id' => $agenceId,
            'mot_de_passe' => bcrypt('Secret123'), 'statut' => 'actif', 'compte_confirme' => true,
        ]);
    }
}
