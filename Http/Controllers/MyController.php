<?php

namespace Modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Modules\User\Contracts\Authentication;
use Modules\User\Repositories\UserRepository;

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
     * @var UserRepository
     */
    protected $user;

    /**
     * @inheritDoc
     */
    public function __construct(Authentication $auth, OrderRepository $order, UserRepository $user)
    {
        parent::__construct();

        $this->auth = $auth;
        $this->order = $order;
        $this->user = $user;
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

    /**
     * 프로필
     * Profile
     *
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function profile(Request $request)
    {
        $user = $this->auth->user();
        $user->load('profile');
        return view('shop.my.profile', compact('user'));
    }

    /**
    * 프로필 저장
    * Profile Store
    *
    * @param  Request $request
    * @return \Illuminate\View\View
    */
   public function profileStore(Request $request)
   {
       $data = $request->all();
       $user = $this->auth->user();

       $this->user->update($user, $request->all());

       return redirect()->route('shop.my.profile')->with('success', trans('shop::theme.profile saved'));
   }

}
