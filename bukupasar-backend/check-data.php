<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check Markets
echo "=== MARKETS ===\n";
$markets = App\Models\Market::all();
echo "Total: {$markets->count()}\n";
foreach ($markets as $market) {
    echo "  - ID: {$market->id}, Name: {$market->name}, Code: {$market->code}\n";
}

// Check Tenants
echo "\n=== TENANTS ===\n";
$tenants = App\Models\Tenant::all();
echo "Total: {$tenants->count()}\n";
foreach ($tenants->take(5) as $tenant) {
    echo "  - ID: {$tenant->id}, Name: {$tenant->nama}, Lapak: {$tenant->nomor_lapak}, Market: {$tenant->market_id}\n";
}

// Check Categories
echo "\n=== CATEGORIES ===\n";
$categories = App\Models\Category::all();
echo "Total: {$categories->count()}\n";
foreach ($categories->take(10) as $cat) {
    echo "  - ID: {$cat->id}, Market: {$cat->market_id}, Jenis: {$cat->jenis}, Nama: {$cat->nama}\n";
}

// Check Users
echo "\n=== USERS (with roles) ===\n";
$users = App\Models\User::with('roles')->get();
echo "Total: {$users->count()}\n";
foreach ($users as $user) {
    $roles = $user->roles->pluck('name')->join(', ');
    $roles = $roles ?: 'NO ROLE';
    echo "  - ID: {$user->id}, Email: {$user->email}, Roles: {$roles}, Market: {$user->market_id}\n";
}

// Check Transactions
echo "\n=== TRANSACTIONS ===\n";
$transactions = App\Models\Transaction::all();
echo "Total: {$transactions->count()}\n";

// Check Payments
echo "\n=== PAYMENTS ===\n";
$payments = App\Models\Payment::all();
echo "Total: {$payments->count()}\n";
