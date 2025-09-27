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

        if ($products->isEmpty()) {
            $this->warn('No tracked products found.');
            return;
        }

        $this->info("Checking availability for {$products->count()} product(s)...");

        $successCount = 0;
        $failCount = 0;

        foreach ($products as $product) {
            try {
                $this->line("Checking: {$product->name}");

                CheckAvailabilityForProduct::run($product);

                $successCount++;
                $this->info("✅ Success");

            } catch (\Exception $e) {
                $failCount++;
                $this->error("❌ Failed: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Availability check completed:");
        $this->line("✅ Successful: {$successCount}");
        $this->line("❌ Failed: {$failCount}");

        if ($failCount > 0) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
