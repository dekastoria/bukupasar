<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Payment extends Model
{
    use HasFactory;

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

    public function scopeByDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('tanggal', $date);
    }

    public function scopeDateRange(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('tanggal', [$from, $to]);
    }

    public function getFormattedJumlahAttribute(): string
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }
}
