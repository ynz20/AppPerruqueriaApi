<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Service;
use App\Models\Shift;
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
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Luis',
        //     'surname' => 'Perez',
        //     'nick' => 'luisp',
        //     'telf' => '123456789',
        //     'email' => 'test@example.com',
        //     'email_verified_at' => now(),
        //     'password' => bcrypt('123456789'),
        //     'remember_token' => Str::random(10),
        //     'created_at' => now(),
        //     'updated_at' => now(),
        //     'dni' => '20572143T',
        //     'is_admin' => 0,
        // ]);

        // Client::factory(3)->create();

        // Shift::factory(1)->create();

        Service::factory(3)->create();
    }
}
