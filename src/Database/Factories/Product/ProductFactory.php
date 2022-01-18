<?php

namespace Webkul\DataFaker\Database\Factories\Product;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;
use Webkul\Product\Models\Product;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sku'                 => $this->faker->uuid,
            'attribute_family_id' => 1,
            'type'                => 'simple'
        ];

    }

    // $factory->define(Product::class, function (Faker $faker) {
    //     return [
    //         'sku'                 => $faker->uuid,
    //         'attribute_family_id' => 1,
    //     ];
    // });

    // $factory->state(Product::class, 'simple', [
    //     'type' => 'simple',
    // ]);

    // $factory->state(Product::class, 'virtual', [
    //     'type' => 'virtual',
    // ]);

    // $factory->state(Product::class, 'downloadable', [
    //     'type' => 'downloadable',
    // ]);

    // $factory->state(Product::class, 'booking', [
    //     'type' => 'booking',
    // ]);
}