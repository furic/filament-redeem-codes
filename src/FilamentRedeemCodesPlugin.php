<?php

namespace Furic\FilamentRedeemCodes;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns\RedeemCampaignResource;

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
        $panel->resources([
            RedeemCampaignResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
