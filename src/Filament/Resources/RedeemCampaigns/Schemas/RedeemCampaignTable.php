<?php

namespace Furic\FilamentRedeemCodes\Filament\Resources\RedeemCampaigns\Schemas;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RedeemCampaignTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('codes_count')
                    ->counts('codes')
                    ->label('Codes')
                    ->alignCenter(),

                TextColumn::make('rewards_count')
                    ->counts('rewards')
                    ->label('Rewards')
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->state(fn ($record) => $record->isActive())
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('start_at')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('end_at')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All')
                    ->trueLabel('Currently active')
                    ->falseLabel('Not active')
                    ->queries(
                        true: fn ($query) => $query
                            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', now()))
                            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now())),
                        false: fn ($query) => $query
                            ->where(fn ($q) => $q
                                ->where('start_at', '>', now())
                                ->orWhere('end_at', '<', now())),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
