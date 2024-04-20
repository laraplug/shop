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
        //임시삭제
//        TODO 가끔식 orderstatus 가 1(PENDING_PAYMENT)인 order 가 생성되어 결제버튼을 눌러도 변경이 안되는 부분이 있습니다. 재현해보려고 하였으나 진행이 안되어 남깁니다.
        if ($order = Order::scopeByUser($user->id, OrderStatus::PENDING_PAYMENT)->first()) {
            return redirect()->route('shop.order.pay.view', $order->id)->with('warning', trans('shop::payments.messages.payment pending order exists'));
        }
        if (Cart::count() == 0) {
            return redirect()->route('shop.cart')->with('warning', trans('shop::theme.cart is empty'));
        }
        $items = Cart::items();
        $totalShipping = Cart::getTotalShipping();
        $totalPrice = Cart::getTotalPrice();
        return view('shop.order.checkout', compact('items', 'totalShipping', 'totalPrice', 'user'));
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
        // 무통장선택 후 현금영수증 번호 입력했을때 shipping_phone 에 추가
        if(!empty($data['receipt_phone'])){
          $shipping_phone = $data['shipping_phone'];
          $recepit_phone = $data['receipt_phone'];
          $data['shipping_phone'] = $shipping_phone."/".$recepit_phone;
        }
        // 결제게이트웨이ID & 결제수단ID 파싱
        list($paymentGatewayId, $paymentMethodId) = explode('|', $request->payment_gateway_method);
        $data['payment_gateway_id'] = $paymentGatewayId;
        $data['payment_method_id'] = $paymentMethodId;

        // 주문저장 성공하면
        // If order placing succeed
        if ($order = Cart::placeOrder($data)) {
            Cart::flush();
            $message = "주문이 추가되었습니다 \n";
            $message +="주문번호: $order->id\n";
//            $message += "주문일시: $order->created_at\n\n";
//            $message += "결제 정보\n";
//            $message +="결제자명: $order->payment_name\n";
//            $message +="결제금액: $order->total_price\n";
//            $message +="결제방법: $paymentMethodId";
            var_dump($message);
                $this->sendSMS("$message",'01064185188');
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

    /***
     * enrePay 첫 실행히 보이는 뷰화면
     * @return mixed
     */
    public function enrePayView(Request $request)
    {

        return view('shop.order.enrePay',compact('request'));
    }
    /***
     * enrePay order 생성
     * @param Request $request
     * @return mixed
     */
    public function storeEnReOrder(Request $request)
    {
        $payData = $request->all();

        $data['shop_id'] = 1;
        $data['user_id'] = 1;
        $data['payment_name'] = 'EnReUtilityMall';
        $data['payment_address'] = '경기도 의왕시 내손동 갈미2로 6';
        $data['payment_address_detail'] = '잉리타워';
        $data['payment_phone'] = '031-476-5988';
        $data['shipping_address'] = '경기도 의왕시 내손동 갈미2로 6';
        $data['shipping_email'] = $payData['BuyerEmail'];
        $data['shipping_phone'] = '031-476-5988';
        $data['total_price'] = $payData['Amt'];
        $data['total_tax_amount'] = $payData['ServiceAmt'];
        $data['total'] = $payData['Amt'];
        $data['payment_gateway_id'] = 'nicepay';
        $data['payment_method_id'] = 'card';
        $data['status_id'] = 9;
        $data['currency_code'] = 'KRW';
        $data['currency_value'] = 1;
        $data['ip'] = isset($payData['UserIP'])?$payData['UserIP']:'0.0.0.0';
        $data['total_supply_amout'] = $payData['SupplyAmt'];
        $data['shipping_note'] = 'EnReUtilityMall 구매상품입니다.';
        $data['shipping_custom_field'] = $payData['shipping_custom_field'];
        $data['items'] = [];
        $payData['email'] = isset($payData['email'])?$payData['email']:'enre@enre.com';
        $order = $this->order->create($data);
//        $order->payment_gateway->pay($data);

        $transaction = $order->payment_gateway->pay($payData);

        return view('shop.order.enrePayEnd');
    }

    /**
     * @param Request $request
     * @return void
     */
    public function checkEnReOrder(Request $request, $cartToken = null){
        $enReOrderByCartToken = Order::query()->where('shipping_custom_field', $cartToken)->get();
        return count($enReOrderByCartToken);

    }
    ///SMS 발송 설정
    public function sendSMS($message, $to){
        // sms 보내기 추가
        $sID = "ncp:sms:kr:314615526549:gdbn_sens"; // 서비스 ID
        $smsURL = "https://sens.apigw.ntruss.com/sms/v2/services/".$sID."/messages";
        $smsUri = "/sms/v2/services/".$sID."/messages";
        $pNum="01043278799";
        $accKeyId = "C4st2WZUoE2HHuiIoJLV";
        $accSecKey = "3NxCXpz78AywSU1gXdMEuviR9kmnSm10TZF5rnFR";
        ////phoneNum 고정
        $to = '01043278799';
        $sTime = floor(microtime(true) * 1000);

        // The data to send to the API
        $postData = array(
            'type' => 'SMS',
            'countryCode' => '82',
            'from' => $pNum, // 발신번호 (등록되어있어야함)
            'contentType' => 'COMM',
            'content' => $message,
            'messages' => array(array('content' => $message, 'to' => $to))
        );

        $postFields = json_encode($postData) ;

        $hashString = "POST {$smsUri}\n{$sTime}\n{$accKeyId}";
        $dHash = base64_encode(hash_hmac('sha256', $hashString, $accSecKey, true));

        $header = array(
            // "accept: application/json",
            'Content-Type: application/json; charset=utf-8',
            'x-ncp-apigw-timestamp: '.$sTime,
            "x-ncp-iam-access-key: ".$accKeyId,
            "x-ncp-apigw-signature-v2: ".$dHash
        );

    // Setup cURL
        $ch = curl_init($smsURL);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => $postFields
        ));
        $response = curl_exec($ch);
    }

}
