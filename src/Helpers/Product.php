<?php

namespace Webkul\Faker\Helpers;

use Illuminate\Support\Arr;
use Webkul\Category\Models\Category;
use Illuminate\Support\Facades\Event;
use Webkul\Attribute\Models\Attribute;
use Webkul\Product\Models\ProductInventory;
use Webkul\Product\Models\ProductBundleOption;
use Webkul\Product\Models\ProductAttributeValue;
use Webkul\Product\Models\ProductGroupedProduct;
use Webkul\Product\Models\Product as ProductModel;
use Webkul\Product\Models\ProductDownloadableLink;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Webkul\Product\Models\ProductBundleOptionProduct;

class Product
{
    /**
     * Contains product type faker classes.
     */
    protected array $types = [
        'simple',
        'virtual',
        'downloadable',
        'configurable',
    ];

    /**
     * Product default attributes.
     */
    protected array $attributes = [
        'sku',
        'name',
        'url_key',
        'new',
        'featured',
        'visible_individually',
        'status',
        'short_description',
        'description',
        'price',
        'cost',
        'special_price',
        'special_price_from',
        'special_price_to',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'length',
        'width',
        'height',
        'weight',
        'guest_checkout',
        'product_number',
        'manage_stock',
    ];

    /**
     * Super attributes for configurable products.
     */
    protected array $superAttributes = [
        'color',
        'size',
    ];

    /**
     * Super attribute options combination for configurable variants.
     */
    protected array $superAttributeOptionCombinations = [];

    /**
     * Locale.
     */
    protected string $locale;

    /**
     * Channel.
     */
    protected object $channel;

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(protected array $options = [])
    {
        $this->locale = app()->getLocale();

        $this->channel = core()->getCurrentChannel();

        if (isset($this->options['attributes'])) {
            $this->attributes = $this->attributes + $this->options['attributes'];
        }

        $this->loadAttributeIds();
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
                $product->channels()->sync([$this->channel->id]);

                $product->load('attribute_values.attribute');

                foreach ($product->attribute_values as $attributeValue) {
                    $attribute = $attributeValue->attribute;

                    if ($attribute->code === 'sku') {
                        $attributeValue->text_value = $product->sku;
                    }

                    $attributeValue->unique_id = implode('|', array_filter([
                        $attribute->value_per_channel ? $attributeValue->channel : null,
                        $attribute->value_per_locale ? $attributeValue->locale : null,
                        $product->id,
                        $attributeValue->attribute_id,
                    ]));

                    $attributeValue->save();
                }

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
                            $variant->channels()->sync([$this->channel->id]);

                            $variant->load('attribute_values.attribute');

                            foreach ($variant->attribute_values as $attributeValue) {
                                $attribute = $attributeValue->attribute;

                                if ($attribute->code === 'sku') {
                                    $attributeValue->text_value = $variant->sku;
                                }

                                $attributeValue->unique_id = implode('|', array_filter([
                                    $attribute->value_per_channel ? $attributeValue->channel : null,
                                    $attribute->value_per_locale ? $attributeValue->locale : null,
                                    $variant->id,
                                    $attributeValue->attribute_id,
                                ]));

                                $attributeValue->save();
                            }

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
                $product->channels()->sync(core()->getCurrentChannel()->id);

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
                        $variant->channels()->sync([$this->channel->id]);

                        $variant->load('attribute_values.attribute');

                        foreach ($variant->attribute_values as $attributeValue) {
                            $attribute = $attributeValue->attribute;

                            if ($attribute->code === 'sku') {
                                $attributeValue->text_value = $variant->sku;
                            }

                            $attributeValue->unique_id = implode('|', array_filter([
                                $attribute->value_per_channel ? $attributeValue->channel : null,
                                $attribute->value_per_locale ? $attributeValue->locale : null,
                                $variant->id,
                                $attributeValue->attribute_id,
                            ]));

                            $attributeValue->save();
                        }
                        
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
     */
    public function getAttributeValues(Sequence $sequence): mixed
    {
        static $index = 0;

        if (($sequence->index % count($this->attributes)) == 0) {
            $index = 0;
        }

        $attributeCodes = array_values($this->attributes);

        $result = array_merge($this->getAttributeValue($attributeCodes[$index]), [
            'attribute_id' => array_search($attributeCodes[$index++], $this->attributes),
        ]);

        return $result;
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
                ];

            case 'url_key':
                return [
                    'text_value' => fake()->slug(),
                    'locale'     => $this->locale,
                ];

            case 'guest_checkout':
            case 'new':
            case 'featured':
            case 'visible_individually':
                return [
                    'boolean_value' => true,
                ];

            case 'manage_stock':
            case 'status':
                return [
                    'boolean_value' => true,
                    'channel'       => $this->channel->code,
                ];

            case 'meta_title':
            case 'meta_keywords':
            case 'meta_description':
            case 'short_description':
                return [
                    'text_value' => fake()->sentence(),
                    'locale'     => $this->locale,
                ];

            case 'description':
                return [
                    'text_value' => fake()->paragraph(),
                    'locale'     => $this->locale,
                ];

            case 'price':
                return [
                    'float_value' => fake()->randomFloat(2, 1, 1000),
                ];

            /**
             * At this stage, this will be null. If you want you can pass the value in the options property.
             *
             * To Do: Need to fix the core pricing test issue first then address this issue.
             */
            case 'cost':
            case 'special_price':
                return [
                    'float_value' => null,
                ];

            case 'special_price_from':
            case 'special_price_to':
                return [
                    'date_value'  => null,
                    'channel'     => $this->channel->code,
                ];

            case 'weight':
            case 'height':
            case 'width':
            case 'length':
                return [
                    'text_value' => fake()->numberBetween(0, 100),
                ];

            case 'product_number':
                return [
                    'text_value' => fake()->numerify('bagisto-#########'),
                ];

            default:
                return;
        }
    }

    /**
     * Load attribute IDs based on the attribute codes.
     *
     * @return void
     */
    protected function loadAttributeIds(): void
    {
        $attributeCodes = array_merge($this->attributes, $this->superAttributes);
        
        $attributes = Attribute::with(['options'])->whereIn('code', $attributeCodes)->get(['id', 'code']);

        $optionSets = $attributes->whereIn('code', $this->superAttributes)
            ->map(function ($attribute) {
                return $attribute->options->take(2)->pluck('id')->toArray();
            })
            ->values()
            ->toArray();
        
        $this->attributes = $attributes->whereIn('code', $this->attributes)->pluck('code', 'id')->toArray();

        $this->superAttributes = $attributes->whereIn('code', $this->superAttributes)->pluck('code', 'id')->toArray();

        $this->superAttributeOptionCombinations = collect($optionSets[0])
            ->crossJoin($optionSets[1])
            ->toArray();
    }
}
