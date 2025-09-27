<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductImportService
{

    public function __construct(protected TaylorSwiftScraperService $scraper)
    {
    }

    public function importProductFromUrl(string $url): array
    {
        Log::info("Importing product from URL: {$url}");


        dd($this->scraper->getProductData($url));

        $parsedData = $this->parseUrl($url);
        $productData = $this->scrapeProductData($parsedData['baseUrl']);


        return [
            'productData' => $productData,
            'selectedVariantId' => $parsedData['variantId'],
            'availableVariants' => $productData['variants'],
        ];
    }

    public function createProductWithVariant(array $productData, ?string $variantId = null, ?string $variantName = null): Product
    {
        Log::info("Creating product: {$productData['name']} with variant: {$variantName}");

        $productData['product_variant_id'] = $variantId;
        $productData['product_variant_name'] = $variantName;

        return $this->findOrCreateProduct($productData);
    }

    public function parseUrl(string $url): array
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

    public function scrapeProductData(string $url): array
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
        $variants = $this->extractVariants($html);
        $externalProductId = $this->extractProductId($url, $html);

        return [
            'name' => $name,
            'url' => $url,
            'price' => $price,
            'image_url' => $imageUrl,
            'variants' => $variants,
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

    public function extractAvailableVariants(string $html): array
    {
        $variants = [];

        // Look for variant select options (like sizes)
        if (preg_match_all('/<option[^>]*value="([^"]*)"[^>]*>([^<]+)<\/option>/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $variantId = trim($match[1]);
                $variantName = trim($match[2]);

                // Skip empty values or "Select Size" type options
                if (empty($variantId) ||
                    stripos($variantName, 'select') !== false ||
                    stripos($variantName, 'choose') !== false) {
                    continue;
                }

                // Look for size-like patterns
                if (preg_match('/^(XS|S|M|L|XL|2XL|3XL|XXL|XXXL|\d+)$/i', $variantName) ||
                    preg_match('/size/i', $variantName)) {
                    $variants[] = [
                        'id' => $variantId,
                        'name' => $variantName,
                        'type' => 'size'
                    ];
                }
            }
        }

        // Look for variant buttons/links
        if (preg_match_all('/<[^>]*data-variant[^>]*=[\'""]([^\'""]*)[\'"""][^>]*>([^<]*)<\/[^>]*>/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $variantId = trim($match[1]);
                $variantName = trim($match[2]);

                if (!empty($variantId) && !empty($variantName)) {
                    $variants[] = [
                        'id' => $variantId,
                        'name' => $variantName,
                        'type' => 'variant'
                    ];
                }
            }
        }

        // If no variants found but the URL has a variant parameter, include it
        if (empty($variants) && str_contains($url, 'variant=')) {
            preg_match('/variant=([0-9]+)/', $url, $matches);
            if (!empty($matches[1])) {
                $variants[] = [
                    'id' => $matches[1],
                    'name' => 'Default',
                    'type' => 'default'
                ];
            }
        }

        return $variants;
    }

    public function findOrCreateProduct(array $data): Product
    {
        // For the new version, we always create since validation is done at the UI level
        Log::info("Creating new product: {$data['name']}");
        return Product::create($data);
    }


}
