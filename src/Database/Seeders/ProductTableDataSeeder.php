<?php

namespace Webkul\DataFaker\Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\DataFaker\Database\Factories\Product\ProductFactory;

class ProductTableDataSeeder extends Seeder
{
    public function run()
    {
        $productFactory = new ProductFactory();

        //seed fake products
        $productFactory
            ->count(2)
            // ->has($customerAddressFactory->count(2), 'addresses')
            ->create();
    }
}