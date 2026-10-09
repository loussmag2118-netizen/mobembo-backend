<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // Supprime l'ancien utilisateur s'il existe
        DB::table('users')->where('email', 'superadmin@mobemboline.com')->delete();

        // En crée un nouveau avec le mot de passe "Ntemo2026" correctement haché
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'superadmin@mobemboline.com',
            'password' => Hash::make('Ntemo2026'),
            'role' => 'SuperAdmin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
