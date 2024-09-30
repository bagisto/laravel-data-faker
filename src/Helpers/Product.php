<?php

namespace Webkul\Faker\Helpers;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Webkul\Category\Models\Category;
use Webkul\Product\Models\Product as ProductModel;
use Webkul\Product\Models\ProductAttributeValue;
use Webkul\Product\Models\ProductBundleOption;
use Webkul\Product\Models\ProductBundleOptionProduct;
use Webkul\Product\Models\ProductDownloadableLink;
use Webkul\Product\Models\ProductGroupedProduct;
use Webkul\Product\Models\ProductInventory;

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
     * Product default attributes.
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
     * Super attributes for configurable products.
     *
     * @var array
     */
    protected $superAttributes = [
        23 => 'color',
        24 => 'size',
    ];

    /**
     * Super attribute options combination for configurable variants.
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
     * Locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * Channel.
     *
     * @var string
     */
    protected $channel;

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(protected array $options = [])
    {
        $this->locale = app()->getLocale();

        $this->channel = core()->getCurrentChannelCode();

        if (isset($this->options['attributes'])) {
            /**
             * Please avoid using array_merge() here; we need the same key to come from the options.
             */
            $this->attributes = $this->attributes + $this->options['attributes'];
        }
    }

    /**
     * Create a products.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function create(int $count, string $productType)
    {
        return $this->factory()
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
                        ->simple()
                        ->for($product, 'parent')
                        ->has(
                            ProductAttributeValue::factory()
                                ->count(count($this->attributes))
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
                        ->count(4)
                        ->create();
                }

                Event::dispatch('catalog.product.update.after', $product);
            })
            ->count($count)
            ->create();
    }

    /**
     * Get a product factory. This will provide a factory instance for
     * attaching additional features and taking advantage of the factory.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    public function factory()
    {
        return ProductModel::factory()
            ->has(
                ProductAttributeValue::factory()
                    ->count(count($this->attributes))
                    ->state(new Sequence(
                        fn ($sequence) => $this->getAttributeValues($sequence),
                    )),
                'attribute_values'
            );
    }

    /**
     * Get a simple product factory. This will provide a factory instance for
     * attaching additional features and taking advantage of the factory.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    public function getSimpleProductFactory()
    {
        return $this->factory()
            ->simple()
            ->afterCreating(function ($product) {
                ProductInventory::factory()
                    ->for($product)
                    ->state(function (array $attributes) {
                        return [
                            'inventory_source_id' => 1,
                        ];
                    })
                    ->create();

                Event::dispatch('catalog.product.update.after', $product);
            });
    }

    /**
     * Get a virtual product factory. This will provide a factory instance for
     * attaching additional features and taking advantage of the factory.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    public function getVirtualProductFactory()
    {
        return $this->factory()
            ->virtual()
            ->afterCreating(function ($product) {
                ProductInventory::factory()
                    ->for($product)
                    ->state(function (array $attributes) {
                        return [
                            'inventory_source_id' => 1,
                        ];
                    })
                    ->create();

                Event::dispatch('catalog.product.update.after', $product);
            });
    }

    /**
     * Get a grouped product factory. This will provide a factory instance for
     * attaching additional features and taking advantage of the factory.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    public function getGroupedProductFactory()
    {
        return $this->factory()
            ->grouped()
            ->afterCreating(function ($product) {
                $simpleProducts = $this->getSimpleProductFactory()->count(4)->create();

                foreach ($simpleProducts as $key => $simpleProduct) {
                    ProductGroupedProduct::factory()->create([
                        'product_id'            => $product->id,
                        'associated_product_id' => $simpleProduct->id,
                        'sort_order'            => $key, 
                    ]);
                }

                Event::dispatch('catalog.product.update.after', $product);
            });
    }

    /**
     * Get a downloadable product factory. This will provide a factory instance for
     * attaching additional features and taking advantage of the factory.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    public function getDownloadableProductFactory()
    {
        return $this->factory()
            ->downloadable()
            ->afterCreating(function ($product) {
                ProductDownloadableLink::factory()
                    ->for($product)
                    ->hasTranslations()
                    ->create();

                Event::dispatch('catalog.product.update.after', $product);
            });
    }

    /**
     * Get a bundle product factory. This will provide a factory instance for
     * attaching additional features and taking advantage of the factory.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    public function getBundleProductFactory()
    {
        return $this->factory()
            ->bundle()
            ->afterCreating(function ($product) {
                $simpleProducts = $this->getSimpleProductFactory()->count(4)->create();

                foreach ($simpleProducts as $simpleProduct) {
                    ProductBundleOptionProduct::factory()->create([
                        'product_id'               => $simpleProduct->id,
                        'product_bundle_option_id' => ProductBundleOption::factory()->create([
                            'product_id' => $product->id,
                            'label'      => fake()->word(),
                        ])->id,
                    ]);
                }

                Event::dispatch('catalog.product.update.after', $product);
            });
    }

    /**
     * Get a configurable product factory. This will provide a factory instance for
     * attaching additional features and taking advantage of the factory.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    public function getConfigurableProductFactory()
    {
        return $this->factory()
            ->configurable()
            ->afterCreating(function ($product) {
                $product->super_attributes()->attach(array_keys($this->superAttributes));

                ProductModel::factory()
                    ->simple()
                    ->for($product, 'parent')
                    ->has(
                        ProductAttributeValue::factory()
                            ->count(count($this->attributes))
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
                    ->count(4)
                    ->create();

                Event::dispatch('catalog.product.update.after', $product);
            });
    }

    /**
     * Creates attribute values for the product.
     *
     * @param  \Illuminate\Database\Eloquent\Factories\Sequence  $sequence
     * @return mixed
     */
    public function getAttributeValues($sequence)
    {
        static $index = 0;

        if (($sequence->index % count($this->attributes)) == 0) {
            $index = 0;
        }

        $attributeCodes = array_values($this->attributes);

        return array_merge($this->getAttributeValue($attributeCodes[$index]), [
            'attribute_id' => array_search($attributeCodes[$index++], $this->attributes),
        ]);
    }

    /**
     * Creates attribute values for the product.
     *
     * @return mixed
     */
    public function getAttributeValue(string $code)
    {
        switch ($code) {
            case isset($this->options['attribute_value'][$code]):
                /**
                 * This will give high priority of given attribute options.
                 * Which is allows the addition of values to new attributes if the attributes key is present in the options property.
                 */
                return $this->options['attribute_value'][$code];
                
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

            default:
                return;
        }
    }
}
