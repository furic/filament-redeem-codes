<?php

namespace Furic\FilamentRedeemCodes\Filament\Resources\RedeemRewardTypes;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemRewardTypes\Pages\CreateRedeemRewardType;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemRewardTypes\Pages\EditRedeemRewardType;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemRewardTypes\Pages\ListRedeemRewardTypes;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemRewardTypes\Schemas\RedeemRewardTypeForm;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemRewardTypes\Schemas\RedeemRewardTypeTable;
use Furic\FilamentRedeemCodes\Models\RedeemRewardType;

class RedeemRewardTypeResource extends Resource
{
    protected static ?string $model = RedeemRewardType::class;

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $modelLabel = 'Reward Type';

    protected static ?string $pluralModelLabel = 'Reward Types';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationGroup(): ?string
    {
        return config('filament-redeem-codes.filament.navigation_group', 'Redeem Codes');
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    public static function form(Schema $schema): Schema
    {
        return RedeemRewardTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RedeemRewardTypeTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRedeemRewardTypes::route('/'),
            'create' => CreateRedeemRewardType::route('/create'),
            'edit' => EditRedeemRewardType::route('/{record}/edit'),
        ];
    }
}
