<?php

namespace Furic\FilamentRedeemCodes\Filament\Resources\RedeemRewardTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RedeemRewardTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    TextInput::make('label')
                        ->required()
                        ->maxLength(191)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, callable $get, callable $set) {
                            if (empty($get('key')) && filled($state)) {
                                $set('key', Str::snake(Str::lower($state)));
                            }
                        })
                        ->helperText('Human-readable name shown in admin (e.g. "Coins", "Gems").'),

                    TextInput::make('key')
                        ->required()
                        ->maxLength(64)
                        ->unique(ignoreRecord: true)
                        ->alphaDash()
                        ->helperText('Stable machine identifier sent to clients (e.g. "coins"). Avoid renaming once codes have been issued.'),

                    TextInput::make('icon')
                        ->maxLength(64)
                        ->placeholder('heroicon-o-currency-dollar')
                        ->helperText('Optional Heroicon name. Display-only metadata for client apps.'),

                    Textarea::make('description')
                        ->rows(2)
                        ->maxLength(1000)
                        ->columnSpanFull(),

                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower values appear first in dropdowns.'),
                ])
                ->columns(2),
        ]);
    }
}
