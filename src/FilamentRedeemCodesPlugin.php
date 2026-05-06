<?php

namespace Furic\FilamentRedeemCodes;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns\RedeemCampaignResource;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemRewardTypes\RedeemRewardTypeResource;
use Furic\FilamentRedeemCodes\Models\RedeemRewardType;
use Illuminate\Database\Eloquent\Model;

class FilamentRedeemCodesPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-redeem-codes';
    }

    public function register(Panel $panel): void
    {
        $resources = [RedeemCampaignResource::class];

        if ($this->shouldRegisterRewardTypeResource()) {
            $resources[] = RedeemRewardTypeResource::class;
        }

        $panel->resources($resources);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * Register the Reward Types Resource only when the host has bound an
     * Eloquent model (the bundled RedeemRewardType, or a custom one).
     * Bindings to enums or no binding at all leave the resource hidden.
     */
    protected function shouldRegisterRewardTypeResource(): bool
    {
        $rewardType = config('filament-redeem-codes.reward_type');

        if ($rewardType === null || ! is_string($rewardType) || ! class_exists($rewardType)) {
            return false;
        }

        return is_subclass_of($rewardType, Model::class);
    }
}
