<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('size')
                    ->maxLength(255)
                    ->placeholder('S, M, L, XL, etc.')
                    ->helperText('Leave empty for non-sized items'),
                TextInput::make('variant_price')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->placeholder('Override product price')
                    ->helperText('Leave empty to use product price'),
                TextInput::make('sku')
                    ->maxLength(255)
                    ->placeholder('Product SKU'),
                Toggle::make('is_available')
                    ->label('In Stock')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('size')
            ->columns([
                TextColumn::make('size')
                    ->searchable()
                    ->placeholder('No Size'),
                IconColumn::make('is_available')
                    ->boolean()
                    ->label('Available'),
                TextColumn::make('variant_price')
                    ->money('AUD')
                    ->placeholder('Uses product price'),
                TextColumn::make('sku')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('active_tracked_count')
                    ->label('Tracked by')
                    ->getStateUsing(function ($record) {
                        $activeTrackers = $record->trackedItems()
                            ->with('user')
                            ->whereNull('deleted_at')
                            ->get();

                        $count = $activeTrackers->count();

                        if ($count === 0) {
                            return '';
                        }

                        $userNames = $activeTrackers->pluck('user.name')->join(', ');

                        return $count === 1
                            ? "1 user: {$userNames}"
                            : "{$count} users: {$userNames}";
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
