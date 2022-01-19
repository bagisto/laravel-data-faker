<?php

namespace Webkul\DataFaker\Database\Factories\Product;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;
use Webkul\Attribute\Models\Attribute;

class AttributeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attribute::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $types = [
            'text',
            'textarea',
            'price',
            'boolean',
            'select',
            'multiselect',
            'datetime',
            'date',
            'image',
            'file',
            'checkbox',
        ];

        return [
            'admin_name'          => $this->faker->word,
            'code'                => $this->faker->word,
            'type'                => array_rand($types),
            'validation'          => '',
            'position'            => $this->faker->randomDigit,
            'is_required'         => false,
            'is_unique'           => false,
            'value_per_locale'    => false,
            'value_per_channel'   => false,
            'is_filterable'       => false,
            'is_configurable'     => false,
            'is_user_defined'     => true,
            'is_visible_on_front' => true,
            'swatch_type'         => null,
            'use_in_flat'         => true,
        ];

    }
}