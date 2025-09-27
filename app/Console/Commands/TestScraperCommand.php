<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\TaylorSwiftScraperService;
use Illuminate\Console\Command;

class TestScraperCommand extends Command
{
    protected $signature = 'scraper:test {product_id?}';

    protected $description = 'Test the Taylor Swift scraper service';

    public function handle()
    {
        $productId = $this->argument('product_id');

        if ($productId) {
            $product = Product::findOrFail($productId);
            $this->testSingleProduct($product);
        } else {
            $this->testAllProducts();
        }

        return Command::SUCCESS;
    }

    private function testSingleProduct(Product $product): void
    {
        $this->info("Testing scraper for: {$product->name}");
        $this->info("URL: {$product->url}");
        $this->newLine();

        $scraper = new TaylorSwiftScraperService();

        $this->info("Before scraping:");
        $this->displayVariants($product->fresh());

        foreach ($product->variants as $variant) {
            $scraper->checkProductAvailability($variant);
        }

        $this->newLine();
        $this->info("After scraping:");
        $this->displayVariants($product->fresh());
    }

    private function testAllProducts(): void
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->error('No products found in database.');
            return;
        }

        $this->info("Testing scraper for all products:");
        $this->newLine();

        foreach ($products as $product) {
            $this->testSingleProduct($product);
            $this->newLine();
            $this->line('---');
            $this->newLine();
        }
    }

    private function displayVariants(Product $product): void
    {
        $variants = $product->variants;

        if ($variants->isEmpty()) {
            $this->warn("  No variants found");
            return;
        }

        foreach ($variants as $variant) {
            $size = $variant->size ?: 'No Size';
            $status = $variant->is_available ? '✅ In Stock' : '❌ Out of Stock';
            $this->line("  {$size}: {$status}");
        }
    }
}
