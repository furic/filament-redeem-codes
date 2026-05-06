<?php

namespace Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class RedeemCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Campaign')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(191),

                    Textarea::make('description')
                        ->rows(2)
                        ->maxLength(1000)
                        ->columnSpanFull(),

                    DateTimePicker::make('start_at')
                        ->seconds(false)
                        ->helperText('Optional. Codes cannot be redeemed before this time.'),

                    DateTimePicker::make('end_at')
                        ->seconds(false)
                        ->after('start_at')
                        ->helperText('Optional. Codes expire after this time.'),
                ])
                ->columns(2),

            Section::make('Rewards')
                ->description('All codes generated for this campaign share these rewards.')
                ->schema([
                    Repeater::make('rewards')
                        ->relationship()
                        ->schema([
                            self::rewardTypeField(),
                            TextInput::make('amount')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required(),
                            TextInput::make('item_id')
                                ->numeric()
                                ->label('Item ID')
                                ->helperText('Optional reference to a host-app item.'),
                            KeyValue::make('payload')
                                ->label('Payload')
                                ->keyLabel('Key')
                                ->valueLabel('Value')
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->defaultItems(1)
                        ->reorderable(false)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => isset($state['type'])
                            ? "{$state['type']} × " . ($state['amount'] ?? 1)
                            : null),
                ]),
        ]);
    }

    protected static function rewardTypeField(): TextInput|Select
    {
        $rewardType = config('filament-redeem-codes.reward_type');

        if ($rewardType !== null && enum_exists($rewardType)) {
            return Select::make('type')
                ->options(collect($rewardType::cases())->mapWithKeys(
                    fn ($case) => [$case->value => $case->name]
                ))
                ->required();
        }

        if ($rewardType !== null && class_exists($rewardType) && is_subclass_of($rewardType, Model::class)) {
            return Select::make('type')
                ->options(fn () => $rewardType::query()
                    ->orderBy('sort_order')
                    ->orderBy('label')
                    ->pluck('label', 'key')
                    ->all())
                ->searchable()
                ->required()
                ->helperText('Manage available types under "Reward Types".');
        }

        return TextInput::make('type')
            ->required()
            ->maxLength(64)
            ->helperText('Bind a backed enum or Eloquent model to filament-redeem-codes.reward_type for a typed dropdown.');
    }
}
