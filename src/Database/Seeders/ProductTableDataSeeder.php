<?php

namespace Webkul\DataFaker\Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\DataFaker\Database\Factories\Product\ProductFactory;
use Webkul\DataFaker\Database\Factories\Product\ProductImageFactory;
use Webkul\DataFaker\Database\Factories\Product\ProductInventoryFactory;

class ProductTableDataSeeder extends Seeder
{
    public function run($count)
    {
        $productFactory = new ProductFactory();
        $inventory = new ProductInventoryFactory();
        $image = new ProductImageFactory();

        //seed fake products
        $productFactory
            ->count($count)
            ->configure()
            ->has($inventory->state(function (array $value, $product) {
                if ($product['type'] == 'configurable') {
                    return ['qty' => 0];
                } else {
                    return ['inventory_source_id' => $value['inventory_source_id']];
                }
            }), 'inventories')
            ->has($image->count(2)->state(function (array $value, $product) {

                $imageData = $this->uploadImages($product['id']);
                return $imageData;

            }), 'images')
            ->create();
    }
}