<?php

use Faker\Generator as Faker;

use Webkul\Product\Models\ProductAttributeValue;

$factory->define(\Webkul\Product\Models\ProductFlat::class, function (Faker $faker, $data) {
    $products = $data['product_id'];

    if ($products->type == 'simple') {

        $product = ['product_id' => $products->id];

        $fakeData = app('Webkul\DataFaker\Repositories\ProductFlatRepository')->GetProductFlatDummyData($faker, $products->type);

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
                'product_id' => $product['product_id'],
                'attribute_id' => $attribute->id,
                'value' => $fakeData[$attribute->code],
                'channel' => $attribute->value_per_channel ? $fakeData['channel'] : null,
                'locale' => $attribute->value_per_locale ? $fakeData['locale'] : null
            ];

            $attributeValue[ProductAttributeValue::$attributeTypeFields[$attribute->type]] = $attributeValue['value'];

            unset($attributeValue['value']);

            factory(\Webkul\Product\Models\ProductAttributeValue::class)->create($attributeValue);
        }

        $fakeImage = app('Webkul\DataFaker\Repositories\ProductFlatRepository')->uploadImages($faker, $product);

        factory(\Webkul\Product\Models\ProductImage::class, 5)->create($fakeImage);


        $category = app('Webkul\DataFaker\Repositories\ProductFlatRepository')->createProductCategories($product, $faker);

        return $fakeData;
    }
});
