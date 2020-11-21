<?php

namespace Modules\Shop\Http\Controllers;


use Illuminate\Support\Collection;
use Modules\Shop\Facades\Category as CategoryFacade;
use Modules\Product\Entities\Product;
use Modules\Product\Repositories\ProductManager;
use Modules\Product\Repositories\ProductRepository;
use Modules\Product\Repositories\CategoryRepository;

use Modules\Core\Http\Controllers\BasePublicController;

/**
 * ProductController
 */
class ProductController extends BasePublicController
{

    /**
     * @var ProductRepository
     */
    private $product;

    /**
     * @var ProductManager
     */
    private $productManager;

    /**
     * @var CategoryRepository
     */
    private $category;

    /**
     * Display a listing of the resource.
     *
     * @param ProductRepository $product
     * @param ProductManager $productManager
     * @param CategoryRepository $category
     */
    public function __construct(ProductRepository $product, ProductManager $productManager, CategoryRepository $category)
    {
        parent::__construct();

        $this->product = $product;
        $this->productManager = $productManager;
        $this->category = $category;
    }

    /**
     * Category View
     * @param  Collection $routeCategories
     * @return \Illuminate\View\View
     */
    public function category(Collection $routeCategories)
    {
        return view('shop.product.category', compact('routeCategories'));
    }

    /**
     * Detail View
     * @param  Product $product
     * @return \Illuminate\View\View
     */
    public function detail(Product $product)
    {
        $routeCategories = CategoryFacade::getWithAncestors($product->category);
        $recommendStudents = null;
        $recommendAcademies = null;
        //원생 추천용 Profile 추가
        if($this->auth->user()){
            $profile = $this->auth->user()->load('profile');
            $recommendStudents = json_decode($profile->profile->student_names);
            $recommendAcademies = json_decode($profile->profile->student_academies);
        }

        return view('shop.product.detail', compact('product', 'routeCategories','recommendStudents','recommendAcademies'));
    }

}
