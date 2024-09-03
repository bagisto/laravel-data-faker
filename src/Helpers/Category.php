<?php

namespace Webkul\Faker\Helpers;

use Illuminate\Support\Facades\Event;
use Webkul\Category\Models\Category as CategoryModel;

class Category
{
    /**
     * Create a categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function create(int $count)
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
        return CategoryModel::factory()
            ->afterCreating(function ($category) {
                Event::dispatch('catalog.category.create.after', $category);
            })
            ->hasTranslations();
    }
}
