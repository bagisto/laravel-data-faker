<?php

namespace Webkul\DataFaker\Database\Factories\Customer;

use \Webkul\Customer\Models\CustomerAddress;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class CustomerAddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustomerAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_name'    => $this->faker->company,
            'first_name'      => $this->faker->firstName,
            'last_name'       => $this->faker->lastName,
            'address1'        => $this->faker->streetAddress,
            'country'         => $this->faker->countryCode,
            'state'           => $this->faker->state,
            'city'            => $this->faker->city,
            'postcode'        => $this->faker->postcode,
            'phone'           => $this->faker->e164PhoneNumber,
            'default_address' => Arr::random([0, 1]),
            'address_type'    => CustomerAddress::ADDRESS_TYPE,
        ];
    }
}