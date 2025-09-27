<?php

namespace App\Filament\Resources\UserTrackedItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use App\Models\Product;
use App\Models\ProductVariant;

class UserTrackedItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(auth()->id()),

                Grid::make(2)
                    ->schema([
                        ViewField::make('product_image')
                            ->view('filament.forms.components.product-image')
                            ->viewData(function ($get, $record) {
                                $productId = $get('product_id');
                                if (!$productId && $record) {
                                    $productId = $record->productVariant?->product_id;
                                }

                                if ($productId) {
                                    $product = Product::find($productId);
                                    return [
                                        'image_url' => $product?->image_url,
                                        'product_name' => $product?->name,
                                    ];
                                }

                                return ['image_url' => null, 'product_name' => null];
                            })
                            ->dehydrated(false)
                            ->columnSpan(1),

                        Grid::make(1)
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::all()->pluck('name', 'id'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (callable $set) => $set('product_variant_id', null))
                                    ->searchable()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record && $record->productVariant) {
                                            $component->state($record->productVariant->product_id);
                                        }
                                    }),

                                Select::make('product_variant_id')
                                    ->label('Size/Variant')
                                    ->options(function ($get, $record): array {
                                        $productId = $get('product_id') ?? $record?->productVariant?->product_id;
                                        if (!$productId) {
                                            return [];
                                        }

                                        return ProductVariant::where('product_id', $productId)
                                            ->get()
                                            ->mapWithKeys(function ($variant) {
                                                $label = $variant->size ?: 'No Size';
                                                if (!$variant->is_available) {
                                                    $label .= ' (Out of Stock)';
                                                }
                                                return [$variant->id => $label];
                                            })
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->helperText('Select the specific size/variant you want to track'),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
