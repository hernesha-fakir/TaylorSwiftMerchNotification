<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\UserTrackedItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductImportService
{
    public function importFromUrl(string $url, int $userId): UserTrackedItem
    {
        Log::info("Importing product from URL: {$url}");

        $parsedData = $this->parseUrl($url);
        $productData = $this->scrapeProductData($parsedData['baseUrl']);

        $product = $this->findOrCreateProduct($productData);
        $variant = $this->findOrCreateVariant($product, $parsedData['variantId'], $parsedData['baseUrl']);

        return $this->createTrackedItem($userId, $variant);
    }

    private function parseUrl(string $url): array
    {
        $baseUrl = strtok($url, '?');

        $variantId = null;
        if (str_contains($url, 'variant=')) {
            preg_match('/variant=([0-9]+)/', $url, $matches);
            $variantId = $matches[1] ?? null;
        }

        return [
            'baseUrl' => $baseUrl,
            'variantId' => $variantId
        ];
    }

    private function scrapeProductData(string $url): array
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
            ])
            ->get($url);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch product page: {$response->status()}");
        }

        $html = $response->body();

        $name = $this->extractProductName($html);
        $price = $this->extractPrice($html);
        $imageUrl = $this->extractImageUrl($html);
        $externalProductId = $this->extractProductId($url, $html);

        return [
            'name' => $name,
            'url' => $url,
            'price' => $price,
            'image_url' => $imageUrl,
            'external_product_id' => $externalProductId,
        ];
    }

    private function extractProductName(string $html): string
    {
        $patterns = [
            '/<h1[^>]*class="[^"]*product[^"]*title[^"]*"[^>]*>([^<]+)<\/h1>/i',
            '/<h1[^>]*class="[^"]*title[^"]*"[^>]*>([^<]+)<\/h1>/i',
            '/<h1[^>]*>([^<]+)<\/h1>/i',
            '/<title>([^<]+)<\/title>/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $title = trim($matches[1]);
                $title = str_replace(' – Taylor Swift AU', '', $title);
                $title = str_replace(' | Taylor Swift Official Store', '', $title);
                if ($title !== 'Unknown Product' && !empty($title)) {
                    return $title;
                }
            }
        }

        return 'Unknown Product';
    }

    private function extractPrice(string $html): ?float
    {
        $patterns = [
            '/<span[^>]*class="[^"]*price[^"]*"[^>]*>\$?([0-9,]+\.?[0-9]*)<\/span>/i',
            '/<span[^>]*class="[^"]*money[^"]*"[^>]*>\$?([0-9,]+\.?[0-9]*)<\/span>/i',
            '/<div[^>]*class="[^"]*price[^"]*"[^>]*>\$?([0-9,]+\.?[0-9]*)<\/div>/i',
            '/\$([0-9,]+\.?[0-9]*)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                return (float) str_replace(',', '', $matches[1]);
            }
        }

        return null;
    }

    private function extractImageUrl(string $html): ?string
    {
        // Find all img tags and filter for product images
        preg_match_all('/<img[^>]*src="([^"]+)"[^>]*alt="([^"]*)"[^>]*>/i', $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $url = html_entity_decode($match[1]);
            $alt = $match[2];

            // Skip logos and other non-product images
            if (stripos($alt, 'taylor swift') !== false && stripos($alt, 'store') !== false) {
                continue;
            }
            if (stripos($alt, 'logo') !== false) {
                continue;
            }

            // Prefer images with product-like alt text or in files directory
            if (stripos($url, '/files/') !== false &&
                (preg_match('/\.(png|jpg|jpeg|webp)/i', $url))) {

                if (str_starts_with($url, '//')) {
                    return 'https:' . $url;
                } elseif (str_starts_with($url, '/')) {
                    return 'https://storeau.taylorswift.com' . $url;
                } elseif (str_starts_with($url, 'http')) {
                    return $url;
                }
            }
        }

        return null;
    }

    private function extractProductId(string $url, string $html): ?string
    {
        if (preg_match('/\/products\/([^?\/]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractVariantSize(string $html): ?string
    {
        if (preg_match('/<span[^>]*class="[^"]*selected[^"]*"[^>]*>([^<]+)<\/span>/i', $html, $matches)) {
            $size = trim($matches[1]);
            if (in_array(strtoupper($size), ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', 'XXL', 'XXXL'])) {
                return $size;
            }
        }

        return null;
    }

    private function findOrCreateProduct(array $data): Product
    {
        $product = Product::where('url', $data['url'])
            ->orWhere('external_product_id', $data['external_product_id'])
            ->first();

        if (!$product) {
            Log::info("Creating new product: {$data['name']}");
            $product = Product::create($data);
        }

        return $product;
    }

    private function findOrCreateVariant(Product $product, ?string $variantId, string $url): ProductVariant
    {
        if (!$variantId) {
            $variant = $product->variants()->whereNull('size')->first();
            if (!$variant) {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
                    ])
                    ->get($url);

                $isAvailable = true;
                if ($response->successful()) {
                    $isAvailable = $this->parseVariantAvailability($response->body());
                }

                Log::info("Creating variant without size for product: {$product->name}, available: " . ($isAvailable ? 'yes' : 'no'));
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => null,
                    'sku' => null,
                    'is_available' => $isAvailable,
                ]);
            }
            return $variant;
        }

        $variant = $product->variants()->where('sku', $variantId)->first();
        if ($variant) {
            return $variant;
        }

        $variantUrl = $url . '?variant=' . $variantId;
        $response = Http::timeout(30)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
            ])
            ->get($variantUrl);

        $size = null;
        if ($response->successful()) {
            $size = $this->extractVariantSize($response->body());
        }

        Log::info("Creating new variant for product: {$product->name}, size: {$size}, SKU: {$variantId}");

        return ProductVariant::create([
            'product_id' => $product->id,
            'size' => $size,
            'sku' => $variantId,
            'is_available' => $this->parseVariantAvailability($response->body() ?? ''),
        ]);
    }

    private function createTrackedItem(int $userId, ProductVariant $variant): UserTrackedItem
    {
        $existing = UserTrackedItem::where('user_id', $userId)
            ->where('product_variant_id', $variant->id)
            ->withTrashed()
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                Log::info("Restoring previously deleted tracked item for user {$userId}, variant {$variant->id}");
                $existing->restore();
                return $existing;
            } else {
                Log::info("User {$userId} is already tracking variant {$variant->id}");
                return $existing;
            }
        }

        Log::info("Creating tracked item for user {$userId}, variant: {$variant->product->name} - {$variant->size}");

        return UserTrackedItem::create([
            'user_id' => $userId,
            'product_variant_id' => $variant->id,
        ]);
    }

    private function parseVariantAvailability(string $html): bool
    {
        // First check for obvious out of stock indicators
        $outOfStockIndicators = [
            '/sold[\\s\\-]?out/i',
            '/out[\\s\\-]?of[\\s\\-]?stock/i'
        ];

        foreach ($outOfStockIndicators as $pattern) {
            if (preg_match($pattern, $html)) {
                // But if there are multiple "add to cart" references, it might be dynamic
                $addToCartCount = preg_match_all('/add[\\s\\w]*cart/i', $html);
                if ($addToCartCount < 3) {
                    return false; // Definitely out of stock
                }
            }
        }

        // Find all cart-related buttons
        preg_match_all('/<button[^>]*>/i', $html, $matches);

        $hasEnabledCartButton = false;
        $hasCartButtons = false;

        foreach ($matches[0] as $button) {
            // Skip buttons that are clearly not cart buttons
            if (stripos($button, 'close') !== false ||
                stripos($button, 'drawer') !== false ||
                stripos($button, 'modal') !== false) {
                continue;
            }

            // Check if this is a cart/add button
            if ((stripos($button, 'cart') !== false ||
                 stripos($button, 'name="add"') !== false ||
                 stripos($button, 'buy') !== false) &&
                (stripos($button, 'submit') !== false || stripos($button, 'form') !== false)) {

                $hasCartButtons = true;

                // Check if it's NOT disabled
                if (stripos($button, 'disabled') === false) {
                    $hasEnabledCartButton = true;
                    break;
                }
            }
        }

        // If we found cart buttons but they're all disabled, and there's JavaScript/dynamic content,
        // assume it might be available (will be corrected on next check if wrong)
        if (!$hasEnabledCartButton && $hasCartButtons) {
            if (stripos($html, 'ShopifyAnalytics') !== false ||
                stripos($html, 'data-available') !== false) {
                // For dynamic sites, default to available and let monitoring correct it
                return true;
            }
        }

        return $hasEnabledCartButton;
    }
}