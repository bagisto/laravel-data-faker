<?php

namespace Webkul\DataFaker\Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\DataFaker\Database\Factories\Customer\CustomerAddressFactory;
use Webkul\DataFaker\Database\Factories\Customer\CustomerFactory;

class CustomerTableDataSeeder extends Seeder
{
    public function run($count)
    {
        $customerFactory = new CustomerFactory();
        $customerAddressFactory = new CustomerAddressFactory();

        //seed fake customers and related addresses
        $customerFactory
            ->count($count)
            ->has($customerAddressFactory->count(2), 'addresses')
            ->create();
    }
}