<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

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

    public function scopeForMarket(Builder $query, int $marketId): Builder
    {
        return $query->where('market_id', $marketId);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $subQuery) use ($search) {
            $subQuery->where('nama', 'like', "%{$search}%")
                ->orWhere('nomor_lapak', 'like', "%{$search}%");
        });
    }

    public function getFormattedOutstandingAttribute(): string
    {
        return 'Rp ' . number_format($this->outstanding, 0, ',', '.');
    }

    public function hasOutstanding(): bool
    {
        return $this->outstanding > 0;
    }
}
