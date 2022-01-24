<?php

namespace Webkul\DataFaker\Database\Factories\Product;

use Webkul\Product\Models\ProductInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductInventoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductInventory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'qty'                 => $this->faker->numberBetween(100, 200),
            'inventory_source_id' => 1,
        ];

    }
}