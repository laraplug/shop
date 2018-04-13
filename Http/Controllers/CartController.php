<?php

namespace Modules\Shop\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Modules\Core\Http\Controllers\BasePublicController;
use Modules\Cart\Repositories\CartItemRepository;

/**
 * CartController
 */
class CartController extends BasePublicController
{
    /**
     * @var CartItemRepository
     */
    private $cartItem;

    /**
     * @param CartItemRepository $cartItem
     */
    public function __construct(CartItemRepository $cartItem)
    {
        parent::__construct();

        $this->cartItem = $cartItem;
    }

    /**
     * Cart View
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function view(Request $request)
    {
        return view('shop.cart.view');
    }

}
