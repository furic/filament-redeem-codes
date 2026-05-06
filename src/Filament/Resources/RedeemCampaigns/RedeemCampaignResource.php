<?php

namespace Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns\Pages\CreateRedeemCampaign;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns\Pages\EditRedeemCampaign;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns\Pages\ListRedeemCampaigns;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns\Schemas\RedeemCampaignForm;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns\Schemas\RedeemCampaignTable;
use Furic\FilamentRedeemCodes\Models\RedeemCampaign;

class RedeemCampaignResource extends Resource
{
    protected static ?string $model = RedeemCampaign::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Redeem Campaign';

    protected static ?string $pluralModelLabel = 'Redeem Campaigns';

    public static function getNavigationIcon(): ?string
    {
        return config('filament-redeem-codes.filament.navigation_icon', 'heroicon-o-ticket');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('filament-redeem-codes.filament.navigation_group', 'Redeem Codes');
    }

    public static function form(Schema $schema): Schema
    {
        return RedeemCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RedeemCampaignTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRedeemCampaigns::route('/'),
            'create' => CreateRedeemCampaign::route('/create'),
            'edit' => EditRedeemCampaign::route('/{record}/edit'),
        ];
    }
}
