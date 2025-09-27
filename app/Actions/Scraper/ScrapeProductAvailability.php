<?php

namespace App\Actions\Scraper;

use App\Models\Product;
use App\Services\TaylorSwiftScraperService;
use Lorisleiva\Actions\Concerns\AsAction;

class ScrapeProductAvailability
{
    use AsAction;

    public function __construct(protected TaylorSwiftScraperService $scraper)
    {
    }

    public function handle(Product $product)
    {
        return $this->scraper->getProductAvailability($product->variant_url, $product->product_variant_name);
    }
}
