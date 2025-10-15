<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get testadmin user
$user = App\Models\User::where('email', 'testadmin@example.com')->first();

if (!$user) {
    echo "User testadmin@example.com tidak ditemukan.\n";
    exit(1);
}

// Assign admin_pusat role
$user->assignRole('admin_pusat');

echo "✅ Role 'admin_pusat' berhasil di-assign ke user: {$user->email}\n";
echo "Silakan logout dan login ulang ke Filament.\n";
