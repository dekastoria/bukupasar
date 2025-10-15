<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
    ];

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

    public function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = $this->settings()->where('key_name', $key)->first();

        return $setting?->value ?? $default;
    }
}
