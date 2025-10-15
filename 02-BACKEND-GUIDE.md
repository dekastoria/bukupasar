# 02-BACKEND-GUIDE.md
# Bukupasar — Backend Implementation Guide

**Laravel 11 + Filament 3 + MySQL** implementation details.

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Laravel Project Setup](#laravel-project-setup)
3. [Database Migrations](#database-migrations)
4. [Eloquent Models](#eloquent-models)
5. [API Endpoints](#api-endpoints)
6. [Filament Admin Panel](#filament-admin-panel)
7. [Excel Import/Export](#excel-importexport)
8. [Testing](#testing)

---

## 1. Prerequisites

### Environment Requirements
✅ **Already Ready (per user):**
- Laragon (PHP 8.2+, MySQL 8, Nginx)
- Node.js 18+
- Composer 2.x
- Git

### Check Versions
```bash
php -v          # Should be 8.2 or higher
composer -v     # Should be 2.x
mysql --version # Should be 8.x
node -v         # Should be 18 or higher
```

---

## 2. Laravel Project Setup

### Step 1: Create Laravel Project

```bash
# Navigate to Laragon www directory
cd C:\laragon\www

# Create new Laravel 11 project
composer create-project laravel/laravel bukupasar-backend

# Navigate into project
cd bukupasar-backend
```

### Step 2: Configure Database

**Edit `.env` file:**
```env
APP_NAME=Bukupasar
APP_ENV=local
APP_DEBUG=true
APP_URL=http://bukupasar-backend.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bukupasar_dev
DB_USERNAME=root
DB_PASSWORD=

# Sanctum for API
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,bukupasar-frontend.test
```

**Create database:**
```bash
# Via Laragon: Open Database Manager → Create new database: bukupasar_dev
# Or via command:
mysql -u root -e "CREATE DATABASE bukupasar_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 3: Install Required Packages

```bash
# Laravel Sanctum (API authentication)
composer require laravel/sanctum

# Spatie Permission (RBAC)
composer require spatie/laravel-permission

# Filament 3 (Admin Panel)
composer require filament/filament:"^3.0"

# Laravel Excel (Import/Export)
composer require maatwebsite/excel

# Image intervention (optional, for image processing)
composer require intervention/image
```

### Step 4: Publish Vendor Assets

```bash
# Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Spatie Permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Run migrations for vendor packages
php artisan migrate
```

### Step 5: Install Filament

```bash
# Install Filament Panel
php artisan filament:install --panels

# Create admin user (akan diminta input)
php artisan make:filament-user
```

### Step 6: Configure Sanctum Middleware

**Edit `bootstrap/app.php`:**
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ]);
})
```

---

## 3. Database Migrations

### Migration Generation Strategy

**Urutan migrations (penting!):**
1. markets
2. users (Laravel default, modify untuk add market_id)
3. Spatie permission tables (auto-created)
4. tenants
5. categories
6. transactions
7. payments
8. settings
9. audit_logs
10. uploads

### Create Migrations

```bash
# Generate migrations
php artisan make:migration create_markets_table
php artisan make:migration add_market_id_to_users_table
php artisan make:migration create_tenants_table
php artisan make:migration create_categories_table
php artisan make:migration create_transactions_table
php artisan make:migration create_payments_table
php artisan make:migration create_settings_table
php artisan make:migration create_audit_logs_table
php artisan make:migration create_uploads_table
```

### Migration: Markets

**File:** `database/migrations/xxxx_create_markets_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 50)->unique();
            $table->text('address')->nullable();
            $table->timestamps();
            
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('markets');
    }
};
```

### Migration: Modify Users Table

**File:** `database/migrations/xxxx_add_market_id_to_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('market_id')->after('id')
                  ->constrained('markets')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->string('username', 100)->after('name');
            $table->string('phone', 50)->nullable()->after('email');
            
            // Make email nullable (username is primary identifier)
            $table->string('email')->nullable()->change();
            
            // Unique constraint per market
            $table->unique(['market_id', 'username']);
            
            $table->index('market_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['market_id']);
            $table->dropUnique(['market_id', 'username']);
            $table->dropColumn(['market_id', 'username', 'phone']);
        });
    }
};
```

### Migration: Tenants

**File:** `database/migrations/xxxx_create_tenants_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')
                  ->constrained('markets')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->string('nama', 200);
            $table->string('nomor_lapak', 50);
            $table->string('hp', 30)->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto_profile')->nullable();
            $table->string('foto_ktp')->nullable();
            $table->bigInteger('outstanding')->default(0);
            $table->timestamps();
            
            // Unique per market
            $table->unique(['market_id', 'nomor_lapak']);
            
            // Indexes
            $table->index(['market_id', 'created_at']);
            $table->index('outstanding');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
```

### Migration: Categories

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')
                  ->constrained('markets')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->string('nama', 100);
            $table->boolean('wajib_keterangan')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            
            // Unique per market and jenis
            $table->unique(['market_id', 'jenis', 'nama']);
            
            $table->index('market_id');
            $table->index(['market_id', 'jenis', 'aktif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
```

### Migration: Transactions

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')
                  ->constrained('markets')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->date('tanggal');
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->string('subkategori', 100);
            $table->bigInteger('jumlah');
            
            $table->foreignId('tenant_id')->nullable()
                  ->constrained('tenants')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->text('catatan')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['market_id', 'tanggal']);
            $table->index(['market_id', 'jenis']);
            $table->index(['market_id', 'created_by']);
            $table->index(['market_id', 'subkategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
```

### Migration: Payments

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')
                  ->constrained('markets')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->foreignId('tenant_id')
                  ->constrained('tenants')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->date('tanggal');
            $table->bigInteger('jumlah');
            
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->text('catatan')->nullable();
            $table->timestamps();
            
            $table->index(['market_id', 'tanggal']);
            $table->index(['market_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
```

### Migration: Settings

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->foreignId('market_id')
                  ->constrained('markets')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->string('key_name', 100);
            $table->text('value')->nullable();
            $table->timestamp('updated_at')->nullable();
            
            // Composite primary key
            $table->primary(['market_id', 'key_name']);
            
            $table->index('market_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
```

### Migration: Audit Logs

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')
                  ->constrained('markets')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->string('action', 100);
            $table->string('entity', 100);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['market_id', 'created_at']);
            $table->index(['entity', 'entity_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
```

### Migration: Uploads

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')
                  ->constrained('markets')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->string('path');
            $table->string('mime', 100);
            $table->integer('size');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['market_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
```

### Run Migrations

```bash
# Run all migrations
php artisan migrate

# If error, rollback and re-run
php artisan migrate:rollback
php artisan migrate

# Fresh migration (drop all + migrate)
php artisan migrate:fresh
```

---

## 4. Eloquent Models

### Model Generation

```bash
php artisan make:model Market
php artisan make:model Tenant
php artisan make:model Category
php artisan make:model Transaction
php artisan make:model Payment
php artisan make:model Setting
php artisan make:model AuditLog
php artisan make:model Upload
```

### Model: Market

**File:** `app/Models/Market.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Market extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
    ];

    // Relationships
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    // Helper method
    public function getSetting($key, $default = null)
    {
        $setting = $this->settings()->where('key_name', $key)->first();
        return $setting ? $setting->value : $default;
    }
}
```

### Model: User (Modify Existing)

**File:** `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    protected $fillable = [
        'market_id',
        'username',
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    // Check if admin pusat
    public function isAdminPusat(): bool
    {
        return $this->hasRole('admin_pusat');
    }

    // Check if admin pasar
    public function isAdminPasar(): bool
    {
        return $this->hasRole('admin_pasar');
    }

    // Check if inputer
    public function isInputer(): bool
    {
        return $this->hasRole('inputer');
    }
}
```

### Model: Tenant

**File:** `app/Models/Tenant.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Tenant extends Model
{
    protected $fillable = [
        'market_id',
        'nama',
        'nomor_lapak',
        'hp',
        'alamat',
        'foto_profile',
        'foto_ktp',
        'outstanding',
    ];

    protected $casts = [
        'outstanding' => 'integer',
    ];

    // Relationships
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeForMarket(Builder $query, int $marketId): Builder
    {
        return $query->where('market_id', $marketId);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('nomor_lapak', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getFormattedOutstandingAttribute(): string
    {
        return 'Rp ' . number_format($this->outstanding, 0, ',', '.');
    }

    public function hasOutstanding(): bool
    {
        return $this->outstanding > 0;
    }
}
```

### Model: Category

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    protected $fillable = [
        'market_id',
        'jenis',
        'nama',
        'wajib_keterangan',
        'aktif',
    ];

    protected $casts = [
        'wajib_keterangan' => 'boolean',
        'aktif' => 'boolean',
    ];

    // Relationships
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    // Scopes
    public function scopeForMarket(Builder $query, int $marketId): Builder
    {
        return $query->where('market_id', $marketId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    public function scopeJenis(Builder $query, string $jenis): Builder
    {
        return $query->where('jenis', $jenis);
    }

    public function scopePemasukan(Builder $query): Builder
    {
        return $query->where('jenis', 'pemasukan');
    }

    public function scopePengeluaran(Builder $query): Builder
    {
        return $query->where('jenis', 'pengeluaran');
    }
}
```

### Model: Transaction

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Transaction extends Model
{
    protected $fillable = [
        'market_id',
        'tanggal',
        'jenis',
        'subkategori',
        'jumlah',
        'tenant_id',
        'created_by',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'integer',
    ];

    // Relationships
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeForMarket(Builder $query, int $marketId): Builder
    {
        return $query->where('market_id', $marketId);
    }

    public function scopeJenis(Builder $query, string $jenis): Builder
    {
        return $query->where('jenis', $jenis);
    }

    public function scopePemasukan(Builder $query): Builder
    {
        return $query->where('jenis', 'pemasukan');
    }

    public function scopePengeluaran(Builder $query): Builder
    {
        return $query->where('jenis', 'pengeluaran');
    }

    public function scopeByDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('tanggal', $date);
    }

    public function scopeDateRange(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('tanggal', [$from, $to]);
    }

    public function scopeSubkategori(Builder $query, string $subkategori): Builder
    {
        return $query->where('subkategori', $subkategori);
    }

    public function scopeCreatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }

    // Helpers
    public function isPemasukan(): bool
    {
        return $this->jenis === 'pemasukan';
    }

    public function isPengeluaran(): bool
    {
        return $this->jenis === 'pengeluaran';
    }

    public function canBeEditedBy(User $user): bool
    {
        // Admin always can
        if ($user->hasRole(['admin_pusat', 'admin_pasar'])) {
            return true;
        }

        // Inputer: own transaction within 24 hours
        if ($user->hasRole('inputer')) {
            $isOwner = $this->created_by === $user->id;
            $within24h = $this->created_at->diffInHours(now()) <= 24;
            return $isOwner && $within24h;
        }

        return false;
    }

    // Formatted
    public function getFormattedJumlahAttribute(): string
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }
}
```

### Model: Payment

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Payment extends Model
{
    protected $fillable = [
        'market_id',
        'tenant_id',
        'tanggal',
        'jumlah',
        'created_by',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'integer',
    ];

    // Relationships
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeForMarket(Builder $query, int $marketId): Builder
    {
        return $query->where('market_id', $marketId);
    }

    public function scopeByDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('tanggal', $date);
    }

    public function scopeDateRange(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('tanggal', [$from, $to]);
    }

    // Formatted
    public function getFormattedJumlahAttribute(): string
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }
}
```

### Model: Setting

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = ['market_id', 'key_name'];

    protected $fillable = [
        'market_id',
        'key_name',
        'value',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    // Helper: Get setting value with default
    public static function get(int $marketId, string $key, $default = null)
    {
        $setting = static::where('market_id', $marketId)
                        ->where('key_name', $key)
                        ->first();
        
        return $setting ? $setting->value : $default;
    }

    // Helper: Set setting value
    public static function set(int $marketId, string $key, $value): void
    {
        static::updateOrCreate(
            ['market_id' => $marketId, 'key_name' => $key],
            ['value' => $value]
        );
    }
}
```

---

## 5. API Endpoints

### API Routes Structure

**File:** `routes/api.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Tenants
    Route::apiResource('tenants', TenantController::class);
    Route::get('/tenants/search/{query}', [TenantController::class, 'search']);
    
    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{jenis}', [CategoryController::class, 'byJenis']);
    
    // Transactions
    Route::apiResource('transactions', TransactionController::class);
    
    // Payments
    Route::apiResource('payments', PaymentController::class);
    
    // Reports
    Route::get('/reports/daily', [ReportController::class, 'daily']);
    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/cashbook', [ReportController::class, 'cashbook']);
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss']);
    
    // Settings
    Route::get('/settings', [SettingController::class, 'index']);
});
```

### Controller: AuthController

**Generate:** `php artisan make:controller Api/AuthController`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Login
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'market_id' => 'required|integer|exists:markets,id',
        ]);

        // Find user by username and market_id
        $user = User::where('username', $request->username)
                    ->where('market_id', $request->market_id)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Username atau password salah'
            ], 401);
        }

        // Generate token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'market_id' => $user->market_id,
                'role' => $user->getRoleNames()->first(),
            ],
        ]);
    }

    /**
     * Logout
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Get authenticated user
     * GET /api/auth/user
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('market'),
        ]);
    }
}
```

### Controller: TransactionController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * List transactions
     * GET /api/transactions?date=&jenis=&subkategori=&page=&limit=
     */
    public function index(Request $request)
    {
        $marketId = $request->user()->market_id;
        
        $query = Transaction::forMarket($marketId)
                            ->with(['tenant', 'creator'])
                            ->latest('tanggal');

        // Filters
        if ($request->has('date')) {
            $query->byDate(Carbon::parse($request->date));
        }

        if ($request->has('jenis')) {
            $query->jenis($request->jenis);
        }

        if ($request->has('subkategori')) {
            $query->subkategori($request->subkategori);
        }

        // Pagination
        $limit = $request->get('limit', 15);
        $transactions = $query->paginate($limit);

        return response()->json($transactions);
    }

    /**
     * Create transaction
     * POST /api/transactions
     */
    public function store(Request $request)
    {
        $marketId = $request->user()->market_id;
        
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jenis' => 'required|in:pemasukan,pengeluaran',
            'subkategori' => 'required|string|max:100',
            'jumlah' => 'required|integer|min:1',
            'tenant_id' => 'nullable|exists:tenants,id',
            'catatan' => 'nullable|string',
        ]);

        // TODO: Add business rule validations:
        // - Check backdate limit
        // - Check allowed days
        // - Check kategori wajib_keterangan

        $transaction = Transaction::create([
            ...$validated,
            'market_id' => $marketId,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Transaksi berhasil ditambahkan',
            'data' => $transaction->load(['tenant', 'creator']),
        ], 201);
    }

    /**
     * Show transaction
     * GET /api/transactions/{id}
     */
    public function show(Request $request, Transaction $transaction)
    {
        // Market scope check
        if ($transaction->market_id !== $request->user()->market_id) {
            abort(403, 'Unauthorized');
        }

        return response()->json([
            'data' => $transaction->load(['tenant', 'creator']),
        ]);
    }

    /**
     * Update transaction
     * PUT/PATCH /api/transactions/{id}
     */
    public function update(Request $request, Transaction $transaction)
    {
        // Authorization check
        if (!$transaction->canBeEditedBy($request->user())) {
            abort(403, 'Anda tidak dapat mengedit transaksi ini');
        }

        $validated = $request->validate([
            'tanggal' => 'sometimes|date',
            'jenis' => 'sometimes|in:pemasukan,pengeluaran',
            'subkategori' => 'sometimes|string|max:100',
            'jumlah' => 'sometimes|integer|min:1',
            'tenant_id' => 'nullable|exists:tenants,id',
            'catatan' => 'nullable|string',
        ]);

        $transaction->update($validated);

        return response()->json([
            'message' => 'Transaksi berhasil diupdate',
            'data' => $transaction->fresh(['tenant', 'creator']),
        ]);
    }

    /**
     * Delete transaction
     * DELETE /api/transactions/{id}
     */
    public function destroy(Request $request, Transaction $transaction)
    {
        // Authorization check
        if (!$transaction->canBeEditedBy($request->user())) {
            abort(403, 'Anda tidak dapat menghapus transaksi ini');
        }

        $transaction->delete();

        return response()->json([
            'message' => 'Transaksi berhasil dihapus',
        ]);
    }
}
```

### Controller: PaymentController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Create payment (bayar sewa)
     * POST /api/payments
     */
    public function store(Request $request)
    {
        $marketId = $request->user()->market_id;
        
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|integer|min:1',
            'catatan' => 'nullable|string',
        ]);

        // Get tenant
        $tenant = Tenant::findOrFail($validated['tenant_id']);

        // Market check
        if ($tenant->market_id !== $marketId) {
            abort(403, 'Tenant tidak ditemukan');
        }

        // Validate: payment <= outstanding
        if ($validated['jumlah'] > $tenant->outstanding) {
            return response()->json([
                'message' => 'Pembayaran melebihi tunggakan',
                'errors' => [
                    'jumlah' => [
                        sprintf(
                            'Maksimal pembayaran Rp %s (sisa tunggakan)',
                            number_format($tenant->outstanding, 0, ',', '.')
                        )
                    ]
                ]
            ], 422);
        }

        // Process payment in transaction
        DB::beginTransaction();
        try {
            // Create payment record
            $payment = Payment::create([
                ...$validated,
                'market_id' => $marketId,
                'created_by' => $request->user()->id,
            ]);

            // Update tenant outstanding
            $tenant->decrement('outstanding', $validated['jumlah']);

            // TODO: Create audit log

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran berhasil',
                'data' => $payment->load(['tenant', 'creator']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Pembayaran gagal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
```

