<?php

namespace Furic\FilamentRedeemCodes\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RedeemCode extends Model
{
    use HasFactory;

    protected $fillable = ['campaign_id', 'code', 'reusable', 'redeemed_at'];

    protected $casts = [
        'reusable' => 'boolean',
        'redeemed_at' => 'datetime',
    ];

    protected $appends = ['rewards'];

    public function getTable(): string
    {
        return config('filament-redeem-codes.table_names.codes', 'redeem_codes');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(RedeemCampaign::class, 'campaign_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(RedeemCodeHistory::class);
    }

    /**
     * Rewards live on the campaign (a code inherits its batch's reward set).
     * Exposed as an attribute so API resources can include them inline.
     */
    public function getRewardsAttribute(): Collection
    {
        return $this->campaign?->rewards ?? new Collection();
    }

    public function isRedeemed(): bool
    {
        return $this->redeemed_at !== null;
    }

    public function isReusable(): bool
    {
        return (bool) $this->reusable;
    }

    public function markRedeemed(): void
    {
        if ($this->isReusable()) {
            return;
        }

        $this->forceFill(['redeemed_at' => now()])->save();
    }

    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
