<?php

namespace Webkul\Faker\Helpers;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Arr;
use Webkul\Product\Models\Product as ProductModel;
use Webkul\Product\Models\ProductAttributeValue;
use Webkul\Product\Models\ProductInventory;
use Webkul\Product\Models\ProductDownloadableLink;
use Webkul\Category\Models\Category;

class Product
{
    /**
     * Contains product type faker classes.
     *
     * @var array
     */
    protected $types = [
        'simple',
        'virtual',
        'downloadable',
        'configurable',
    ];

    /**
     * Product default attributes
     *
     * @var array
     */
    protected $attributes = [
        1  => 'sku',
        2  => 'name',
        3  => 'url_key',
        7  => 'visible_individually',
        8  => 'status',
        9  => 'short_description',
        10 => 'description',
        11 => 'price',
        13 => 'special_price',
        22 => 'weight',
    ];

    /**
     * Super attributes for configurable products
     *
     * @var array
     */
    protected $superAttributes = [
        23 => 'color',
        24 => 'size',
    ];

    /**
     * Super attribute options combination for configurable variants
     *
     * @var array
     */
    protected $superAttributeOptionCombinations = [
        [1, 6],
        [1, 7],
        [2, 6],
        [2, 7],
    ];

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $channel;

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->locale = app()->getLocale();

        $this->channel = core()->getCurrentChannelCode();
    }

    /**
     * Create a records
     *
     * @param  integer  $count
     * @param  string  $productType
     * @return void
     */
    public function create($count, $productType)
    {
        ProductModel::factory()
            ->count($count)
            ->state(new Sequence(
                fn ($sequence) => [
                    'type' => (
                        $productType == 'all'
                            ? Arr::random($this->types)
                            : $productType
                    ),
                ],
            ))
            ->hasAttached(Category::inRandomOrder()->limit(2)->get())
            ->has(
                ProductAttributeValue::factory()
                    ->count(10)
                    ->state(new Sequence(
                        fn ($sequence) => $this->getAttributeValues($sequence),
                    )),
                'attribute_values'
            )
            ->afterCreating(function ($product) {
                if (in_array($product->type, ['simple', 'virtual'])) {
                    ProductInventory::factory()
                        ->for($product)
                        ->state(function (array $attributes) {
                            return [
                                'inventory_source_id' => 1,
                            ];
                        })
                        ->create();
                } elseif ($product->type == 'downloadable') {
                    ProductDownloadableLink::factory()
                        ->for($product)
                        ->hasTranslations()
                        ->create();
                } elseif ($product->type == 'configurable') {
                    $product->super_attributes()->attach(array_keys($this->superAttributes));

                    ProductModel::factory()
                        ->count(4)
                        ->simple()
                        ->for($product, 'parent')
                        ->has(
                            ProductAttributeValue::factory()
                                ->count(10)
                                ->state(new Sequence(
                                    fn ($sequence) => $this->getAttributeValues($sequence),
                                )),
                            'attribute_values'
                        )
                        ->hasInventories(1, [
                            'inventory_source_id' => 1,
                        ])
                        ->afterCreating(function ($variant) {
                            static $index = 0;

                            $attributeIds = array_keys($this->superAttributes);

                            $currentCombination = $this->superAttributeOptionCombinations[$index++];

                            $variant->attribute_values()->create([
                                'attribute_id'  => $attributeIds[0],
                                'integer_value' => $currentCombination[0],
                            ]);

                            $variant->attribute_values()->create([
                                'attribute_id'  => $attributeIds[1],
                                'integer_value' => $currentCombination[1],
                            ]);

                            $variant->attribute_values()->updateOrCreate([
                                'attribute_id'  => 7,
                            ], [
                                'boolean_value' => 0,
                            ]);

                            Event::dispatch('catalog.product.update.after', $variant);
                        })
                        ->create();
                }

                Event::dispatch('catalog.product.update.after', $product);
            })
            ->create();
    }

    /**
     * Creates attribute values for the product
     *
     * @param  \Illuminate\Database\Eloquent\Factories\Sequence  $sequence
     * @return void
     */
    public function getAttributeValues($sequence)
    {
        static $index = 0;

        if (($sequence->index % 10) == 0) {
            $index = 0;
        }

        $attributeCodes = array_values($this->attributes);

        return array_merge($this->getAttributeValue($attributeCodes[$index]), [
            'attribute_id' => array_search($attributeCodes[$index++], $this->attributes),
        ]);
    }

    /**
     * Creates attribute values for the product
     *
     * @param  string  $code
     * @return mixed
     */
    public function getAttributeValue($code)
    {
        switch ($code) {
            case 'sku':
                return [
                    'text_value' => fake()->uuid(),
                ];

            case 'name':
                return [
                    'text_value' => fake()->words(3, true),
                    'locale'     => $this->locale,
                    'channel'    => $this->channel,
                ];

            case 'url_key':
                return [
                    'text_value' => fake()->slug(),
                ];

            case 'visible_individually':
            case 'status':
                return [
                    'boolean_value' => true,
                ];

            case 'short_description':
                return [
                    'text_value' => fake()->sentence(),
                    'locale'     => $this->locale,
                    'channel'    => $this->channel,
                ];

            case 'description':
                return [
                    'text_value' => fake()->paragraph(),
                    'locale'     => $this->locale,
                    'channel'    => $this->channel,
                ];

            case 'price':
                return [
                    'float_value' => fake()->randomFloat(2, 1, 1000),
                ];

            case 'special_price':
                return [
                    'float_value' => null,
                ];

            case 'weight':
                return [
                    'text_value' => fake()->numberBetween(0, 100),
                ];
        }   
    }
}