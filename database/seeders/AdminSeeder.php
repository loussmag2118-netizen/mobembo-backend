<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'nom_agence' => 'Océan du Nord',
                'nom' => 'Admin',
                'prenom' => 'OceanDuNord',
                'telephone' => '+242060000011',
            ],
            [
                'nom_agence' => 'Nzoko Transport',
                'nom' => 'Admin',
                'prenom' => 'Nzoko',
                'telephone' => '+242060000012',
            ],
            [
                'nom_agence' => 'Stelimac',
                'nom' => 'Admin',
                'prenom' => 'Stelimac',
                'telephone' => '+242060000013',
            ],
            [
                'nom_agence' => 'Transbony',
                'nom' => 'Admin',
                'prenom' => 'Transbony',
                'telephone' => '+242060000014',
            ],
        ];

        foreach ($admins as $data) {
            $agence = Agence::where('nom_agence', $data['nom_agence'])->first();

            if (!$agence) {
                continue;
            }

            Utilisateur::firstOrCreate(
                ['telephone' => $data['telephone']],
                [
                    'agence_id' => $agence->id,
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom'],
                    'role' => 'Admin',
                    'mot_de_passe' => Hash::make('Mobembo2026'),
                    'code_acces_agent' => null,
                    'statut' => 'Actif',
                    'compte_confirme' => true,
                ]
            );
        }
    }
}