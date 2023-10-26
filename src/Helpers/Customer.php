<?php

namespace Webkul\Faker\Helpers;

use Webkul\Customer\Models\Customer as CustomerModel;

class Customer
{
    /**
     * Create a customers.
     *
     * @param  int  $count
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function create($count)
    {
        return CustomerModel::factory()
            ->count($count)
            ->create();
    }
}
