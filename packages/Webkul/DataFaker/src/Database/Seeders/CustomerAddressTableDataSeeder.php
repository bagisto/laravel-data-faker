<?php

namespace Webkul\DataFaker\Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class CustomerAddressTableDataSeeder extends Seeder
{
    public function run()
    {
        $count = 10;
        DB::table('customers')->delete();
        DB::table('addresses')->where('address_type', 'customer')->delete();

        $data = factory(\Webkul\Customer\Models\CustomerAddress::class, $count)->create();
    }
}