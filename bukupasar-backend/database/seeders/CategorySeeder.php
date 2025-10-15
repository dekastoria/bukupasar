<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $market = \App\Models\Market::where('code', 'TEST01')->first();

        if (! $market) {
            return;
        }

        $categories = [
            'pemasukan' => [
                'Retribusi',
                'Parkir',
                'Sewa',
                'Pendapatan Lain',
            ],
            'pengeluaran' => [
                'Operasional',
                'Kebersihan',
                'Honor',
                'Pemeliharaan',
                'Lainnya',
            ],
        ];

        foreach ($categories as $jenis => $names) {
            foreach ($names as $name) {
                \App\Models\Category::updateOrCreate(
                    [
                        'market_id' => $market->id,
                        'jenis' => $jenis,
                        'nama' => $name,
                    ],
                    [
                        'wajib_keterangan' => false,
                        'aktif' => true,
                    ]
                );
            }
        }
    }
}
