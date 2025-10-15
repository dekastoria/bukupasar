<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upload extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'market_id',
        'user_id',
        'path',
        'mime',
        'size',
        'created_at',
    ];

    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
    ];

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForMarket(Builder $query, int $marketId): Builder
    {
        return $query->where('market_id', $marketId);
    }
}
