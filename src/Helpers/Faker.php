<?php

namespace Webkul\Faker\Helpers;

class Faker
{
    /**
     * Contains faker classes.
     *
     * @var array
     */
    protected $entities = [
        'customers'  => Customer::class,
        'categories' => Category::class,
        'products'   => Product::class,
    ];

    /**
     * Fake data.
     *
     * @param  string  $entity
     * @param  int  $count
     * @param  string  $productType
     * @return void
     */
    public function fake(
        $entity,
        $count,
        $productType
    ) {
        return $entity == 'products'
            ? app($this->entities[$entity])->create($count, strtolower($productType ?? 'all'))
            : app($this->entities[$entity])->create($count);
    }
}
