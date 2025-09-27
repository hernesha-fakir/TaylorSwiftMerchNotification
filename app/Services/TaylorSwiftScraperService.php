<?php

namespace App\Services;

use App\Models\AvailabilityCheck;
use App\Models\ProductVariant;
use HeadlessChromium\BrowserFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TaylorSwiftScraperService
{


    public function getProductData($url)
    {

        $browser = $this->getBrowser();

        try {
            // creates a new page and navigate to an URL
            $page = $browser->createPage();
            $page->navigate($url)->waitForNavigation();

             //Get the console data
            $evaluation = $page->evaluate('window.ShopifyAnalytics.meta');
            $variantData = $evaluation->getReturnValue();


            $evaluation = $page->evaluate('window.product');
            $productData = $evaluation->getReturnValue();

            $variantData['product_data'] = $productData;


            return $variantData;

        } finally {
            $browser->close();
        }

    }


    public function getProductAvailability($url, $variant)
    {
        $browser = $this->getBrowser();


        try {
            // creates a new page and navigate to an URL
            $page = $browser->createPage();
            $page->navigate($url)->waitForNavigation();

            $evaluation = $page->evaluate('window.ShopifyAnalytics.meta');
            $metaData = $evaluation->getReturnValue();

            $evaluation = $page->evaluate('window.productVariantData');
            $productVariantData = $evaluation->getReturnValue();


            return [
                'meta' => $metaData,
                'availability' => $productVariantData
            ];

        } finally {
            $browser->close();
        }
    }

    private function getBrowser()
    {
        $browserFactory = new BrowserFactory();

        return $browserFactory->createBrowser([
            'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
        ]);
    }


//    public function checkProductAvailability(ProductVariant $variant): bool
//    {
//        try {
//            $product = $variant->product;
//
//            Log::info("Checking availability for {$product->name} - {$variant->size}");
//
//            $variantUrl = $this->buildVariantUrl($product->url, $variant->sku);
//
//            $response = Http::timeout(30)
//                ->withHeaders([
//                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
//                ])
//                ->get($variantUrl);
//
//            $this->logAvailabilityCheck($variant, $response);
//
//            if (!$response->successful()) {
//                Log::warning("Failed to fetch variant page: {$response->status()}");
//                return false;
//            }
//
//            $isAvailable = $this->parseVariantAvailability($response->body());
//
//            if ($variant->is_available !== $isAvailable) {
//                Log::info("Stock status changed for {$product->name} ({$variant->size}): " .
//                    ($isAvailable ? 'Back in stock!' : 'Out of stock'));
//            }
//
//            $variant->update(['is_available' => $isAvailable]);
//
//            return $isAvailable;
//
//        } catch (\Exception $e) {
//            Log::error("Error checking availability for variant {$variant->id}: " . $e->getMessage());
//            $this->logAvailabilityCheck($variant, null, $e->getMessage());
//            return false;
//        }
//    }
//
//    private function buildVariantUrl(string $baseUrl, ?string $sku): string
//    {
//        if (empty($sku)) {
//            return $baseUrl;
//        }
//
//        $separator = str_contains($baseUrl, '?') ? '&' : '?';
//        return $baseUrl . $separator . 'variant=' . $sku;
//    }
//
//
//    private function parseVariantAvailability(string $html): bool
//    {
//        // First check for obvious out of stock indicators
//        $outOfStockIndicators = [
//            '/sold[\\s\\-]?out/i',
//            '/out[\\s\\-]?of[\\s\\-]?stock/i'
//        ];
//
//        foreach ($outOfStockIndicators as $pattern) {
//            if (preg_match($pattern, $html)) {
//                // But if there are multiple "add to cart" references, it might be dynamic
//                $addToCartCount = preg_match_all('/add[\\s\\w]*cart/i', $html);
//                if ($addToCartCount < 3) {
//                    return false; // Definitely out of stock
//                }
//            }
//        }
//
//        // Find all cart-related buttons
//        preg_match_all('/<button[^>]*>/i', $html, $matches);
//
//        $hasEnabledCartButton = false;
//        $hasCartButtons = false;
//
//        foreach ($matches[0] as $button) {
//            // Skip buttons that are clearly not cart buttons
//            if (stripos($button, 'close') !== false ||
//                stripos($button, 'drawer') !== false ||
//                stripos($button, 'modal') !== false) {
//                continue;
//            }
//
//            // Check if this is a cart/add button
//            if ((stripos($button, 'cart') !== false ||
//                 stripos($button, 'name="add"') !== false ||
//                 stripos($button, 'buy') !== false) &&
//                (stripos($button, 'submit') !== false || stripos($button, 'form') !== false)) {
//
//                $hasCartButtons = true;
//
//                // Check if it's NOT disabled
//                if (stripos($button, 'disabled') === false) {
//                    $hasEnabledCartButton = true;
//                    break;
//                }
//            }
//        }
//
//        // If we found cart buttons but they're all disabled, and there's JavaScript/dynamic content,
//        // assume it might be available (will be corrected on next check if wrong)
//        if (!$hasEnabledCartButton && $hasCartButtons) {
//            if (stripos($html, 'ShopifyAnalytics') !== false ||
//                stripos($html, 'data-available') !== false) {
//                // For dynamic sites, default to available and let monitoring correct it
//                return true;
//            }
//        }
//
//        return $hasEnabledCartButton;
//    }
//
//    private function logAvailabilityCheck(ProductVariant $variant, ?Response $response = null, ?string $error = null): void
//    {
//        AvailabilityCheck::create([
//            'product_variant_id' => $variant->id,
//            'was_available' => $variant->is_available,
//            'price_at_check' => $variant->variant_price ?: $variant->product->price,
//            'checked_at' => now(),
//            'http_status' => $response?->status(),
//            'response_time_ms' => $response?->handlerStats()['total_time'] * 1000,
//            'error_message' => $error,
//        ]);
//    }
}
