<?php

namespace Webkul\Faker\Helpers;

use Webkul\Category\Models\Category as CategoryModel;

class Category
{
    /**
     * Create a categories.
     *
     * @param  int  $count
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function create($count)
    {
        return CategoryModel::factory()
            ->count($count)
            ->hasTranslations()
            ->create();
    }
}
