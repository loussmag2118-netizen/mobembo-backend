<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\SuperAdmin;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder unique de l'application (regroupe les anciens
 * SuperAdminSeeder, AgenceSeeder, AdminSeeder et AgentSeeder).
 *
 *   php artisan db:seed
 *
 * Idempotent : peut être relancé sans créer de doublons.
 *
 * ⚠️ Les téléphones sont au format local (9 chiffres, ex: 060000011),
 * le même format que celui attendu par AuthController::login.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = $this->seedSuperAdmins();
        $this->seedAgences($superAdmin);
        $this->seedAdmins();
        $this->seedAgents();

        $this->command->info('');
        $this->command->info('🎉 Base de données initialisée avec succès !');
    }

    // ============================================================
    // 1. SUPER ADMINS (table super_admins, guard "superadmin")
    // ============================================================
    private function seedSuperAdmins(): SuperAdmin
    {
        $superAdmins = [
            ['email' => 'admin@mobemboline.com',      'nom' => 'SuperAdmin Mobembo', 'mot_de_passe' => 'Mobembo2026!'],
            ['email' => 'superadmin@mobemboline.com', 'nom' => 'Super Admin',        'mot_de_passe' => 'Ntemo2026'],
        ];

        $principal = null;

        foreach ($superAdmins as $data) {
            $superAdmin = SuperAdmin::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nom'          => $data['nom'],
                    'mot_de_passe' => Hash::make($data['mot_de_passe']),
                ]
            );
            $principal ??= $superAdmin;
            $this->command->info("✅ Super Admin : {$superAdmin->email}");
        }

        return $principal;
    }

    // ============================================================
    // 2. AGENCES
    // ============================================================
    private function seedAgences(SuperAdmin $superAdmin): void
    {
        $agences = [
            'Océan du Nord',
            'Nzoko Transport',
            'Stelimac',
            'Transbony',
        ];

        foreach ($agences as $nom) {
            Agence::firstOrCreate(
                ['nom_agence' => $nom],
                [
                    'statut'           => 'Actif',
                    'date_partenariat' => '2026-06-10',
                    'superadmin_id'    => $superAdmin->id,
                ]
            );
            $this->command->info("✅ Agence : {$nom}");
        }
    }

    // ============================================================
    // 3. ADMINS D'AGENCE
    // ============================================================
    private function seedAdmins(): void
    {
        $admins = [
            ['agence' => 'Océan du Nord',   'prenom' => 'OceanDuNord', 'telephone' => '060000011'],
            ['agence' => 'Nzoko Transport', 'prenom' => 'Nzoko',       'telephone' => '060000012'],
            ['agence' => 'Stelimac',        'prenom' => 'Stelimac',    'telephone' => '060000013'],
            ['agence' => 'Transbony',       'prenom' => 'Transbony',   'telephone' => '060000014'],
        ];

        foreach ($admins as $data) {
            $this->creerUtilisateur($data, 'Admin', 'Mobembo2026!');
        }
    }

    // ============================================================
    // 4. AGENTS
    // ============================================================
    private function seedAgents(): void
    {
        $agents = [
            ['agence' => 'Océan du Nord',   'prenom' => 'OceanDuNord', 'telephone' => '060000001', 'code_acces_agent' => 'ODN-AG-000'],
            ['agence' => 'Nzoko Transport', 'prenom' => 'Nzoko',       'telephone' => '060000002', 'code_acces_agent' => 'NZK-AG-000'],
            ['agence' => 'Stelimac',        'prenom' => 'Stelimac',    'telephone' => '060000003', 'code_acces_agent' => 'STL-AG-000'],
            ['agence' => 'Transbony',       'prenom' => 'Transbony',   'telephone' => '060000004', 'code_acces_agent' => 'TRB-AG-000'],
        ];

        foreach ($agents as $data) {
            $this->creerUtilisateur($data, 'Agent', 'Mobembo2026');
        }
    }

    /**
     * Crée un Admin ou un Agent rattaché à son agence (si l'agence existe).
     */
    private function creerUtilisateur(array $data, string $role, string $motDePasse): void
    {
        $agence = Agence::where('nom_agence', $data['agence'])->first();

        if (!$agence) {
            $this->command->warn("⚠️  Agence introuvable : {$data['agence']} ({$role} ignoré)");
            return;
        }

        Utilisateur::withTrashed()->firstOrCreate(
            ['telephone' => $data['telephone']],
            [
                'agence_id'        => $agence->id,
                'nom'              => $role,
                'prenom'           => $data['prenom'],
                'role'             => $role,
                'mot_de_passe'     => Hash::make($motDePasse),
                'code_acces_agent' => $data['code_acces_agent'] ?? null,
                'statut'           => 'Actif',
                'compte_confirme'  => true,
            ]
        );

        $this->command->info("✅ {$role} : {$data['telephone']} ({$data['agence']})");
    }
}
