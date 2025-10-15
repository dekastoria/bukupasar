<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasFactory;

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

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

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
