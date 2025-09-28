<?php

namespace App\Console\Commands;

use App\Actions\AvailabilityCheck\CheckAvailabilityForProduct;
use App\Models\Product;
use Illuminate\Console\Command;

class CheckAllProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:availability';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check availability and price changes for all tracked products';

    public function handle()
    {
        $products = Product::where('is_tracked', true)->get();

        $this->info("Checking availability for {$products->count()} product(s)...");

        foreach ($products as $product) {

            $this->line("Checking: {$product->name}");
            CheckAvailabilityForProduct::dispatch($product);
        }

        return Command::SUCCESS;
    }
}
