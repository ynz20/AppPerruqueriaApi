<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Shift;
use App\Models\Product;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    
        User::factory()->create([
            'name' => 'Luis',
            'surname' => 'Perez',
            'nick' => 'luisp',
            'telf' => '123456789',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('123456789'),
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
            'dni' => '20572143T',
            'is_admin' => 0,
        ]);

        Client::factory(20)->create();

        Client::factory()->create([
            'dni' => '50572123G',
            'name' => 'Yeray',
            'surname' => 'Zafra',
            'telf' => '600749384',
            'email' => 'luisruiz@gmail.com'
        ]);

        Shift::factory(1)->create();

        Service::factory(3)->create();

        Reservation::factory()->create([
            "date" => "2025-01-01",
            "hour" => "10:00",
            "worker_dni" => "20572143T",
            "client_dni" => "50572123G",
            "service_id" => 1,
            "shift_id" => 1,
            "status" => "completed",
            "rating" => 3
        ]);

        Reservation::factory()->create([
            "date" => "2025-01-02",
            "hour" => "11:00",
            "worker_dni" => "20572143T",
            "client_dni" => "50572123G",
            "service_id" => 2,
            "shift_id" => 1,
            "status" => "completed",
            "rating" => 2
        ]);

        Product::create([
            'name' => 'Xampú Reparador',
            'description' => 'Xampú ideal per a cabells danyats. Repara i enforteix.',
            'price' => 12.99,
            'stock' => 50,
        ]);

        Product::create([
            'name' => 'Acondicionador Nutritiu',
            'description' => 'Acondicionador que aporta suavitat i brillantor al cabell.',
            'price' => 14.50,
            'stock' => 30,
        ]);

        Product::create([
            'name' => 'Laca Fijadora',
            'description' => 'Laca professional per a una fixació duradora.',
            'price' => 8.75,
            'stock' => 20,
        ]);

        Product::create([
            'name' => 'Cera per al Cabell',
            'description' => 'Cera modeladora amb efecte mat.',
            'price' => 10.00,
            'stock' => 25,
        ]);

        
    }
}
