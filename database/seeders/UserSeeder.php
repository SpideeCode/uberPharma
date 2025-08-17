<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@site.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Pharmacy',
            'email' => 'pharmacy@site.test',
            'password' => Hash::make('password'),
            'role' => 'pharmacy',
        ]);

        User::create([
            'name' => 'Client',
            'email' => 'client@site.test',
            'password' => Hash::make('password'),
            'role' => 'client',
        ]);
    }
}
