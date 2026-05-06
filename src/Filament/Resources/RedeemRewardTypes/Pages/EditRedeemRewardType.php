<?php

namespace Furic\FilamentRedeemCodes\Filament\Resources\RedeemRewardTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemRewardTypes\RedeemRewardTypeResource;

class EditRedeemRewardType extends EditRecord
{
    protected static string $resource = RedeemRewardTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
