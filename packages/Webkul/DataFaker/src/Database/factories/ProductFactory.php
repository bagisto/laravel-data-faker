<?php

use Faker\Generator as Faker;

$factory->define(\Webkul\Product\Models\Product::Class, function (Faker $faker) {

    $fakeData = app('Webkul\DataFaker\Repositories\ProductRepository')->productDummyData($faker);

    return $fakeData;
});