<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected const CACHE_MISS = '__SETTING_CACHE_MISS__';

    public $timestamps = false;
    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'market_id',
        'key_name',
        'value',
        'updated_at',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    protected static array $localCache = [];

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    protected function setKeysForSaveQuery($query)
    {
        $query->where('market_id', $this->getAttribute('market_id'))
            ->where('key_name', $this->getAttribute('key_name'));

        return $query;
    }

    public function scopeForMarket(Builder $query, int $marketId): Builder
    {
        return $query->where('market_id', $marketId);
    }

    public static function getValue(int $marketId, string $key, mixed $default = null): mixed
    {
        $cacheKey = self::cacheKey($marketId, $key);

        if (array_key_exists($cacheKey, self::$localCache)) {
            return self::$localCache[$cacheKey] === self::CACHE_MISS
                ? $default
                : self::$localCache[$cacheKey];
        }

        $value = static::where('market_id', $marketId)
            ->where('key_name', $key)
            ->value('value');

        if ($value === null) {
            self::$localCache[$cacheKey] = self::CACHE_MISS;

            return $default;
        }

        self::$localCache[$cacheKey] = $value;

        return $value;
    }

    public static function setValue(int $marketId, string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['market_id' => $marketId, 'key_name' => $key],
            ['value' => $value, 'updated_at' => now()]
        );

        self::$localCache[self::cacheKey($marketId, $key)] = $value;
    }

    public static function clearCache(int $marketId, ?string $key = null): void
    {
        if ($key !== null) {
            unset(self::$localCache[self::cacheKey($marketId, $key)]);

            return;
        }

        foreach (array_keys(self::$localCache) as $cacheKey) {
            if (str_starts_with($cacheKey, $marketId.'|')) {
                unset(self::$localCache[$cacheKey]);
            }
        }
    }

    protected static function cacheKey(int $marketId, string $key): string
    {
        return $marketId.'|'.$key;
    }
}
