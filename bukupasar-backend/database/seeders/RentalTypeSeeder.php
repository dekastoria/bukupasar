<?php

namespace Database\Seeders;

use App\Models\Market;
use App\Models\RentalType;
use Illuminate\Database\Seeder;

class RentalTypeSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $markets = Market::all();

        foreach ($markets as $market) {
            // Default rental types untuk setiap pasar
            $rentalTypes = [
                ['nama' => 'Lapak', 'keterangan' => 'Lapak pedagang kecil'],
                ['nama' => 'Kios', 'keterangan' => 'Kios permanen dengan dinding'],
                ['nama' => 'Toko', 'keterangan' => 'Toko dengan ukuran lebih besar'],
                ['nama' => 'Ruko', 'keterangan' => 'Rumah toko 2 lantai'],
                ['nama' => 'Los', 'keterangan' => 'Tempat terbuka tanpa dinding'],
            ];

            foreach ($rentalTypes as $type) {
                RentalType::firstOrCreate(
                    [
                        'market_id' => $market->id,
                        'nama' => $type['nama'],
                    ],
                    [
                        'keterangan' => $type['keterangan'],
                        'aktif' => true,
                    ]
                );
            }
        }
    }
}
