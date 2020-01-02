<?php

namespace Webkul\DataFaker\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CustomerAddressTableDataSeeder::class);
        $this->call(ProductCategoryTableDataSeeder::class);
        $this->call(ProductTableDataSeeder::class);
    }
}