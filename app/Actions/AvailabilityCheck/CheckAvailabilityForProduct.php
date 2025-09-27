<?php

namespace App\Actions\AvailabilityCheck;

use App\Actions\Scraper\ScrapeProductAvailability;
use App\Models\AvailabilityCheck;
use App\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckAvailabilityForProduct
{
    use AsAction;

    public function handle(Product $product)
    {
        $data = $this->getLatestProductData($product);

        $availabilityCheck = new AvailabilityCheck();
        $availabilityCheck->product_id = $product->id;
        $availabilityCheck->is_available = $data['is_available'];
        $availabilityCheck->price = $data['price'];
        $availabilityCheck->save();

    }

    private function getLatestProductData(Product $product)
    {
        $result = ScrapeProductAvailability::run($product);

        $variantIsAvailable = reset($result['availability'])['variantData'][$product->product_variant_id]['availability'];

        $selectedVariant = collect($result['meta']['product']['variants'])->firstWhere('id', $product->product_variant_id);

        $price = $selectedVariant['price'] / 100;

        return [
            'is_available' => $variantIsAvailable,
            'price' => $price
        ];
    }
}
