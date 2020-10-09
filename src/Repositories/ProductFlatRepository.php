<?php

namespace Webkul\DataFaker\Repositories;

use Illuminate\Container\Container as App;
use Webkul\Core\Eloquent\Repository;
use Webkul\DataFaker\Repositories\AttributeValueRepository;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Product\Repositories\ProductRepository as BaseProductRepository;
use Webkul\Product\Repositories\ProductInventoryRepository as ProductInventoryRepository;
use Webkul\DataFaker\Repositories\RelatedProductRepository;
use Webkul\Product\Repositories\ProductImageRepository;
use Webkul\Product\Repositories\ProductFlatRepository as ProductFlat;
use Webkul\Product\Repositories\ProductRepository as Product;
use Webkul\Product\Models\ProductAttributeValue;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Illuminate\Support\Facades\Storage;
use DB;

/**
 * Product Flat Reposotory
 *
 * @copyright 2019 Webkul Software Pvt Ltd (http://www.webkul.com)
 */
class ProductFlatRepository extends Repository
{
    /**
     *  Product Attribute Value Repository Object
     *
     * @var array
     */
    protected $productAttributeValue;

    /**
     *  Base Product Repository Object
     *
     * @var array
     */
    protected  $baseProductRepository;

     /**
     * Product Inventory Repository object
     *
     * @var array
     */
    protected $productInventory;

    /**
     * ProductImageRepository object
     *
     * @var array
     */
    protected $productImage;

    /**
     * ProductAttributeValueRepository object
     *
     * @var array
     */
    protected $attributeValue;

    /**
     * AttributeRepository object
     *
     * @var array
     */
    protected $attribute;

    /**
     * Category Repository object
     *
     * @var array
     */
    protected $categoryRepository;

    /**
     * ProductRepository object
     *
     * @var array
     */
    protected $ProductFlat;

    /**
     *  Product Repository Object
     *
     * @var array
     */
    protected $product;

    /**
     * ProductRepository object
     *
     * @var array
     */
    protected $relatedProduct;

    /**
     * Create a new instance.
     *
     * @param  Webkul\Attribute\Repositories\AttributeRepository             $attribute
     * @param  Webkul\Product\Repositories\ProductInventoryRepository        $productInventory
     * @param  Webkul\Product\Repositories\ProductImageRepository            $productImage
     * @param  Webkul\Attribute\Repositories\ProductAttributeValueRepository $attributeValue
     * @param \Webkul\Product\Repositories\ProductRepository $product
     * @return void
     */
    public function __construct(
        AttributeRepository $attribute,
        AttributeFamilyRepository $attributeFamilyRepository,
        AttributeValueRepository $productAttributeValue,
        ProductFlat $productFlat,
        BaseProductRepository $baseProductRepository,
        ProductInventoryRepository $productInventory,
        ProductImageRepository $productImage,
        CategoryRepository $categoryRepository,
        RelatedProductRepository $relatedProduct,
        Product $product,
        App $app
    )
    {
        $this->attribute = $attribute;

        $this->attributeFamilyRepository = $attributeFamilyRepository;

        $this->productAttributeValue = $productAttributeValue;

        $this->baseProductRepository = $baseProductRepository;

        $this->productInventory = $productInventory;

        $this->productImage = $productImage;

        $this->productFlat = $productFlat;

        $this->product = $product;

        $this->relatedProduct = $relatedProduct;

        $this->categoryRepository = $categoryRepository;

        parent::__construct($app);
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return 'Webkul\Product\Contracts\Product';
    }

    /**
     * Create Product Dummy Data.
     *
     * @param $faker
     * @return mixed
     */
    public function getProductFlatDummyData($faker, $productType)
    {
        switch( $productType ) {
            case 'simple':
                $fakeData = $this->getSimpleProductDummyData($faker ,$productType);
                return $fakeData;
            case 'configurable':
                $fakeData = $this->getConfigurableProductDummyData($faker, $productType);
                return $fakeData;
        }
    }

