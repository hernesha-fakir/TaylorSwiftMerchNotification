<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->imageHeight(85),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product_variant_name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_available')
                    ->boolean()
                    ->label('Available'),

                TextColumn::make('price')
                    ->money('AUD')
                    ->sortable(),

                TextColumn::make('external_product_id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('is_tracked')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('last_checked')
                    ->dateTime()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
