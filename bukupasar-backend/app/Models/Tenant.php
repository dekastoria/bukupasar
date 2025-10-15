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
        'rental_type_id',
        'nama',
        'nomor_lapak',
        'hp',
        'alamat',
        'foto_profile',
        'foto_ktp',
        'outstanding',
        'tanggal_mulai_sewa',
        'tanggal_akhir_sewa',
        'tarif_sewa',
        'periode_sewa',
        'catatan_sewa',
    ];

    protected $casts = [
        'outstanding' => 'integer',
        'tanggal_mulai_sewa' => 'date',
        'tanggal_akhir_sewa' => 'date',
        'tarif_sewa' => 'integer',
    ];

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function rentalType(): BelongsTo
    {
        return $this->belongsTo(RentalType::class);
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

    public function getFormattedTarifSewaAttribute(): string
    {
        return 'Rp '.number_format($this->tarif_sewa, 0, ',', '.');
    }

    public function hasOutstanding(): bool
    {
        return $this->outstanding > 0;
    }

    public function isSewaActive(): bool
    {
        if (!$this->tanggal_mulai_sewa || !$this->tanggal_akhir_sewa) {
            return false;
        }

        $today = now()->startOfDay();

        return $this->tanggal_mulai_sewa <= $today && $this->tanggal_akhir_sewa >= $today;
    }

    public function isSewaExpired(): bool
    {
        if (!$this->tanggal_akhir_sewa) {
            return false;
        }

        return $this->tanggal_akhir_sewa < now()->startOfDay();
    }

    public function getDaysUntilSewaExpires(): ?int
    {
        if (!$this->tanggal_akhir_sewa) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->tanggal_akhir_sewa, false);
    }
}
