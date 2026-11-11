<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@gabon-catalogue.com'],
            [
                'name' => 'Administrateur',
                'password' => 'admin1234',
            ],
        );
    }
}
