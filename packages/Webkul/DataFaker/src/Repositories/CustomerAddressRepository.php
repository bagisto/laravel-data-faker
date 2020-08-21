<?php

namespace Webkul\DataFaker\Repositories;

use Webkul\Core\Repositories\CountryRepository;
use Webkul\Core\Repositories\CountryStateRepository;

/**
 * CustomerAddress Reposotory
 *
 * @copyright 2019 Webkul Software Pvt Ltd (http://www.webkul.com)
 */
class CustomerAddressRepository
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
     * Create a new instance.
     *
     * @param  Webkul\Core\Repositories\CountryRepository      $countryRepository
     * @param  \Webkul\Core\Repositories\CountryStateRepository $countryStateRepository
     * @return void
     */
    public function __construct(
        CountryRepository $countryRepository,
        CountryStateRepository $countryStateRepository
    )
    {
        $this->countryRepository = $countryRepository;

        $this->countryStateRepository = $countryStateRepository;
    }

    /**
     * Dummy Data For Customer Table.
     *
     * @param $faker
     * @return mixed
     */
    public function customerAddressDummyData($faker)
    {
        $countries = $this->countryRepository->get()->random();
        $default_address = $faker->randomElement([1,0]);

        return [
            'customer_id' => factory('Webkul\Customer\Models\Customer')->create()->id,
            'address1' => $faker->address,
            'country' => $countries->code,
            'state' => $faker->state,
            'city' => $faker->city,
            'postcode' => $faker->postcode,
            'phone' => $faker->phoneNumber,
            'default_address' => $default_address,
            'address_type' => 'customer'
        ];
    }
}