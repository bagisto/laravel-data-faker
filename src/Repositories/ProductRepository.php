<?php

namespace Webkul\DataFaker\Repositories;

use Webkul\Attribute\Repositories\AttributeRepository;

/**
 * Data Faker Product Reposotory
 *
 */
class ProductRepository
{
    /**
     * AttributeRepository object
     *
     * @var object
     */
    protected $attribute;

    /**
     * createProductCategories a new instance.
     *
     * @param  Webkul\Attribute\Repositories\AttributeRepository  $attribute
     * @return void
     */
    public function __construct(AttributeRepository $attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * Super Attibutes For configurable product
     *
     * @param $product
     * @return array
     */
    public function getSuperAttribute($product)
    {
        $data = [
            'super_attributes' =>[
                'size' => [
                    0 => 6,
                    1 => 7,
                ]
            ],
            'family' => 1
        ];

        $super_attributes = [];

        foreach ($data['super_attributes'] as $attributeCode => $attributeOptions) {
            $attribute = $this->attribute->findOneByField('code', $attributeCode);

            $super_attributes[$attribute->id] = $attributeOptions;

            $product->super_attributes()->attach($attribute->id);
        }

        return ['data' => $data, 'superAttribute' => array_permutation($super_attributes)];
    }
}