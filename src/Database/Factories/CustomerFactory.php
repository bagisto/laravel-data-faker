<?php

use Faker\Generator as Faker;

$factory->define(\Webkul\Customer\Models\Customer::class, function (Faker $faker) {
    $fakeData = app('Webkul\DataFaker\Repositories\CustomerRepository')->customerDummyData($faker);

    return $fakeData;
});
