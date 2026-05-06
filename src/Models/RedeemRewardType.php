<?php

namespace Furic\FilamentRedeemCodes\Models;

use Furic\FilamentRedeemCodes\Contracts\RewardType;
use Illuminate\Database\Eloquent\Model;

/**
 * Optional model-backed reward type registry. Bind to
 * config('filament-redeem-codes.reward_type') as an alternative to
 * a backed enum when reward types should be admin-managed.
 */
class RedeemRewardType extends Model implements RewardType
{
    protected $fillable = ['key', 'label', 'icon', 'description', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function getTable(): string
    {
        return config('filament-redeem-codes.table_names.reward_types', 'redeem_reward_types');
    }

    /**
     * Resolve a row by its key — used by RedeemCodeReward when looking up
     * display metadata for a stored type string.
     */
    public static function findByKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }
}
