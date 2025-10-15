<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MarketSeeder extends Seeder
{
    public function run(): void
    {
        $market = \App\Models\Market::firstOrCreate(
            ['code' => 'TEST01'],
            [
                'name' => 'Pasar Test',
                'address' => 'Jl. Contoh No.1',
            ]
        );

        \App\Models\Setting::setValue($market->id, 'backdate_days', 60);
        \App\Models\Setting::setValue($market->id, 'inputer_edit_window_hours', 24);
    }
}
