<?php

namespace App\Filament\Resources\Products\Pages\ViewProduct\Widgets;

use App\Models\AvailabilityCheck;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AvailabilityChecksWidget extends BaseWidget
{
    protected static ?string $heading = 'Latest Availability Checks';

    protected int|string|array $columnSpan = 'full';

    public $record;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AvailabilityCheck::query()
                    ->where('product_id', $this->getRecord()->id)
                    ->latest()
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Checked At')
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
            ->poll('30s');
    }

    protected function getRecord()
    {
        return $this->record;
    }
}
