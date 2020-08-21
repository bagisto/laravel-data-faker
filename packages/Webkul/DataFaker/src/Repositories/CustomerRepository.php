<?php

namespace Webkul\DataFaker\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Core\Repositories\CountryRepository;
use Webkul\Core\Repositories\CountryStateRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

/**
 * Customer Reposotory
 *
 * @copyright 2019 Webkul Software Pvt Ltd (http://www.webkul.com)
 */
class CustomerRepository
{
    /**
     * Country Repository Object
     *
     * @var array
     */
    protected $countryRepository;

    /**
     *  Country State Repository Object
     *
     * @var array
     */
    protected $countryStateRepository;

    /**
     *  Customer Group Repository Object
     *
     * @var array
     */
    protected $customerGroupRepository;

    /**
     * Create a new instance.
     *
     * @param  Webkul\Core\Repositories\CountryRepository      $countryRepository
     * @param  \Webkul\Core\Repositories\CountryStateRepository $countryStateRepository
     * @param  Webkul\Customer\Repositories\CustomerGroupRepository $customerGroupRepository
     * @return void
     */
    public function __construct(
        CountryRepository $countryRepository,
        CountryStateRepository $countryStateRepository,
        CustomerGroupRepository $customerGroupRepository
    )
    {
        $this->countryRepository = $countryRepository;

        $this->countryStateRepository = $countryStateRepository;

        $this->customerGroupRepository = $customerGroupRepository;
    }

    /**
     * Dummy Data For Customer Table.
     *
     * @return mixed
     */
    public function customerDummyData($faker)
    {
        $gender = $faker->randomElement(['male', 'female']);

        $customerGroup = $this->customerGroupRepository->get()->random();

        return [
        'first_name' => $faker->firstName($gender),
        'last_name' => $faker->lastName,
        'gender' => $gender,
        'date_of_birth' => $faker->date($format = 'Y-m-d', $max = 'now'),
        'email' => $faker->unique()->safeEmail(),
        'phone' => $faker->e164PhoneNumber,
        'password' => bcrypt('admin123'),
        'customer_group_id' => $customerGroup->id,
        'is_verified' => 1,
        'remember_token' =>str_random(10)
    ];
    }
}