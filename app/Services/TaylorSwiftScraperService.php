<?php

namespace App\Services;


use HeadlessChromium\BrowserFactory;

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



}
