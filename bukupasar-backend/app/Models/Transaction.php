<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Transaction extends Model
{
    use HasFactory;

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
        if ($user->hasRole(['admin_pusat', 'admin_pasar'])) {
            return true;
        }

        if ($user->hasRole('inputer')) {
            $isOwner = $this->created_by === $user->id;
            $createdAt = $this->created_at ?? now();

            return $isOwner && $createdAt->diffInHours(now()) <= 24;
        }

        return false;
    }

    public function getFormattedJumlahAttribute(): string
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }
}
