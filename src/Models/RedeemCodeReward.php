<?php

namespace Furic\FilamentRedeemCodes\Models;

use BackedEnum;
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

    /**
     * Resolve a human-readable label for the reward type.
     *  - Enum binding: returns the case name
     *  - Model binding: returns the looked-up label (or null if row missing)
     *  - No binding: returns null (label would just duplicate `type`)
     */
    public function getTypeLabelAttribute(): ?string
    {
        $rawType = $this->getRawOriginal('type') ?? $this->attributes['type'] ?? null;

        if ($rawType === null) {
            return null;
        }

        $rewardType = config('filament-redeem-codes.reward_type');

        if ($rewardType !== null && enum_exists($rewardType)) {
            return $this->type instanceof BackedEnum ? $this->type->name : null;
        }

        if ($rewardType !== null && class_exists($rewardType) && is_subclass_of($rewardType, Model::class)) {
            return $rewardType::query()->where('key', $rawType)->first()?->label;
        }

        return null;
    }
}
