<?php

namespace Webkul\DataFaker\Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class CustomerAddressTableDataSeeder extends Seeder
{
    public function run($count)
    {
        $data = factory(\Webkul\Customer\Models\CustomerAddress::class, (int)$count)->create();
    }
}