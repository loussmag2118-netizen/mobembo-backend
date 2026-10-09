<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\SuperAdmin;
use Illuminate\Database\Seeder;

class AgenceSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = SuperAdmin::first();

        if (!$superAdmin) {
            $superAdmin = SuperAdmin::create([
                'nom' => 'SuperAdmin Mobembo',
                'email' => 'admin@mobemboline.com',
                'mot_de_passe' => bcrypt('changeme123'),
            ]);
        }

        $agences = [
            ['nom_agence' => 'Océan du Nord', 'statut' => 'Actif', 'date_partenariat' => '2026-06-10'],
            ['nom_agence' => 'Nzoko Transport', 'statut' => 'Actif', 'date_partenariat' => '2026-06-10'],
            ['nom_agence' => 'Stelimac', 'statut' => 'Actif', 'date_partenariat' => '2026-06-10'],
            ['nom_agence' => 'Transbony', 'statut' => 'Actif', 'date_partenariat' => '2026-06-10'],
        ];

        foreach ($agences as $agence) {
            Agence::firstOrCreate(
                ['nom_agence' => $agence['nom_agence']],
                [
                    'statut' => $agence['statut'],
                    'date_partenariat' => $agence['date_partenariat'],
                    'superadmin_id' => $superAdmin->id,
                ]
            );
        }
    }
}