<?php

namespace Modules\Shop\Support;

use Modules\Product\Entities\Category;
use Modules\Product\Repositories\CategoryRepository;

class CategoryHelper
{

    /**
     * @var Shop
     */
    private $shop;

    /**
     * @var CategoryRepository
     */
    private $category;

    /**
     * @param ShopHelper $shopHelper
     * @param CategoryRepository $category
     */
    public function __construct(ShopHelper $shopHelper, CategoryRepository $category)
    {
        $this->shop = $shopHelper->shop();
        $this->category = $category;
    }

    /**
     * Get category with ancestors as array
     * @param Category $current
     * @return bool
     */
    public function getWithAncestors(Category $current)
    {
        $categories = collect();
        do {
            $categories->prepend($current);
        } while($current = $current->parent);
        return $categories;
    }

    /**
     * Get model's slug with parents' slug as path
     * @param Category $current
     * @return bool
     */
    public function getSlugPath(Category $current)
    {
        return $this->getWithAncestors($current)->implode('slug', '/');
    }

    /**
     * Check if the current item is child of the slug
     * @param Category $current
     * @param Category $parent
     * @return bool
     */
    public function isDescendantOf(Category $current, Category $parent)
    {
        return (bool) $this->getWithAncestors($current)->where('slug', $parent->slug)->first();
    }

}
