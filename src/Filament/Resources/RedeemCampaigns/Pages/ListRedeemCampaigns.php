<?php

namespace Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns\RedeemCampaignResource;

class ListRedeemCampaigns extends ListRecords
{
    protected static string $resource = RedeemCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
