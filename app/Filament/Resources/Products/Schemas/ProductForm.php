<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
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
                TextInput::make('product_variant_name'),
                TextInput::make('url')
                    ->url()
                    ->required()
                    ->maxLength(255)
                    ->placeholder('https://storeau.taylorswift.com/products/...'),
                TextInput::make('external_product_id')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Shopify product ID'),
                TextInput::make('price')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->placeholder('0.00'),
                ViewField::make('image_preview')
                    ->view('filament.forms.components.product-image')
                    ->viewData(function ($record) {
                        return [
                            'image_url' => $record?->image_url,
                            'product_name' => $record?->name,
                        ];
                    })
                    ->columnSpanFull(),
            ]);
    }
}
