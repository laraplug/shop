<?php

namespace Modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Modules\User\Contracts\Authentication;
use Modules\Order\Entities\Order;
use Modules\Core\Http\Controllers\BasePublicController;
use Modules\Order\Repositories\OrderRepository;

/**
 * MyController
 */
class MyController extends BasePublicController
{
    /**
     * @var Authentication
     */
    protected $auth;

    /**
     * @var OrderRepository
     */
    protected $order;

    /**
     * @inheritDoc
     */
    public function __construct(Authentication $auth, OrderRepository $order)
    {
        parent::__construct();

        $this->auth = $auth;
        $this->order = $order;
    }

    /**
     * 대시보드
     * Dashboard
     *
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        $user = $this->auth->user();
        $user->load('profile');
        return view('shop.my.dashboard', compact('user'));
    }

    /**
     * 주문목록
     * Order Index Table
     *
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function orders(Request $request)
    {
        $user = $this->auth->user();
        $user->load('profile');
        $orders = Order::scopeByUser($user->id)->latest()->paginate(10);
        return view('shop.my.order.index', compact('user', 'orders'));
    }

    /**
     * 주문보기
     * Order View
     *
     * @param  Order $order
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function orderView(Order $order, Request $request)
    {
        $user = $this->auth->user();
        $user->load('profile');
        return view('shop.my.order.view', compact('user', 'order'));
    }

}
