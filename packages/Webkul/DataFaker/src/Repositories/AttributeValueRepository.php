<?php

namespace Webkul\DataFaker\Repositories;

use Illuminate\Container\Container as App;
use Webkul\Core\Eloquent\Repository;
use Webkul\Product\Models\ProductAttributeValue;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Product\Repositories\ProductRepository as BaseProductRepository;
use Webkul\Product\Repositories\ProductAttributeValueRepository as AttributeValue;
use Webkul\Product\Repositories\ProductInventoryRepository as ProductInventoryRepository;

/**
 * ProductAttributeValue Reposotory
 *
 * @copyright 2019 Webkul Software Pvt Ltd (http://www.webkul.com)
 */
class AttributeValueRepository extends Repository
{
    /**
     *  Product Attribute Repository Object
     *
     * @var array
     */
    protected $attribute;

    /**
     * ProductAttributeValueRepository object
     *
     * @var array
     */
    protected $attributeValue;

    /**
     * Product Inventory Repository object
     *
     * @var array
     */
    protected $productInventory;

    /**
     *  Base Product Repository Object
     *
     * @var array
     */
    protected $baseProductRepository;

    /**
     * Create a new instance.
     *
     * @param  Webkul\Attribute\Repositories\ProductAttributeValueRepository $attributeValue
     * @param  Webkul\Product\Repositories\ProductInventoryRepository        $productInventory
     * @param  Webkul\Attribute\Repositories\AttributeRepository             $attribute
     * @param  Webkul\Product\Repositories\ProductRepository                 $baseProductRepository
     * @return void
     */
    public function __construct(
        AttributeRepository $attribute,
        BaseProductRepository $baseProductRepository,
        AttributeValue $attributeValue,
        ProductInventoryRepository $productInventory,
        App $app
    )
    {
        $this->attribute = $attribute;

        $this->baseProductRepository = $baseProductRepository;

        $this->attributeValue = $attributeValue;

        $this->productInventory = $productInventory;

        parent::__construct($app);
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return 'Webkul\Product\Contracts\ProductAttributeValue';
    }

    /**
     * Dummy Data For Customer Table.
     *
     * @return mixed
     */
    public function createAttributeValue($data)
    {
        $localeCode = core()->getCurrentLocale()->code;

        $channelCode = core()->getCurrentChannel()->code;

        $product = $this->baseProductRepository->find($data['product_id']);

        $attributeValues = [
            'channel' => $channelCode,
            'locale' => $localeCode,
            'sku' => $data['sku'],
            'name' => $data['name'],
            'url_key' => $data['url_key'],
            'new' => $data['new'],
            'featured' => $data['featured'],
            'visible_individually' => $data['visible_individually'],
            'status' => $data['status'],
            'color' => $data['color'],
            'size' => $data['size'],
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'meta_title' => $data['meta_title'],
            'meta_keywords' => $data['meta_keywords'],
            'meta_description' => $data['meta_description'],
            'price' => $data['price'],
            'cost' => '',
            'special_price' => $data['special_price'],
            'special_price_from' => $data['special_price_from'],
            'special_price_to' => $data['special_price_to'],
            'width' => $data['width'],
            'height' => $data['height'],
            'depth' => $data['depth'],
            'weight' => $data['weight'],
            'channels' => [
                0 => 1
            ],
        ];

        $attributes = $product->attribute_family->custom_attributes;

        foreach ($attributes as $attribute) {
            if (! isset($attributeValues[$attribute->code]) || (in_array($attribute->type, ['date', 'datetime']) && ! $attributeValues[$attribute->code]))
                continue;

            if ($attribute->type == 'multiselect' || $attribute->type == 'checkbox') {
                $attributeValues[$attribute->code] = implode(",", $attributeValues[$attribute->code]);
            }

            if ($attribute->type == 'image' || $attribute->type == 'file') {
                $dir = 'product';
                if (gettype($attributeValues[$attribute->code]) == 'object') {
                    $attributeValues[$attribute->code] = request()->file($attribute->code)->store($dir);
                } else {
                    $attributeValues[$attribute->code] = NULL;
                }
            }

            $attributeValue = $this->attributeValue->findOneWhere([
                    'product_id' => $product->id,
                    'attribute_id' => $attribute->id,
                    'channel' => $attribute->value_per_channel ? $channelCode : null,
                    'locale' => $attribute->value_per_locale ? $localeCode : null
                ]);

            if (! $attributeValue) {
               $attributeValue = $this->createValue([
                    'product_id' => $product->id,
                    'attribute_id' => $attribute->id,
                    'value' => $attributeValues[$attribute->code],
                    'channel' => $attribute->value_per_channel ? $channelCode : null,
                    'locale' => $attribute->value_per_locale ? $localeCode : null
                ]);
            }

            $this->attributeValue->update([
                ProductAttributeValue::$attributeTypeFields[$attribute->type] => $attributeValues[$attribute->code]
                ], $attributeValue->id
            );
        }
    }

    /**
     * Create The Attribute Value
     *
     * @param array $data
     * @return mixed
     */
    public function createValue(array $data)
    {
        $data['value'] = 1;

        if (isset($data['attribute_id'])) {

            $attribute = $this->attribute->find($data['attribute_id']);
        } else {
            $attribute = $this->attribute->findOneByField('code', $data['attribute_code']);
        }

        if (! $attribute)
            return;

        $data[ProductAttributeValue::$attributeTypeFields[$attribute->type]] = $data['value'];

        unset($data['value']);

        return $this->model->create($data);
    }
}