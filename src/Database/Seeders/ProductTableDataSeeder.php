<?php

namespace Webkul\DataFaker\Database\Seeders;

use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Webkul\DataFaker\Database\Factories\Product\ProductFactory;
use Webkul\DataFaker\Database\Factories\Product\ProductImageFactory;
use Webkul\DataFaker\Database\Factories\Product\ProductInventoryFactory;

class ProductTableDataSeeder extends Seeder
{
    protected $faker;

    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    public function run()
    {
        $productFactory = new ProductFactory();
        $inventory = new ProductInventoryFactory();
        $image = new ProductImageFactory();

        //seed fake products
        $productFactory
            ->count(1)
            ->configure()
            // ->has($inventory, 'inventories')
            // ->has($image->count(2)->state(function (array $value, $product) {

            //     $imageData = $this->uploadImages($product['id']);
            //     return $imageData;

            // }), 'images')
            ->create();

            session()->forget('seed_product_category');
    }
}