<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MarketSeeder::class,
            RoleSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
        ]);
    }
}
