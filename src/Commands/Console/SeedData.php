<?php

namespace Webkul\DataFaker\Commands\Console;

use DB;
use Illuminate\Console\Command;
use Webkul\DataFaker\Database\Seeders\CustomerTableDataSeeder;
use Webkul\DataFaker\Database\Seeders\CategoryTableDataSeeder;
// use Webkul\DataFaker\Database\Seeders\ProductTableDataSeeder;

use Faker\Generator as Faker;
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

                        $seeder = new CustomerTableDataSeeder;
                        $seeder->callWith(CustomerTableDataSeeder::class, [$count]);

                        $this->comment('Customers Created Successfully.');
                        break;
                    case 2:
                        if ($this->confirm('Do you want to seed product category?')) {
                            session()->put('seed_product_category', true);
                        }
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

dd('test');
        if ($this->confirm('Confirm: Please confirm, Do you want to remove all tables and records?')) {
            $migrate = shell_exec('php artisan migrate:fresh');
        } else {
            $migrate = shell_exec('php artisan migrate');
        }

        $this->info($migrate);

        // running `php artisan vendor:publish --all`
        $this->warn('Step: Publishing Assets and Configurations...');
        $result = shell_exec('php artisan vendor:publish --all');
        $this->info($result);

        // running `php artisan storage:link`
        $this->warn('Step: Linking Storage directory...');
        $result = shell_exec('php artisan storage:link');
        $this->info($result);

        $this->comment('Info: Generating super user for the system.');
        $name = $this->ask('Input: Please enter super user name?');

        // $validator = Validator::make([
        //     'first_name' => $name
        // ], [
        //     'first_name' => 'required',
        // ]);

        // if ($validator->fails()) {
        //     $this->comment('Warning: Name invalid, please enter try again.');

        //     return false;
        // }

        // $this->comment('Info: You entered = '. $name);
        // $email = $this->ask('Input: Please enter email?');

        // $data = [
        //     'email' => $email
        // ];

        // $validator = Validator::make($data, [
        //     'email' => 'required|email|unique:super_admins,email',
        // ]);

        // if($validator->fails()) {
        //     $this->comment('Warning: Email already exists or invalid, please enter try again.');

        //     return false;
        // }

        // unset($data);

        // $this->comment('Info: You entered = ' . $email);
        // $password = $this->ask('Input: Please enter password?');
        // $data = ['password' => $password];

        // $validator = Validator::make($data, [
        //     'password' => 'required|string|min:6'
        // ]);

        // if ($validator->fails()) {
        //     $this->comment('Warning: Password invalid, make sure password is atleast 6 characters of length.');

        //     return false;
        // }

        // $this->comment('Info: You entered = '. $password);

        // unset($data);

        // if ($this->confirm('Confirm: Please confirm all the entered details are correct?')) {
        //     $data = [
        //         'first_name'    => $name,
        //         'email'         => $email,
        //         'password'      => bcrypt($password),
        //     ];

        //     $result = $this->generateSuperUserCompany($data);

        //     if ($result) {
        //         $this->comment('Success: Super user for the system is created successfully.');
        //     } else {
        //         $this->comment('Warning: Super user for the system already exists, please contact support@bagisto.com for troubleshooting.');
        //     }

        // } else {
        //     $this->comment('Warning: Please try again for creating the super user.');
        // }
    }
}