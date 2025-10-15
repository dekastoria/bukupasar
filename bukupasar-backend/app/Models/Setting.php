<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

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
        $setting = static::where('market_id', $marketId)
            ->where('key_name', $key)
            ->first();

        return $setting?->value ?? $default;
    }

    public static function setValue(int $marketId, string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['market_id' => $marketId, 'key_name' => $key],
            ['value' => $value, 'updated_at' => now()]
        );
    }
}
