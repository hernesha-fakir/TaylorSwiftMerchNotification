<?php

namespace App\Actions\Product;

use App\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateProduct
{
    use AsAction;

    public function handle($url, $productData, $variantId)
    {
        $product = new Product;

        $product->url = $url;

        $selectedVariant = collect($productData['product']['variants'])->firstWhere('id', $variantId);

        $product->name = $productData['product_data']['title'];
        $product->external_product_id = $productData['product']['id'];
        $product->product_variant_name = $selectedVariant['public_title'];
        $product->product_variant_id = $selectedVariant['id'];
        $product->price = $selectedVariant['price'] / 100;
        $product->image_url = 'https://storeau.taylorswift.com/cdn/shop/'.$productData['product_data']['featuredImage'];

        $product->save();

        return $product;
    }
}
