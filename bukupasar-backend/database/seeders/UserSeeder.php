<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $market = \App\Models\Market::where('code', 'TEST01')->first();

        if (! $market) {
            return;
        }

        $users = [
            [
                'username' => 'adminpusat',
                'name' => 'Admin Pusat',
                'email' => 'admin.pusat@example.com',
                'role' => 'admin_pusat',
            ],
            [
                'username' => 'adminpasar',
                'name' => 'Admin Pasar',
                'email' => 'admin.pasar@example.com',
                'role' => 'admin_pasar',
            ],
            [
                'username' => 'inputer',
                'name' => 'Inputer Pasar',
                'email' => 'inputer@example.com',
                'role' => 'inputer',
            ],
            [
                'username' => 'viewer',
                'name' => 'Viewer Pasar',
                'email' => 'viewer@example.com',
                'role' => 'viewer',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                [
                    'market_id' => $market->id,
                    'username' => $data['username'],
                ],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => null,
                    'password' => Hash::make('password'),
                ]
            );

            $user->assignRole($data['role']);
        }
    }
}
