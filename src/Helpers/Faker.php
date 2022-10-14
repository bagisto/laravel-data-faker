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
        'customers'  => Customer::Class,
        'categories' => Category::Class,
        'products'   => Product::Class,
    ];

    /**
     * Fake data
     *
     * @param  string  $entity
     * @param  integer  $count
     * @param  string  $productType
     * @return void
     */
    public function fake(
        $entity,
        $count,
        $productType
    )
    {
        if ($entity == 'products') {
            app($this->entities[$entity])->create($count, strtolower($productType ?? 'all'));
        } else {
            app($this->entities[$entity])->create($count);
        }
    }
}