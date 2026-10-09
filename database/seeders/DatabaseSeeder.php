<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\SuperAdmin;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // 1. SUPER ADMIN
        // ============================================================
        $superAdmin = SuperAdmin::firstOrCreate(
            ['email' => 'admin@mobemboline.com'],
            [
                'nom'          => 'SuperAdmin Mobembo',
                'mot_de_passe' => Hash::make('Mobembo2026!'),
            ]
        );
        $this->command->info("✅ Super Admin : {$superAdmin->email}");

        // ============================================================
        // 2. AGENCES
        // ============================================================
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
                    'date_partenariat' => now(),
                    'superadmin_id'    => $superAdmin->id,
                ]
            );
            $this->command->info("✅ Agence : {$nom}");
        }

        // ============================================================
        // 3. ADMINS D'AGENCE
        // ============================================================
        $admins = [
            ['nom' => 'Admin', 'prenom' => 'OcéanDuNord', 'telephone' => '060000011', 'agence_nom' => 'Océan du Nord'],
            ['nom' => 'Admin', 'prenom' => 'Nzoko',       'telephone' => '060000012', 'agence_nom' => 'Nzoko Transport'],
            ['nom' => 'Admin', 'prenom' => 'Stelimac',    'telephone' => '060000013', 'agence_nom' => 'Stelimac'],
            ['nom' => 'Admin', 'prenom' => 'Transbony',   'telephone' => '060000014', 'agence_nom' => 'Transbony'],
        ];

        foreach ($admins as $a) {
            $agence = Agence::where('nom_agence', $a['agence_nom'])->first();

            Utilisateur::firstOrCreate(
                ['telephone' => $a['telephone']],
                [
                    'nom'          => $a['nom'],
                    'prenom'       => $a['prenom'],
                    'role'         => 'Admin',
                    'statut'       => 'Actif',
                    'agence_id'    => $agence?->id,
                    'mot_de_passe' => Hash::make('Mobembo2026!'),
                ]
            );
            $this->command->info("✅ Admin : {$a['telephone']} ({$a['agence_nom']})");
        }

        $this->command->info('');
        $this->command->info('🎉 Base de données initialisée avec succès !');
    }
}