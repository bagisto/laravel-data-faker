<?php

namespace Webkul\DataFaker\Database\Factories\Product;

use DB;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductAttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        $productFaker = \Faker\Factory::create();

        $productFaker->addProvider(new \Bezhanov\Faker\Provider\Commerce($productFaker));

        $productName = $productFaker->productName;

        $sku = substr(strtolower(str_replace(array('a','e','i','o','u'), '', $productName)), 0, 6);

        $productSku = str_replace(' ', '', $sku) . "-" . rand(100,9999999);

        return [
            'sku'                 => $productSku,
            'attribute_family_id' => 1,
            // 'type'                => $this->faker->randomElement(['simple', 'configurable'])
            'type'                => 'configurable'
        ];

    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {

            $attributeValueFacroty = new ProductAttributeValueFactory();
            $productFlat = new ProductFlatFactory();

            if ($product->type == 'simple') {
                $data = $this->getSimpleProductData($product);
                //seed simple product related tables
                $attributeValues = $data['attribute'];
                $productData = $data['data'];

                foreach($attributeValues as $value) {
                    $attributeValueFacroty->state($value)->create();
                }

                $productFlat->state($productData)->create();

                if(session()->get('seed_product_category') == true) {
                    $categories = $this->getCategory();

                    foreach ($categories as $categoryId) {

                        DB::table('product_categories')->insert([
                            'product_id' => $product['id'],
                            'category_id' => $categoryId,
                        ]);
                    }
                }
            } else {

                //seed configurable product related tables
                //create configurable product
                $configData = $this->getSimpleProductData($product);

                $attributeValues = $configData['attribute'];
                $productData = $configData['data'];

                foreach($attributeValues as $value) {
                    $attributeValueFacroty->state($value)->create();
                }

                $configurableFlat = $productFlat->state($productData)->create();

                //create variant
                $data = app('Webkul\DataFaker\Repositories\ProductRepository')->getSuperAttribute($this->faker,$product);

                foreach ($data['superAttribute'] as  $permutation) {

                    $variantProductData = app('Webkul\DataFaker\Repositories\ProductRepository')->createVariant($product, $permutation, $this->faker);

                    $variant = $this->state([
                        'parent_id' => $product['id'],
                        'type' => 'simple',
                        'attribute_family_id' => 1,
                        'sku' => $variantProductData['sku'],
                    ])->create()->first();

                    $variantData = $this->getSimpleProductData($variant);

                    $variantAttributeValues = $variantData['attribute'];
                    $variantProductData = $variantData['data'];

                    foreach($variantAttributeValues as $value) {

                        $attributeValueFacroty->state($value)->create();
                    }

                    $attributeValueFacroty->state([
                        'product_id' => $variant['id'],
                        'attribute_id' => 24,
                        'integer_value' => $permutation[24]
                    ])->create();


                    $variantProductData['parent_id'] = $configurableFlat['id'];

                    $productFlat->state($variantProductData)->create();
                }
            }

        });
    }

    /**
     * Get the data for product
     *
     * @param int $productId
     * @return array
     */
    public function getSimpleProductData($product)
    {
        $fakeData = $this->getProductFlatData($this->faker, $product);

        $attributes = app('Webkul\Attribute\Repositories\AttributeRepository')->get();

        foreach ($attributes as $attribute) {

            if (! isset($fakeData[$attribute->code]) || (in_array($attribute->type, ['date', 'datetime']) && ! $fakeData[$attribute->code]))
                continue;

            if ($attribute->type == 'multiselect' || $attribute->type == 'checkbox') {
                $fakeData[$attribute->code] = implode(",", $fakeData[$attribute->code]);
            }

            if ($attribute->type == 'image' || $attribute->type == 'file') {
                $dir = 'product';
                if (gettype($fakeData[$attribute->code]) == 'object') {
                    $fakeData[$attribute->code] = request()->file($attribute->code)->store($dir);
                } else {
                    $fakeData[$attribute->code] = NULL;
                }
            }

            $attributeValue = [
                // 'product_id' => $product['product_id'],
                'attribute_id' => $attribute->id,
                'value' => $fakeData[$attribute->code],
                'channel' => $attribute->value_per_channel ? $fakeData['channel'] : null,
                'locale' => $attribute->value_per_locale ? $fakeData['locale'] : null,
                'product_id' => $product['id']
            ];

            $attributeValue[ProductAttributeValue::$attributeTypeFields[$attribute->type]] = $attributeValue['value'];

            unset($attributeValue['value']);
            $value[] = $attributeValue;

        }

        // return $value;
        return ['attribute' => $value, 'data' => $fakeData];
    }

    /**
     * Dummy Data For Simple Product
     *
     * @param $faker, $productType
     * @return array
     */
    public function getProductFlatData($faker, $product)
    {
        $price = $faker->numberBetween($min = 0, $max = 500);
        $specialPrice = rand('0', $faker->numberBetween($min = 0, $max = 500));

        $parentId = null;
        $new = 1;
        $feature = 1;
        $visibleIndividually = 1;
        $weight = $faker->randomNumber(2);
        $width  = $faker->randomNumber(2);
        $height = $faker->randomNumber(2);
        $depth  = $faker->randomNumber(2);

        if ($product['type'] == 'configurable') {

            $price = null;
            $min = $faker->numberBetween($min = 100, $max = 200);
            $max = $faker->numberBetween($min = 200, $max = 500);
            $weight = null;
            $width =  null;
            $height = null;
            $depth =  null;

        } elseif($product['parent_id'] != null && $product['type'] == 'simple') {
            $new = 0;
            $feature = 0;
            $visibleIndividually = 0;

            if ($specialPrice == 0) {
                $max = $price;
                $min = $price;
            } else {
                $max = $specialPrice;
                $min = $specialPrice;
            }
        } else {
            if ($specialPrice == 0) {
                $max = $price;
                $min = $price;
            } else {
                $max = $specialPrice;
                $min = $specialPrice;
            }
        }

        $localeCode = core()->getCurrentLocale()->code;
        $channelCode = core()->getCurrentChannel()->code;
        $productFaker = \Faker\Factory::create();

        $productFaker->addProvider(new \Bezhanov\Faker\Provider\Commerce($productFaker));

        $data = [
            'sku' => $product['sku'],
            'name' => $productFaker->productName,
            'url_key' => $faker->unique(true)->word . '-' . rand(1,9999999),
            'new' => $new,
            'featured' => $feature,
            'visible_individually' => $visibleIndividually,
            'min_price' => $min,
            'max_price' => $max,
            'status' => 1,
            'color' => 1,
            'price' => $price,
            'special_price' => $specialPrice,
            'special_price_from' => null,
            'special_price_to' => null,
            'width'  => $width,
            'height' => $height,
            'depth'  => $depth,
            'meta_title' => '',
            'meta_keywords' => '',
            'meta_description' => '',
            'weight' => $weight,
            'color_label' => $faker->colorName,
            'size' => 6,
            'size_label' => 'S',
            'short_description' => '<p>' . $faker->paragraph . '</p>',
            'description' => '<p>' . $faker->paragraph . '</p>',
            'channel' => $channelCode,
            'locale' => $localeCode,
            'product_id' => $product['id'],
            'parent_id' => $parentId
        ];

        return $data;
    }

    public function getCategory()
    {
        $categories = app('Webkul\Category\Repositories\CategoryRepository')->all()->random(2)->pluck('id');

        return $categories;
    }
}