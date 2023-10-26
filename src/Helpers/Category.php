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
        return $this->factory()
            ->count($count)
            ->create();
    }

    /**
     * Get a category factory. This will provide a factory instance for
     * attaching additional features and taking advantage of the factory.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    public function factory()
    {
        return CategoryModel::factory()->hasTranslations();
    }
}
