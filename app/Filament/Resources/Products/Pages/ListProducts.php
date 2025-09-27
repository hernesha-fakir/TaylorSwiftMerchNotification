<?php

namespace App\Filament\Resources\Products\Pages;

use App\Actions\Scraper\ScrapeProductData;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importFromUrl')
                ->label('Import from URL')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->schema([
                    TextInput::make('url')
                        ->label('Taylor Swift Product URL')
                        ->url()
                        ->required()
                        ->placeholder('https://storeau.taylorswift.com/products/...')
                        ->helperText('Paste any Taylor Swift product URL and we\'ll automatically detect available variants!')
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (empty($state)) {
                                $set('variant_options', []);
                                $set('selected_variant', null);
                                $set('has_url', false);

                                return;
                            }

                            $set('has_url', true);
                            // Clear previous variants immediately to show loading state
                            $set('variant_options', []);

                            try {
                                $result = ScrapeProductData::run($state);
                                $variants = $result['product']['variants'];

                                if (count($variants) > 1) {
                                    $options = [];
                                    foreach ($variants as $variant) {
                                        $options[$variant['id']] = $variant['public_title'];
                                    }
                                    $set('variant_options', $options);
                                    $set('selected_variant', array_key_first($options));
                                } elseif (count($variants) === 1) {
                                    $set('variant_options', []);
                                    $set('selected_variant', $variants[0]['id']);
                                    $set('variant_name', $variants[0]['public_title']);
                                } else {
                                    $set('variant_options', []);
                                    $set('selected_variant', null);
                                    $set('variant_name', null);
                                }
                            } catch (\Exception $e) {
                                $set('variant_options', []);
                                $set('selected_variant', null);
                            }
                        })
                        ->columnSpanFull(),
                    Hidden::make('has_url')
                        ->default(false),
                    Hidden::make('selected_variant')
                        ->visible(fn (Get $get): bool => empty($get('variant_options'))),
                    Select::make('selected_variant')
                        ->label('Select Variant')
                        ->options(fn (Get $get): array => $get('variant_options') ?? [])
                        ->disabled(fn (Get $get): bool => empty($get('variant_options')))
                        ->helperText(fn (Get $get) => ! $get('has_url')
                                ? null
                                : (empty($get('variant_options'))
                                    ? '🔄 Loading variants...'
                                    : 'Select a variant to import'
                                )
                        )
                        ->required(fn (Get $get): bool => ! empty($get('variant_options')))
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            $options = $get('variant_options') ?? [];
                            if (isset($options[$state])) {
                                $variantName = $options[$state];
                                // Extract just the variant name without the type suffix
                                $set('variant_name', preg_replace('/\s+\([^)]+\)$/', '', $variantName));
                            }
                        }),
                ])
                ->action(function (array $data) {
                    try {
                        $result = ScrapeProductData::run($data['url']);
                        $productData = $result;

                        $variantId = $data['selected_variant'] ?? null;
                        $selectedVariant = collect($productData['product']['variants'])->firstWhere('id', $variantId);

                        // Check if this product/variant combination already exists
                        $existingProduct = Product::where('external_product_id', $productData['product']['id'])->where('product_variant_id', $variantId)->first();

                        if ($existingProduct) {
                            Notification::make()
                                ->warning()
                                ->title('Product Already Exists')
                                ->body("This product - {$selectedVariant['public_name']} has already been imported: {$existingProduct->name}")
                                ->send();

                            return;
                        }

                        $product = \App\Actions\Product\CreateProduct::run($data['url'], $productData, $variantId);

                        Notification::make()
                            ->success()
                            ->title("{$product->name}{$product->product_variant_name} Imported Successfully!")
                            ->body('Tracking has automatically been enabled')
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));

                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Failed to Import Product')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}
