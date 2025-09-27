<?php

namespace App\Actions\Scraper;

use App\Services\TaylorSwiftScraperService;
use Lorisleiva\Actions\Concerns\AsAction;

class ScrapeProductData
{
    use AsAction;

    public function __construct(protected TaylorSwiftScraperService $scraper)
    {
    }

    public function handle($url)
    {
        return $this->scraper->getProductData($url);
    }
}
