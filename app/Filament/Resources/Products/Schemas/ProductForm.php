<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(3),
                TextInput::make('url')
                    ->url()
                    ->required()
                    ->maxLength(255)
                    ->placeholder('https://storeau.taylorswift.com/products/...'),
                TextInput::make('external_product_id')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Shopify product ID'),
                TextInput::make('price')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->placeholder('0.00'),
                TextInput::make('image_url')
                    ->url()
                    ->maxLength(255)
                    ->placeholder('https://example.com/image.jpg'),
            ]);
    }
}