---

## 6. Filament Admin Panel

### Install Filament Resources

```bash
# Generate Filament resources
php artisan make:filament-resource Market --generate
php artisan make:filament-resource Tenant --generate
php artisan make:filament-resource Category --generate
php artisan make:filament-resource Transaction --generate
php artisan make:filament-resource Payment --generate
```

### Filament Resource: MarketResource (Example)

**File:** `app/Filament/Resources/MarketResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketResource\Pages;
use App\Models\Market;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MarketResource extends Resource
{
    protected static ?string $model = Market::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Pasar';
    protected static ?string $modelLabel = 'Pasar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Pasar')
                    ->required()
                    ->maxLength(150),
                    
                Forms\Components\TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->helperText('Kode unik untuk pasar'),
                    
                Forms\Components\Textarea::make('address')
                    ->label('Alamat')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Jumlah User')
                    ->counts('users'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarkets::route('/'),
            'create' => Pages\CreateMarket::route('/create'),
            'edit' => Pages\EditMarket::route('/{record}/edit'),
        ];
    }

    // Restrict access to admin_pusat only
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin_pusat');
    }
}
```

### Dashboard Widgets

**Generate widget:**
```bash
php artisan make:filament-widget StatsOverview --resource=TransactionResource
```

**Example Widget:**
```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Transaction;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $marketId = auth()->user()->market_id;
        $today = Carbon::today();

        $pemasukan = Transaction::forMarket($marketId)
            ->pemasukan()
            ->whereDate('tanggal', $today)
            ->sum('jumlah');

        $pengeluaran = Transaction::forMarket($marketId)
            ->pengeluaran()
            ->whereDate('tanggal', $today)
            ->sum('jumlah');

        $saldo = $pemasukan - $pengeluaran;

        return [
            Stat::make('Pemasukan Hari Ini', 'Rp ' . number_format($pemasukan, 0, ',', '.'))
                ->description('Total pemasukan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Pengeluaran Hari Ini', 'Rp ' . number_format($pengeluaran, 0, ',', '.'))
                ->description('Total pengeluaran')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
                
            Stat::make('Saldo', 'Rp ' . number_format($saldo, 0, ',', '.'))
                ->description('Pemasukan - Pengeluaran')
                ->color($saldo >= 0 ? 'success' : 'danger'),
        ];
    }
}
```

