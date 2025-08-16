<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Order;
use App\Models\OrderItem;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Créer un admin
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@uberpharma.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 2️⃣ Créer 5 clients
        User::factory(5)->create([
            'role' => 'client',
        ]);

        // 3️⃣ Créer 3 pharmacies
        $pharmacies = Pharmacy::factory(3)->create();

        // 4️⃣ Créer 5 catégories de produits
        $categories = ProductCategory::factory(5)->create();

        // 5️⃣ Créer 10 produits pour chaque pharmacie, avec catégorie aléatoire
        $pharmacies->each(function ($pharmacy)  {
            Product::factory(10)->create([
                'pharmacy_id' => $pharmacy->id,
                
            ]);
        });



           
            
        }
    }
