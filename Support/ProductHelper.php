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
        $this->shop = $shopHelper->model();
        $this->category = $category;
    }

    /**
     * 상점의 상품 쿼리
     * Products Query
     * @return mixed
     */
    public function productsQuery()
    {
        return $this->shop->products()->where('status', 'active');
    }

    /**
     * Get Lastest Products
     * @param  int $limit
     * @return mixed
     */
    public function getLatest($limit = 10)
    {
        return $this->productsQuery()->paginate($limit);
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
        return $this->productsQuery()->whereIn('category_id', $categoryIds)->paginate($limit);
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
