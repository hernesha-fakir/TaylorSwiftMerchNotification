<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AvailabilityChecksRelationManager extends RelationManager
{
    protected static string $relationship = 'availabilityChecks';

    protected $listeners = ['refreshRelation' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('created_at')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Check Date')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('price')
                    ->label('Price')
                    ->money('AUD')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->headerActions([
                // No create/edit actions since these are generated automatically
            ])
            ->recordActions([
                // No edit/delete actions to prevent manual modification
            ])
            ->bulkActions([
                // No bulk actions
            ]);
    }
}
