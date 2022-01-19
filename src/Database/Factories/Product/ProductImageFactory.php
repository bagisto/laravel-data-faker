<?php

namespace Webkul\DataFaker\Database\Factories\Product;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\Product\Models\ProductImage;

class ProductImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductImage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'path'                 => $this->faker->uuid,
        ];
    }

    /**
     * Upload Product Images
     *
     * @param array $productFaker
     * @param int $productId
     * @return array
     */
    public function uploadImages($productId)
    {
        $filepath = storage_path('app/public/product/');
        Storage::makeDirectory('/product/'. $productId);

        $path = $this->faker->image($filepath. $productId, 800, 800, 'food', true, true);

        $pos = strpos($path, 'product');
        $imagePath = substr($path, $pos);

        $data = [
            'path' => $imagePath,
            'product_id' => $productId
        ];

        return $data;
    }
}