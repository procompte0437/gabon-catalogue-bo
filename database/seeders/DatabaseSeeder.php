<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@gabon-catalogue.local'],
            [
                'name' => 'Administrateur',
                'password' => '0110',
            ],
        );

        $this->call(CatalogueSeeder::class);
    }
}
