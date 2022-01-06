<?php

namespace Webkul\DataFaker\Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\Attribute\Repositories\AttributeRepository;
use DB;
use Faker\Generator as Faker;

class ProductTableDataSeeder extends Seeder
{
    /**
     *  Product Repository Object
     *
     * @var array
     */
    protected $product;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function __construct(AttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function run()
    {
        $count = 2;
        DB::table('products')->delete();
        DB::table('product_flat')->delete();
        DB::table('product_inventories')->delete();
        DB::table('product_images')->delete();
        DB::table('product_attribute_values')->delete();


        factory(\Webkul\Product\Models\Product::class, $count)->create()->each(function ($product) {
            $faker = \Faker\Factory::create();

            $productType = $faker->randomElement(['simple', 'configurable']);

            $product->update(['type' => $productType]);

            if ($product->type == 'simple') {
                factory(\Webkul\Product\Models\ProductFlat::class)->create(['product_id' => $product]);

                factory(\Webkul\Product\Models\ProductInventory::class)->create(['product_id' => $product->id, 'inventory_source_id' => 1]);
            }

            if ($product->type == 'configurable') {

                $productFaker = \Faker\Factory::create();
                $faker = \Faker\Factory::create();
                $productFaker->addProvider(new \Bezhanov\Faker\Provider\Commerce($productFaker));

                $fakeImage = app('Webkul\DataFaker\Repositories\ProductFlatRepository')->uploadImages($faker, $product);

                factory(\Webkul\Product\Models\ProductImage::class, 5)->create($fakeImage);

                $fakeData = app('Webkul\DataFaker\Repositories\ProductFlatRepository')->createConfigurableProduct($product, $faker, $productFaker);
            }
        });
    }
}
