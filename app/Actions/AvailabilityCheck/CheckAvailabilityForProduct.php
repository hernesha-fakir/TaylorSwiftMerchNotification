<?php

namespace App\Actions\AvailabilityCheck;

use App\Actions\Scraper\ScrapeProductAvailability;
use App\Models\AvailabilityCheck;
use App\Models\Product;
use App\Models\User;
use App\Notifications\PriceChangedNotification;
use App\Notifications\StockAvailableNotification;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckAvailabilityForProduct
{
    use AsAction;

    public function handle(Product $product)
    {
        $data = $this->getLatestProductData($product);

        // Get the actual previous availability from the latest check BEFORE creating new one
        $previousCheck = $product->availabilityChecks()->latest()->first();
        $previousAvailability = $previousCheck ? $previousCheck->is_available : false;
        $previousPrice = $previousCheck ? $previousCheck->price : null;

        $availabilityCheck = new AvailabilityCheck();
        $availabilityCheck->product_id = $product->id;
        $availabilityCheck->is_available = $data['is_available'];
        $availabilityCheck->price = $data['price'];
        $availabilityCheck->save();

        $this->checkAndNotifyStockAvailable($product, $previousAvailability, $data['is_available']);
        $this->checkAndNotifyPriceChanged($product, $previousPrice, $data['price']);
    }

    private function checkAndNotifyStockAvailable(Product $product, bool $previousAvailability, bool $currentAvailability)
    {
        if (!$previousAvailability && $currentAvailability) {
            $users = User::all();
            $productUrl = $product->url;

            foreach ($users as $user) {
                $user->notify(new StockAvailableNotification($product, $productUrl));
            }
        }
    }

    private function checkAndNotifyPriceChanged(Product $product, ?float $previousPrice, float $currentPrice)
    {
        if ($previousPrice !== null && $previousPrice != $currentPrice) {
            $users = User::all();
            $productUrl = $product->url;

            foreach ($users as $user) {
                $user->notify(new PriceChangedNotification($product, $productUrl, $previousPrice, $currentPrice));
            }
        }
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
