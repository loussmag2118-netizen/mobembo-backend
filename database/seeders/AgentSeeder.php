<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        $agents = [
            [
                'nom_agence' => 'Océan du Nord',
                'nom' => 'Agent',
                'prenom' => 'OceanDuNord',
                'telephone' => '+242060000001',
                'code_acces_agent' => 'ODN-AG-000',
            ],
            [
                'nom_agence' => 'Nzoko Transport',
                'nom' => 'Agent',
                'prenom' => 'Nzoko',
                'telephone' => '+242060000002',
                'code_acces_agent' => 'NZK-AG-000',
            ],
            [
                'nom_agence' => 'Stelimac',
                'nom' => 'Agent',
                'prenom' => 'Stelimac',
                'telephone' => '+242060000003',
                'code_acces_agent' => 'STL-AG-000',
            ],
            [
                'nom_agence' => 'Transbony',
                'nom' => 'Agent',
                'prenom' => 'Transbony',
                'telephone' => '+242060000004',
                'code_acces_agent' => 'TRB-AG-000',
            ],
        ];

        foreach ($agents as $data) {
            $agence = Agence::where('nom_agence', $data['nom_agence'])->first();

            if (!$agence) {
                continue; // agence introuvable, on saute
            }

            Utilisateur::firstOrCreate(
                ['telephone' => $data['telephone']],
                [
                    'agence_id' => $agence->id,
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom'],
                    'role' => 'Agent',
                    'mot_de_passe' => Hash::make('Mobembo2026'), // mot de passe temporaire
                    'code_acces_agent' => $data['code_acces_agent'],
                    'statut' => 'Actif',
                    'compte_confirme' => true,
                ]
            );
        }
    }
}