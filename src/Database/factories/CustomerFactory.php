<?php

use Faker\Generator as Faker;

$factory->define(\Webkul\Customer\Models\Customer::Class, function (Faker $faker) {

    $fakeData = app('Webkul\DataFaker\Repositories\CustomerRepository')->customerDummyData($faker);

    return $fakeData;
});