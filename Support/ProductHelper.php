<?php

namespace Modules\Shop\Support;

use Modules\Product\Entities\Category;
use Modules\Shop\Entities\Shop;
use Modules\Product\Repositories\ProductRepository;
use Modules\Product\Repositories\CategoryRepository;

class ProductHelper
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
     * Get Lastest Products
     * @param  int $limit
     * @return mixed
     */
    public function getLatest($limit = 10)
    {
        return $this->shop->products()->paginate($limit);
    }

    /**
     * Get Lastest Products
     * @param  int $categoryId
     * @param  int $limit
     * @return mixed
     */
    public function getByCategory($categoryId, $limit = 10)
    {
        $category = $this->category->find($categoryId);
        if(!$category) return collect();
        $categoryIds = collect($this->getChildrenCategories($category))->pluck('id');
        return $this->shop->products()->whereIn('category_id', $categoryIds)->paginate($limit);
    }

    protected function getChildrenCategories(Category $category, $results = [])
    {
        $results[] = $category;
        foreach ($category->children as $child) {
            $results = $this->getChildrenCategories($child, $results);
        }
        return $results;
    }

}
