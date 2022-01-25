<?php

namespace Webkul\DataFaker\Database\Factories\Product;

use DB;
use Carbon\Carbon;
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

        $type = session()->get('seed_config_product') ? $this->faker->randomElement(['simple', 'configurable']) : 'simple';

        return [
            'sku'                 => $productSku,
            'attribute_family_id' => 1,
            'type'                => $type
        ];

    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {

            $attributeValueFacroty = new ProductAttributeValueFactory();
            $productFlat = new ProductFlatFactory();
            $inventory = new ProductInventoryFactory();
            $productFactory = new ProductFactory();

            if ($product->type == 'simple') {
                $data = $this->getSimpleProductData($product, 6);
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

                if(session()->get('seed_product_category') == true) {
                    $categories = $this->getCategory();

                    foreach ($categories as $categoryId) {

                        DB::table('product_categories')->insert([
                            'product_id' => $product['id'],
                            'category_id' => $categoryId,
                        ]);
                    }
                }

                //seed configurable product related tables
                //create configurable product
                $configData = $this->getSimpleProductData($product, null);

                $attributeValues = $configData['attribute'];
                $productData = $configData['data'];

                foreach($attributeValues as $value) {
                    $attributeValueFacroty->state($value)->create();
                }

                $configurableFlat = $productFlat->state($productData)->create();

                //create variant
                $data = app('Webkul\DataFaker\Repositories\ProductRepository')->getSuperAttribute($product);

                foreach ($data['superAttribute'] as  $permutation) {

                    $variant = $productFactory->state([
                        'parent_id' => $product['id'],
                        'type' => 'simple',
                        'attribute_family_id' => 1,
                        'sku' => $product['sku'] . '-variant-' . implode('-', $permutation),
                    ])
                    ->has($inventory, 'inventories')
                    ->create();


                    $variantData = $this->getSimpleProductData($variant, $permutation['24']);

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
    public function getSimpleProductData($product, $size)
    {
        $fakeData = $this->getProductFlatData($this->faker, $product, $size);
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

        return ['attribute' => $value, 'data' => $fakeData];
    }

    /**
     * Dummy Data For Simple Product
     *
     * @param $faker, $productType, $size
     * @return array
     */
    public function getProductFlatData($faker, $product, $size)
    {
        $price = $faker->numberBetween($min = 0, $max = 500);
        $specialPrice = rand('0', $faker->numberBetween($min = 0, $max = 500));

        if($size != null) {
            switch($size) {
                case '6': $sizeLabel = 'S';
                        break;
                case '7': $sizeLabel = 'M';
                        break;
                case '8': $sizeLabel = 'L';
                        break;
                case '9': $sizeLabel = 'XL';
            }
        } else {
            $size = 6;
            $sizeLabel = 'S';
        }

        $parentId = null;
        $new = 1;
        $feature = 1;
        $visibleIndividually = 1;
        $weight = $faker->randomNumber(2);
        $width  = $faker->randomNumber(2);
        $height = $faker->randomNumber(2);
        $depth  = $faker->randomNumber(2);
        $short_description = '<p>' . $faker->paragraph . '</p>';
        $description = '<p>' . $faker->paragraph . '</p>';
        $urlKey = $faker->unique(true)->word . '-' . rand(1,9999999);

        if ($product['type'] == 'configurable') {

            $price = null;
            $min = $faker->numberBetween($min = 100, $max = 200);
            $max = $faker->numberBetween($min = 200, $max = 500);
            $weight = null;
            $width =  null;
            $height = null;
            $depth =  null;
            $specialPrice = null;
            $max = 0;
            $min = 0;
            $size = null;
            $sizeLabel = null;

        } elseif($product['parent_id'] != null && $product['type'] == 'simple') {
            $new = 0;
            $feature = 0;
            $visibleIndividually = 0;
            $specialPrice = null;

            $max = $price;
            $min = $price;
            $short_description = null;
            $description = null;
            $width =  null;
            $height = null;
            $depth =  null;
            $urlKey = null;

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
            'url_key' => $urlKey,
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
            'size' => $size,
            'size_label' => $sizeLabel,
            'short_description' => $short_description,
            'description' => $description,
            'channel' => $channelCode,
            'locale' => $localeCode,
            'product_id' => $product['id'],
            'parent_id' => $parentId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        return $data;
    }

    /**
     * Get random categories
     *
     * @return $categories
     */
    public function getCategory()
    {
        $categories = app('Webkul\Category\Repositories\CategoryRepository')->all()->random(2)->pluck('id');

        return $categories;
    }
}