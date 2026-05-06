<?php

namespace Furic\FilamentRedeemCodes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedeemCodeReward extends Model
{
    use HasFactory;

    protected $fillable = ['campaign_id', 'type', 'amount', 'item_id', 'payload'];

    public function getTable(): string
    {
        return config('filament-redeem-codes.table_names.rewards', 'redeem_code_rewards');
    }

    protected function casts(): array
    {
        $rewardType = config('filament-redeem-codes.reward_type');

        return array_filter([
            'amount' => 'integer',
            'item_id' => 'integer',
            'payload' => 'array',
            'type' => $rewardType && enum_exists($rewardType) ? $rewardType : null,
        ]);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(RedeemCampaign::class, 'campaign_id');
    }
}
