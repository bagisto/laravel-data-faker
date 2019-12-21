<?php

namespace Webkul\DataFaker\Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use DB;

class ProductCategoryTableDataSeeder extends Seeder
{
    public function run()
    {
        DB::table('categories')->delete();

        $now = Carbon::now();

        DB::table('categories')->insert([
            ['id' => '1','position' => '1','image' => NULL,'status' => '1','_lft' => '1','_rgt' => '26','parent_id' => NULL, 'created_at' => $now, 'updated_at' => $now],

            ['id' => '2','position' => '2','image' => NULL,'status' => '1','_lft' => '14','_rgt' => '15','parent_id' => NULL, 'created_at' => $now, 'updated_at' => $now],

            ['id' => '3','position' => '3','image' => NULL,'status' => '1','_lft' => '16','_rgt' => '17','parent_id' => NULL, 'created_at' => $now, 'updated_at' => $now],

            ['id' => '4','position' => '4','image' => NULL,'status' => '1','_lft' => '18','_rgt' => '19','parent_id' => NULL, 'created_at' => $now, 'updated_at' => $now],

            ['id' => '5','position' => '5','image' => NULL,'status' => '1','_lft' => '20','_rgt' => '21','parent_id' => NULL, 'created_at' => $now, 'updated_at' => $now],

            ['id' => '6','position' => '6','image' => NULL,'status' => '1','_lft' => '22','_rgt' => '23','parent_id' => NULL, 'created_at' => $now, 'updated_at' => $now],

            ['id' => '7','position' => '7','image' => NULL,'status' => '1','_lft' => '24','_rgt' => '25','parent_id' => NULL, 'created_at' => $now, 'updated_at' => $now],

        ]);

        DB::table('category_translations')->insert([

            ['id' => '2','name' => 'Fashion','slug' => 'fashion','description' => 'Fashion','meta_title' => '','meta_description' => '','meta_keywords' => '','category_id' => '2','locale' => 'en'],

            ['id' => '3','name' => 'Blankets, Quilts & Wraps','slug' => 'blankets','description' => 'Blankets, Quilts & Wraps','meta_title' => '','meta_description' => '','meta_keywords' => '','category_id' => '3','locale' => 'en'],

            ['id' => '4','name' => 'Shoe','slug' => 'shoe','description' => 'Shoe','meta_title' => '','meta_description' => '','meta_keywords' => '','category_id' => '4','locale' => 'en'],

            ['id' => '5','name' => 'Toys','slug' => 'toys','description' => 'Toys','meta_title' => '','meta_description' => '','meta_keywords' => '','category_id' => '5','locale' => 'en'],

            ['id' => '6','name' => 'Body & Oral Care','slug' => 'bodycare','description' => 'Body & Oral Care','meta_title' => '','meta_description' => '','meta_keywords' => '','category_id' => '6','locale' => 'en'],
            ['id' => '7','name' => 'Cloths','slug' => 'cloths','description' => 'Cloths','meta_title' => '','meta_description' => '','meta_keywords' => '','category_id' => '7','locale' => 'en'],
        ]);
    }
}