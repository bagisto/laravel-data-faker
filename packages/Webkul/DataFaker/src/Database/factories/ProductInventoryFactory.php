<?php

use Faker\Generator as Faker;

$factory->define(\Webkul\Product\Models\ProductInventory::Class, function (Faker $faker) {

    $fakeData = [
        'qty' => $faker->randomNumber(2),
        'inventory_source_id' => 1
    ];

    return $fakeData;
});