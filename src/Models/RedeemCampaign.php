<?php

namespace Furic\FilamentRedeemCodes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class RedeemCampaign extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'start_at', 'end_at'];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('filament-redeem-codes.table_names.campaigns', 'redeem_campaigns');
    }

    public function codes(): HasMany
    {
        return $this->hasMany(RedeemCode::class, 'campaign_id');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(RedeemCodeReward::class, 'campaign_id');
    }

    public function isActive(?Carbon $at = null): bool
    {
        $at ??= now();

        if ($this->start_at && $at->lt($this->start_at)) {
            return false;
        }

        if ($this->end_at && $at->gt($this->end_at)) {
            return false;
        }

        return true;
    }
}
