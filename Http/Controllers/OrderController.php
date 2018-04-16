<?php

namespace Modules\Shop\Http\Controllers;

use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Cart\Facades\Cart;
use Modules\Core\Http\Controllers\BasePublicController;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderStatus;
use Modules\Order\Repositories\OrderRepository;
use Modules\Shop\Exceptions\GatewayException;
use Modules\Shop\Facades\Shop;

/**
 * OrderController
 */
class OrderController extends BasePublicController
{
    /**
     * @var OrderRepository
     */
    private $order;

    /**
     * @param OrderRepository $order
     */
    public function __construct(OrderRepository $order)
    {
        parent::__construct();

        $this->order = $order;
    }

    /**
     * 주문 폼 화면
     * Order Checkout Form
     *
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function createFromCart(Request $request)
    {
        $user = Auth::user()->load('profile');
        if ($order = Order::scopeByUser($user->id, OrderStatus::PENDING_PAYMENT)->first()) {
            return redirect()->route('shop.order.pay.view', $order->id)->with('warning', trans('shop::payments.messages.payment pending order exists'));
        }
        if (Cart::count() == 0) {
            return redirect()->route('shop.cart')->with('warning', trans('shop::theme.cart is empty'));
        }
        $items = Cart::items();
        $subTotal = Cart::getTotalPrice();

        return view('shop.order.checkout', compact('items', 'subTotal', 'user'));
    }

    /**
     * 장바구니 상품 주문처리
     * Order from Cart
     *
     * @param  Request $request
     * @return mixed
     */
    public function storeCart(Request $request)
    {
        if (Cart::count() == 0) {
            return redirect()->route('shop.cart')->with('warning', trans('shop::theme.cart is empty'));
        }
        $data = $request->all();
        // 주문정보와 다른 배송지를 사용하지 않으면
        if (empty($data['shipping_different_address'])) {
            $data['shipping_name'] = $data['payment_name'];
            $data['shipping_postcode'] = $data['payment_postcode'];
            $data['shipping_address'] = $data['payment_address'];
            $data['shipping_address_detail'] = $data['payment_address_detail'];
            $data['shipping_email'] = $data['payment_email'];
            $data['shipping_phone'] = $data['payment_phone'];
        }
        // 결제게이트웨이ID & 결제수단ID 파싱
        list($paymentGatewayId, $paymentMethodId) = explode('|', $request->payment_gateway_method);
        $data['payment_gateway_id'] = $paymentGatewayId;
        $data['payment_method_id'] = $paymentMethodId;

        // 배송비 계산
        $fees = Shop::getShippingMethodFees();
        $fee = array_get($fees, $request->shipping_gateway_method, 0);
        Cart::setShippingFee($fee);
        // 배송게이트웨이ID & 배송수단ID 파싱
        list($shippingGatewayId, $shippingMethodId) = explode('|', $request->shipping_gateway_method);
        $data['shipping_gateway_id'] = $shippingGatewayId;
        $data['shipping_method_id'] = $shippingMethodId;

        // 주문저장 성공하면
        // If order placing succeed
        if ($order = Cart::placeOrder($data)) {
            Cart::flush();

            return redirect()->route('shop.order.pay.view', $order->id);
        }

        // If order placing failed
        return redirect()->back()->withError('Order Failed');
    }

    /**
     * 결제 화면
     * Pay Form
     *
     * @param  Order   $order
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function payForm(Order $order, Request $request)
    {
        $user = Auth::user()->load('profile');

        // 결제승인 대기중인 주문이면
        // If watiting for approval
        if ($order->status_id == OrderStatus::PENDING_PAYMENT_APPROVAL) {
            return redirect()->route('shop.my.order.view', $order->id)->with('warning', trans('shop::payments.messages.waiting for approval'));
        }

        // 결제 게이트웨이에서 제공하는 뷰
        // Get Gateway View
        $gatewayView = $order->payment_gateway->preparePayment(route('shop.order.pay.store', $order->id));
        $payButtonOnClick = $order->payment_gateway->getPayButtonOnClick();

        return view('shop.order.pay', compact('order', 'user', 'gatewayView', 'payButtonOnClick'));
    }

    /**
     * 결제 처리
     * Pay Processing
     *
     * @param  Order   $order
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function pay(Order $order, Request $request)
    {
        $error = '';
        try {

            // 결제 성공하면
            // If pay succeed
            $transaction = $order->payment_gateway->pay($request->all());

            return redirect()->route('shop.my.order.index')->with('success', trans('shop::payments.messages.pay succeed'));
        } catch (GatewayException $e) {
            $error = $e->getMessage();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        // 결제에 실패하면
        // If pay failed
        return redirect()->back()->withError($error);
    }

    /**
     * 주문완료페이지
     * Show order complete page
     * @param  Request $request
     * @return mixed
     */
    public function complete(Request $request)
    {
        return view('shop.order.complete', compact(''));
    }

    /**
     * 주문취소
     * Show order cancel
     * @param  Order   $order
     * @param  Request $request
     * @return mixed
     */
    public function cancel(Order $order, Request $request)
    {
        // 결제전 상태면 바로 삭제됨
        // Delete order if not paid
        if ($order->status_id == OrderStatus::PENDING_PAYMENT || $order->status_id == OrderStatus::PENDING_PAYMENT_APPROVAL) {
            $order->delete();
            // 취소성공
            return redirect()->route('homepage')->with('success', trans('shop::theme.order cancelled'));
        }

        // 결제완료 상태면 취소가능 (주문처리 시작전)
        if($order->status_id == OrderStatus::PENDING) {
            // 취소되지 않은 모든 결제내역 리턴
            $error = '';
            foreach ($order->transactions as $transaction) {
                try {
                    // 결제취소 성공하면
                    // If pay cancel succeed
                    $transaction = $order->payment_gateway->cancel($transaction, trans('shop::payments.user cancel'));

                } catch (GatewayException $e) {
                    $error = $e->getMessage();
                    break;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                    break;
                }
            }

            // 에러가 있으면 원래페이지로
            if($error) {
                return redirect()->back()->withError($error);
            }

            // 에러가 없으면 취소성공
            return redirect()->route('shop.my.order.index')->with('success', trans('shop::theme.order cancelled'));
        }

        return redirect()->back()->withError(trans('shop::payments.messages.cannot cancel'));
    }
}