    /**
     * Dummy Data For Simple Product
     *
     * @param $faker, $productType
     * @return array
     */
    public function getSimpleProductDummyData($faker, $productType)
    {
        $productName = $faker->userName;

        $sku = substr(strtolower(str_replace(array('a','e','i','o','u'), '', $productName)), 0, 6);

        $productSku = str_replace(' ', '', $sku) . "-". str_replace(' ', '', $sku) . "-" . rand(1,9999999) . "-" . rand(1,9999999);

        $price = $faker->numberBetween($min = 0, $max = 500);

        $specialPrice = rand('0', $faker->numberBetween($min = 0, $max = 500));

        if ($specialPrice == 0) {
            $max = $price;
            $min = $price;
        } else {
            $max = $specialPrice;
            $min = $specialPrice;
        }

        $localeCode = core()->getCurrentLocale()->code;

        $channelCode = core()->getCurrentChannel()->code;

        $productFaker = \Faker\Factory::create();

        $productFaker->addProvider(new \Bezhanov\Faker\Provider\Commerce($productFaker));

        $data = [
            'sku' => $productSku,
            'name' => $productFaker->productName,
            'url_key' => $faker->unique(true)->word . '-' . rand(1,9999999),
            'new' => 1,
            'featured' => 1,
            'visible_individually' => 1,
            'min_price' => $min,
            'max_price' => $max,
            'status' => 1,
            'color' => 1,
            'price' => $price,
            'special_price' => 0,
            'special_price_from' => null,
            'special_price_to' => null,
            'width' => $faker->randomNumber(2),
            'height' => $faker->randomNumber(2),
            'depth' => $faker->randomNumber(2),
            'meta_title' => '',
            'meta_keywords' => '',
            'meta_description' => '',
            'weight' => $faker->randomNumber(2),
            'color_label' => $faker->colorName,
            'size' => 6,
            'size_label' => 'S',
            'short_description' => '<p>' . $faker->paragraph . '</p>',
            'description' => '<p>' . $faker->paragraph . '</p>',
            'channel' => $channelCode,
            'locale' => $localeCode,
        ];

        return $data;
    }

    /**
     * Dummy Data For Configurable Product
     *
     * @param $faker, $productType
     * @return array
     */
    public function getConfigurableProductDummyData($faker, $productType)
    {
        $productFaker = \Faker\Factory::create();

        $productFaker->addProvider(new \Bezhanov\Faker\Provider\Commerce($productFaker));

        $productName = $productFaker->productName;

        $sku = substr(strtolower(str_replace(array('a','e','i','o','u'), '', $productName)), 0, 6);

        $productSku = str_replace(' ', '', $sku) . "-" . rand(100,9999999);

        $attributeFamily = $this->attributeFamilyRepository->get()->random();

        $productsTableData = [
            'type' =>'configurable',
            'attribute_family_id' => $attributeFamily->id,
            'sku' => $productSku,
        ];

        $this->createConfigurableProduct($productsTableData, $faker, $productFaker);
    }

    /**
     * Create Configurable Product
     *
     * @param $faker, $data, $productFaker
     * @return void
     */
    public function createConfigurableProduct($product, $faker, $productFaker)
    {
        $parentData = $this->createProduct($product,$faker,$productFaker);
        $data = [
            'super_attributes' =>[
                'size' => [
                    0 => 6,
                    1 => 7,
                    // 2 => 8,
                    // 3 => 9
                ]
            ],
            'family' => 1
        ];

        $nameAttribute = $this->attribute->findOneByField('code', 'status');

        $addttribute = $this->productAttributeValue->createValue([
                'product_id' => $product->id,
                'attribute_id' => $nameAttribute->id,
                'value' => 1
            ]);

        if (isset($data['super_attributes'])) {

            $super_attributes = [];

            foreach ($data['super_attributes'] as $attributeCode => $attributeOptions) {
                $attribute = $this->attribute->findOneByField('code', $attributeCode);

                $super_attributes[$attribute->id] = $attributeOptions;

                $product->super_attributes()->attach($attribute->id);
            }

            foreach (array_permutation($super_attributes) as $permutation) {
                $variantProduct = $this->createVariant($product, $permutation, $faker);

                if (isset($variantProduct)) {
                    //insert data into product flat
                   $this->createVariantProduct($variantProduct->getOriginal(), $faker, $productFaker, $permutation['24']);
                }
            }
        }
    }

