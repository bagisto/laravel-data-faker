<?php

namespace Webkul\DataFaker\Repositories;

use Webkul\Product\Repositories\ProductRepository as Product;
use DB;

/**
 * Product Reposotory
 *
 * @copyright 2019 Webkul Software Pvt Ltd (http://www.webkul.com)
 */
class RelatedProductRepository
{
    /**
     * ProductRepository object
     *
     * @var array
     */
    protected $Product;


    /**
     * Create a new instance.
     *
     * @param  Webkul\Core\Repositories\CountryRepository      $countryRepository
     * @param  \Webkul\Core\Repositories\CountryStateRepository $countryStateRepository
     * * @param  \Webkul\Attribute\Repositories\AttributeFamilyRepository $attributeFamilyRepository
     * @return void
     */
    public function __construct(
        Product $product
    )
    {
        $this->product = $product;
    }

    /**
     * Dummy Data For Customer Table.
     *
     * @return mixed
     */
    public function getRelatedProducts($product)
    {
        // $productCount = $this->product->count();

        // if ($productCount > 4) {

        //     $products = $this->product->get()->random(4);

        //     foreach ($products as $relatedProduct) {
        //         if ( $product['product_id'] != $relatedProduct->id) {
        //             DB::table('product_up_sells')->insert([
        //                 'parent_id' => $product['product_id'],
        //                 'child_id' => $relatedProduct->id,
        //             ]);

        //             DB::table('product_cross_sells')->insert([
        //                 'parent_id' => $product['product_id'],
        //                 'child_id' => $relatedProduct->id,
        //             ]);
        //         }
        //     }
        // }
    }
}
