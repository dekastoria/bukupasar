<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = App\Models\User::with('market', 'roles')->get();

foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Market ID: {$user->market_id}\n";
    echo "Market: " . ($user->market ? $user->market->name : 'NULL') . "\n";
    echo "Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
    echo "---\n";
}

echo "\nTotal users: " . $users->count() . "\n";

// Check roles table
echo "\n=== Roles ===\n";
$roles = Spatie\Permission\Models\Role::all();
foreach ($roles as $role) {
    echo "- {$role->name}\n";
}

// Check markets
echo "\n=== Markets ===\n";
$markets = App\Models\Market::all();
foreach ($markets as $market) {
    echo "- ID: {$market->id}, Name: {$market->name}, Code: {$market->code}\n";
}