    /**
     * Create Product Variant
     *
     * @param mixed $product
     * @param array $permutation
     * @param array $data
     * @return mixed
     */
    public function createVariant($product, $permutation, $faker, $data = [])
    {
        if (! count($data)) {
            $data = [
                "sku" => $product->sku . '-variant-' . implode('-', $permutation),
                "name" => "",
                "inventories" => [],
                "price" => 0,
                "weight" => 0,
                "status" => 1
            ];
        }

        $variant = $this->model->create([
            'parent_id' => $product->id,
            'type' => 'simple',
            'attribute_family_id' => $product->attribute_family_id,
            'sku' => $data['sku'],
        ]);

        foreach (['sku', 'name', 'price', 'weight', 'status'] as $attributeCode) {
            $attribute = $this->attribute->findOneByField('code', $attributeCode);

            if ($attribute->value_per_channel) {
                if ($attribute->value_per_locale) {
                    foreach (core()->getAllChannels() as $channel) {
                        foreach (core()->getAllLocales() as $locale) {
                            $datas = $this->productAttributeValue->createValue([
                                    'product_id' => $variant->id,
                                    'attribute_id' => $attribute->id,
                                    'channel' => $channel->code,
                                    'locale' => $locale->code,
                                    'value' => $data[$attributeCode]
                                ]);
                        }
                    }
                } else {
                    foreach (core()->getAllChannels() as $channel) {
                        $datas = $this->productAttributeValue->createValue([
                                'product_id' => $variant->id,
                                'attribute_id' => $attribute->id,
                                'channel' => $channel->code,
                                'value' => $data[$attributeCode]
                            ]);
                    }
                }
            } else {
                if ($attribute->value_per_locale) {
                    foreach (core()->getAllLocales() as $locale) {
                        $datas =  $this->productAttributeValue->createValue([
                                'product_id' => $variant->id,
                                'attribute_id' => $attribute->id,
                                'locale' => $locale->code,
                                'value' => $data[$attributeCode]
                            ]);
                    }
                } else {
                    $datas =  $this->productAttributeValue->createValue([
                            'product_id' => $variant->id,
                            'attribute_id' => $attribute->id,
                            'value' => $data[$attributeCode]
                        ]);
                }
            }
        }

        foreach ($permutation as $attributeId => $optionId) {
            $this->productAttributeValue->createValue([
                'product_id' => $variant->id,
                'attribute_id' => $attributeId,
                'value' => $optionId
            ]);
        }

        $inventory = [
            'inventories' => [
                1 => $faker->randomNumber(2)
            ]
        ];

        $this->productInventory->saveInventories($inventory, $variant);

        return $variant;
    }

    /**
     * Create Product
     *
     * @param mixed $product
     * @param array $productFaker
     * @param array $faker
     * @return mixed
     */
    public function createProduct($product, $faker, $productFaker)
    {
        $localeCode = core()->getCurrentLocale()->code;
        $channelCode = core()->getCurrentChannel()->code;

        if ($product['type'] == 'configurable') {
            $price = null;
            $parentId = null;
            $urlKey = $faker->unique(true)->word . '-' . rand(1,9999999);
            $new = 1;
            $feature = 1;
            $description = $faker->paragraph;
            $description = $faker->paragraph;
            $visibleIndividually = 1;
        }

        $data = [
            'product_id' => $product['id'],
            'sku' => $faker->word,
            'name' => $productFaker->productName,
            'url_key' => $urlKey,
            'new' => $new,
            'featured' => $feature,
            'visible_individually' => $visibleIndividually,
            'min_price' => $faker->numberBetween($min = 100, $max = 200),
            'max_price' => $faker->numberBetween($min = 200, $max = 500),
            'parent_id' => $parentId,
            'status' => 1,
            'color' => 1,
            'price' => $price,
            'width' => null,
            'height' =>null,
            'depth' => null,
            'meta_title' => '',
            'meta_keywords' => '',
            'meta_description' => '',
            'weight' => null,
            'color_label' => 'Red',
            'size' => null,
            'size_label' => null,
            'short_description' => '<p>' . $description . '</p>',
            'description' => '<p>' . $description . '</p>',
            'channel' => $channelCode,
            'locale' => $localeCode,
            'special_price' => null,
            'special_price_from' => null,
            'special_price_to' => null,
        ];

        $this->productAttributeValue->createAttributeValue($data);

        $this->productFlat->create($data);
    }