---

## 7. Excel Import/Export

**Phase 2 Feature** - Detailed guide will be added later.

Basic structure:
- Use `maatwebsite/excel` package
- Upload → Parse → Validate → Preview → Commit
- Transaction safety with DB::transaction()
- Duplicate detection per market

---

## 8. Testing

### Feature Test Example

**Generate test:**
```bash
php artisan make:test TransactionApiTest
```

**Example test:**
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Market;
use App\Models\Transaction;
use Laravel\Sanctum\Sanctum;

class TransactionApiTest extends TestCase
{
    public function test_can_create_transaction()
    {
        $market = Market::factory()->create();
        $user = User::factory()->create(['market_id' => $market->id]);
        $user->assignRole('inputer');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/transactions', [
            'tanggal' => now()->format('Y-m-d'),
            'jenis' => 'pemasukan',
            'subkategori' => 'Retribusi',
            'jumlah' => 50000,
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => ['id', 'tanggal', 'jenis'],
                 ]);
    }
}
```

**Run tests:**
```bash
php artisan test
php artisan test --filter=TransactionApiTest
```

---

## 📝 Next Steps

After completing backend:
1. ✅ All migrations run successfully
2. ✅ Models created with relationships
3. ✅ API endpoints tested with Postman
4. ✅ Filament admin panel accessible
5. ➡️ **Proceed to:** [03-FRONTEND-GUIDE.md](03-FRONTEND-GUIDE.md)

---

**Document Status:** ✅ Complete | **Last Updated:** 2025-01-15
