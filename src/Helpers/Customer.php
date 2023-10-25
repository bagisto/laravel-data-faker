<?php

namespace Webkul\Faker\Helpers;

use Webkul\Customer\Models\Customer as CustomerModel;

class Customer
{
    /**
     * Create a records
     *
     * @param  integer  $count
     * @return void
     */
    public function create($count)
    {
        return CustomerModel::factory()
            ->count($count)
            ->create();
    }
}
