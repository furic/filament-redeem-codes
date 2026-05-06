<?php

namespace Furic\FilamentRedeemCodes\Filament\Resources\RedeemRewardTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemRewardTypes\RedeemRewardTypeResource;

class ListRedeemRewardTypes extends ListRecords
{
    protected static string $resource = RedeemRewardTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
