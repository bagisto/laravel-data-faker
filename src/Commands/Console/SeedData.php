<?php

namespace Webkul\DataFaker\Commands\Console;

use DB;
use Faker\Generator as Faker;
use Illuminate\Console\Command;
use Webkul\DataFaker\Database\Seeders\CustomerTableDataSeeder;
use Webkul\DataFaker\Database\Seeders\CategoryTableDataSeeder;
use Webkul\DataFaker\Database\Seeders\ProductTableDataSeeder;
use Webkul\Category\Repositories\CategoryRepository;

class SeedData extends Command
{
    /**
     * Holds the execution signature of the command needed
     * to be executed for generating super user
     */
    protected $signature = 'seed:fake:data';

    /**
     * Will inhibit the description related to this
     * command's role
     */
    protected $description = 'Generates fake data for system';

    protected $faker;
    protected $categoryRepository;

    public function __construct(
        Faker $faker,
        CategoryRepository $categoryRepository
    ) {
        parent::__construct();
        $this->faker = $faker;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Does the all sought of lifting required to be performed for
     * generating fake data
     */
    public function handle()
    {

        $input = $this->ask('Input: What do you want to seed, press the number?
            1-> Customers
            2-> Products
            3-> Categories'
        );

        if ($input <= 3 & $input >= 1) {

            $this->comment('This may take some time accoring to the input value.');

            if($input != 3) {
                $count = $this->ask('Enter Seeder Limit');
            } else {
                $count = 1;
            }

            if($count > 0) {
                $this->comment('Seeding  may take some time according to seeder limit.');

                switch($input) {
                    case 1:
                        if($this->confirm('Do you want to delete old customer\'s?')) {
                            DB::table('customers')->delete();
                            DB::table('addresses')->where('address_type', 'customer')->delete();
                        }

                        $this->comment('Seeding...');

                        $seeder = new CustomerTableDataSeeder;
                        $seeder->callWith(CustomerTableDataSeeder::class, [$count]);

                        $this->comment('Customers Created Successfully.');
                        break;
                    case 2:
                        if ($this->confirm('Do you want to seed product category?')) {
                            session()->put('seed_product_category', true);
                        }

                        if ($this->confirm('Do you want to seed configurable products ?')) {
                            session()->put('seed_config_product', true);
                        }

                        $this->comment('Seeding...');

                        $seeder = new ProductTableDataSeeder;
                        $seeder->callWith(ProductTableDataSeeder::class, [$count]);

                        $this->comment('Products Created Successfully.');
                        break;
                    case 3:
                        $parent = $this->ask('Enter the number of parent categories');
                        $child = $this->ask('Enter the number of child categories');

                        if($this->confirm('Do you want to delete old Categorie\'s?')) {
                            DB::table('categories')
                            ->where('categories.id' ,'<>' ,1)
                            ->delete();
                        }

                        $this->comment('Seeding  may take some time according to seeder limit.');

                        if($parent > 0 && $child > 0) {

                            $this->comment('Seeding...');

                            $categorySeeder = new CategoryTableDataSeeder();
                            $categorySeeder->callWith(CategoryTableDataSeeder::class, [$parent, $child,$this->faker]);

                            $this->comment('Categories Created Successfully.');
                        } else {
                            $this->warn('Warning: value must be greater then 0.');
                        }
                }
            } else {
                $this->warn('warning: Seeder limit must be greater then 0.');
            }

        } else {
            $this->comment('Warning: Please select a valid number.');
        }

        session()->forget('seed_product_category');
        session()->forget('seed_config_product');
    }
}