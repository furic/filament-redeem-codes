<?php

namespace Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Furic\FilamentRedeemCodes\Actions\GenerateCodes;
use Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns\RedeemCampaignResource;

class EditRedeemCampaign extends EditRecord
{
    protected static string $resource = RedeemCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_codes')
                ->label('Generate codes')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->schema([
                    TextInput::make('count')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(10000)
                        ->default(10)
                        ->required(),
                    TextInput::make('prefix')
                        ->maxLength(8)
                        ->helperText('Optional. Uppercased automatically. Counts toward total length.'),
                    Checkbox::make('reusable')
                        ->helperText('A reusable code is generated once and can be redeemed many times. Forces count to 1.'),
                ])
                ->action(function (array $data, GenerateCodes $generator): void {
                    $codes = $generator->execute(
                        campaign: $this->record,
                        count: (int) ($data['count'] ?? 1),
                        prefix: $data['prefix'] ?? null,
                        reusable: (bool) ($data['reusable'] ?? false),
                    );

                    Notification::make()
                        ->title("Generated {$codes->count()} code(s)")
                        ->body($codes->pluck('code')->take(5)->implode(', ') . ($codes->count() > 5 ? '…' : ''))
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}
