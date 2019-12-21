<?php

namespace Webkul\DataFaker\Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class CustomerAddressTableDataSeeder extends Seeder
{
    public function run()
    {
        $count = 100;
        DB::table('customers')->delete();
        DB::table('customer_addresses')->delete();

        $data = factory(\Webkul\Customer\Models\CustomerAddress::class, $count)->create();
    }
}