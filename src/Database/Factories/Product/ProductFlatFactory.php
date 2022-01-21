<?php

namespace Webkul\DataFaker\Database\Factories\Product;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductFlat;

class ProductFlatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductFlat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sku'                 => $this->faker->uuid,
        ];

    }
}