<?php

namespace App\Filament\Resources\UserTrackedItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UserTrackedItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('productVariant.product.image_url')
                    ->label('Image')
                    ->circular()
                    ->size(50),
                TextColumn::make('productVariant.product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('productVariant.size')
                    ->label('Size')
                    ->placeholder('No Size'),
                IconColumn::make('productVariant.is_available')
                    ->label('In Stock')
                    ->boolean(),
                TextColumn::make('productVariant.product.price')
                    ->label('Price')
                    ->money('AUD'),
                TextColumn::make('created_at')
                    ->label('Tracking Since')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
