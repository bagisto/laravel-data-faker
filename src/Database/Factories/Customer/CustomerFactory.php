<?php

namespace Webkul\DataFaker\Database\Factories\Customer;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use \Webkul\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $now = date("Y-m-d H:i:s");
        $password = $this->faker->password;

        return [
            'first_name'        => $this->faker->firstName(),
            'last_name'         => $this->faker->lastName,
            'gender'            => Arr::random(['male', 'female', 'other']),
            'email'             => $this->faker->email,
            'status'            => 1,
            'password'          => Hash::make($password),
            'customer_group_id' => 2,
            'is_verified'       => 1,
            'created_at'        => $now,
            'updated_at'        => $now,
            'notes'             => json_encode(['plain_password' => $password]),
        ];
    }
}