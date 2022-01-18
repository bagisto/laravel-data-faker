<?php

namespace Webkul\DataFaker\Database\Seeders;

use Faker\Generator as Faker;
use Illuminate\Database\Seeder;

class CategoryTableDataSeeder extends Seeder
{
    private $numberOfParentCategories;

    private $numberOfChildCategories;

    public function __construct() {
        $this->categoryRepository = app('Webkul\Category\Repositories\CategoryRepository');
    }

    public function run( int $parent, int $child, $faker)
    {
        $this->faker = $faker;
        $this->numberOfParentCategories = $parent;
        $this->numberOfChildCategories = $child;

        for ($i = 0; $i < $this->numberOfParentCategories; ++$i) {
            $createdCategory = $this->categoryRepository->create([
                'slug'        => $this->faker->slug,
                'name'        => $this->faker->firstName,
                'description' => $this->faker->text(),
                'parent_id'   => 1,
                'status'      => 1,
            ]);

            if ($createdCategory) {
                for ($j = 0; $j < $this->numberOfChildCategories; ++$j) {

                    $this->categoryRepository->create([
                        'slug'        => $this->faker->slug,
                        'name'        => $this->faker->firstName,
                        'description' => $this->faker->text(),
                        'parent_id'   => $createdCategory->id,
                        'status'      => 1
                    ]);
                }
            }
        }
    }
}