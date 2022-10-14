<?php

namespace Webkul\Faker\Helpers;

use Webkul\Category\Models\Category as CategoryModel;

class Category
{
    /**
     * Create a records
     *
     * @param  integer  $count
     * @return void
     */
    public function create($count)
    {
        CategoryModel::factory()
            ->count($count)
            ->hasTranslations()
            ->create();
    }
}