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
        return $this->factory()
            ->count($count)
            ->create();
    }

    /**
     * Get a customer factory. This will provide a factory instance for
     * attaching additional features and taking advantage of the factory.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    public function factory()
    {
        return CustomerModel::factory();
    }
}