    /**
     * Create Variant Product
     *
     * @param mixed $product
     * @param array $productFaker
     * @param array $faker
     * @return mixed
     */
    public function createVariantProduct($variantProduct, $faker, $productFaker, $size)
    {
        $localeCode = core()->getCurrentLocale()->code;

        $channelCode = core()->getCurrentChannel()->code;

        if ($variantProduct['type'] == 'simple') {
            $price = $faker->numberBetween($min = 0, $max = 500);
            $parentId = $variantProduct['id'];
        }

        switch($size) {
            case '6': $sizeLabel = 'S';
                    break;
            case '7': $sizeLabel = 'M';
                    break;
            case '8': $sizeLabel = 'L';
                    break;
            case '9': $sizeLabel = 'XL';
        }

        $data = [
            'product_id' => $variantProduct['id'],
            'sku' => $variantProduct['sku'],
            'name' => $productFaker->productName,
            'url_key' => null,
            'new' => null,
            'featured' => null,
            'visible_individually' => null,
            'min_price' => $price,
            'max_price' => $price,
            'parent_id' => $parentId,
            'status' => 1,
            'color' => null,
            'price' => $price,
            'width' => $faker->randomNumber(2),
            'height' => $faker->randomNumber(2),
            'depth' => $faker->randomNumber(2),
            'meta_title' => '',
            'meta_keywords' => '',
            'meta_description' => '',
            'weight' => $faker->randomNumber(2),
            'color_label' => null,
            'size' => $size,
            'size_label' => $sizeLabel,
            'short_description' => null,
            'description' => null,
            'channel' => $channelCode,
            'locale' => $localeCode,
            'special_price' => null,
            'special_price_from' => null,
            'special_price_to' => null,
        ];

        $parentId = $this->productFlat->findOneWhere(['product_id' => $variantProduct['parent_id']])->id;

        $data['parent_id'] = $parentId;

        $this->productAttributeValue->createAttributeValue($data);

        $this->productFlat->create($data);
    }

    /**
     * Upload Product Images
     *
     * @param mixed $product
     * @param array $productFaker
     * @return mixed
     */
    public function uploadImages($faker, $product)
    {

        $filepath = storage_path('app/public/product/');

        Storage::makeDirectory('/product/'. $product['product_id']);

        $path = $faker->image($filepath. $product['product_id'], 800, 800, 'food', true, true);


        $pos = strpos($path, 'product');

        $imagePath = substr($path, $pos);

        $data = [
            'path' => $imagePath,
            'product_id' => $product['product_id']
        ];

        return $data;
    }

    /**
     * Create Product Categories
     *
     * @param mixed $product
     * @param array $productFaker
     * @return mixed
     */
    public function createProductCategories($product, $faker)
    {
        $categories = $this->categoryRepository->all()->random(3);

        $filterableAttribute = ['11', '23', '24', '25'];

        foreach ($categories as $category) {
            if (! empty($category->translations) && count($category->translations) > 0) {
                foreach ($category->translations as $translation) {

                    DB::table('product_categories')->insert([
                        'product_id' => $product['product_id'],
                        'category_id' => $translation->category_id,
                    ]);

                    foreach ($filterableAttribute as $categoryFilterableAttribute) {

                        $categoryExist = DB::table('category_filterable_attributes')->where('category_id',$translation->category_id)->count();

                        if ($categoryExist < 4) {
                            DB::table('category_filterable_attributes')->insert([
                                'attribute_id' => $categoryFilterableAttribute,
                                'category_id' => $translation->category_id,
                            ]);
                        }
                    }
                }
            }
        }
    }
}