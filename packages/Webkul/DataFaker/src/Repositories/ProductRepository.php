<?php

namespace Webkul\DataFaker\Repositories;

use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Product\Repositories\ProductRepository as Product;

/**
 * Product Reposotory
 *
 * @copyright 2019 Webkul Software Pvt Ltd (http://www.webkul.com)
 */
class ProductRepository
{
    /**
     *  AttributeFamily Repository Object
     *
     * @var array
     */
    protected $attributeFamilyRepository;

    /**
     *  Product Repository Object
     *
     * @var array
     */
    protected $product;

    /**
     * Create a new instance.
     *
     * @param  \Webkul\Attribute\Repositories\AttributeFamilyRepository $attributeFamilyRepository
     * @param \Webkul\Product\Repositories\ProductRepository $product
     * @return void
     */
    public function __construct(
        AttributeFamilyRepository $attributeFamilyRepository,
        Product $product
    )
    {
        $this->attributeFamilyRepository = $attributeFamilyRepository;

        $this->product = $product;
    }

    /**
     * Dummy Data For Customer Table.
     *
     * @return mixed
     */
    public function productDummyData($faker)
    {
        $productType = $faker->randomElement(['simple', 'configurable']);

        $fakeData = $this->getProductDummyData($faker, $productType);

        return $fakeData;
    }

    /**
     * Dummy Data For Simple Product
     *
     * @param $faker,$productType
     * @return array $data
     */
    public function getProductDummyData($faker, $productType)
    {
        $productName = $faker->userName;
        $sku = substr(strtolower(str_replace(array('a','e','i','o','u'), '', $productName)), 0, 6);
        $productSku = str_replace(' ', '', $sku) . "-" . rand(100,999999);

        $data =   [
            'sku' => $productSku,
            'type' => $productType,
            'attribute_family_id' => 1
        ];

        return $data;
    }
}