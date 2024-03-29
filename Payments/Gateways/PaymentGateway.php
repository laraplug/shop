<?php

namespace Modules\Shop\Payments\Gateways;

use Carbon\Carbon;

use Modules\Order\Entities\Transaction;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use JsonSerializable;
use Modules\Order\Entities\OrderStatus;
use Modules\Shop\Contracts\ShopOrderInterface;
use Modules\Shop\Contracts\ShopPaymentMethodInterface;
use Modules\Shop\Contracts\ShopTransactionInterface;
use Modules\Shop\Entities\PaymentGatewayConfig;

/**
 * 결제 게이트웨이
 * Payment Gateway
 */
abstract class PaymentGateway implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * 결제모듈 API 객체
     * Payment module's API context
     * @var object
     */
    protected $api;

    /**
     * 결제모듈 API 판매자ID
     * Payment module's API merchant id
     * @var object
     */
    protected $merchantId;

    /**
     * 결제모듈 API 토큰
     * Payment module's API merchant token
     * @var object
     */
    protected $merchantToken;

    /**
     * 결제에 사용될 주문
     * Order Model for paying
     * @var ShopOrderInterface
     */
    protected $order;

    /**
     * 사용가능한 결제수단 목록
     * @var array
     */
    protected $supportedPaymentMethods = [];

    /**
     * 허용된 결제수단 목록
     * @var array
     */
    protected $allowedPaymentMethodIds = [];

    /**
     * 현재 세팅된 결제수단
     * @var array
     */
    protected $paymentMethod = null;

    /**
     * 거래시 생성되는 메세지
     * Gateway generated message
     *
     * @var string
     */
    protected $message = null;

    /**
     * 게이트웨이에서 생성된 거래ID
     * Gateway transaction id
     *
     * @var mixed
     */
    protected $transactionId;

    /**
     * Status id before pay order.
     *
     * @var string
     */
    protected $initialStatusId = OrderStatus::PENDING_PAYMENT;

    /**
     * 결제폼 전송시 실행될 자바스크립트
     * Javascript Method name to be called when payform submit
     * @var object
     */
    protected $payButtonOnClick;

    /**
     * 옵션 Key와 value
     * Options Key & Value
     * @var string
     */
    protected $options = [

    ];

    /**
     * 게이트웨이 ID
     * Get Gateway ID (for identifying)
     * @return string
     */
    public function getId()
    {
        return strtolower(class_basename($this));
    }

    /**
     * 게이트웨이 이름
     * Get Gateway Name
     * @return string
     */
    public function getName()
    {
        return class_basename($this);
    }

    /**
     * 게이트웨이 로그경로
     * Get Gateway Name
     * @return string
     */
    public function getLogPath()
    {
        $path = storage_path("logs/{$this->getId()}");
        // 로그폴더 없다면 생성
        if (!File::exists($path)) {
            File::makeDirectory($path);
        }

        return $path;
    }

    /**
     * 게이트웨이 옵션
     * Get Gateway Options
     * @return string
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * 게이트웨이 옵션 가져오기
     * Get Gateway Option
     * @param string $key
     * @return string
     */
    public function getOptionValue($key)
    {
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    /**
     * Get Option Name
     * @param  string $key
     * @return string
     */
    public function getOptionName($key)
    {
        return strtoupper($key);
    }

    /**
     * 게이트웨이 추가정보
     * Get Gateway Additional Data
     * @return string
     */
    public function getAdditionalData()
    {
        return [];
    }

    /**
     * 상점별 게이트웨이 설정적용
     * Set Gateway Config per Shop
     *
     * @param  PaymentGatewayConfig $config
     * @return void
     */
    public function setConfig(PaymentGatewayConfig $config)
    {
        $this->merchantId = $config->merchant_id;
        $this->merchantToken = $config->merchant_token;
        $this->allowedPaymentMethodIds = $config->enabled_method_ids;

        foreach ($config->options as $key => $value) {
            if (isset($this->options[$key])) {
                $this->options[$key] = $value;
            }
        }
    }

    /**
     * 게이트웨이에서 사용할 주문세팅
     * Set Order for Gateway
     *
     * @param  ShopOrderInterface $order
     * @return void
     */
    public function setOrder(ShopOrderInterface $order)
    {
        $this->order = $order;
        $supported = $this->getSupportedPaymentMethods();
        $this->paymentMethod = $supported->first(function ($method) use ($order) {
            return $method::getId() == $order->payment_method_id;
        });
    }

    /**
     * 허용된 결제수단 리턴
     * @return bool
     */
    public function getAllowedPaymentMethods()
    {
        $supported = $this->getSupportedPaymentMethods();
        if (empty($this->allowedPaymentMethodIds) || !is_array($this->allowedPaymentMethodIds)) {
            return $supported;
        }

        return $supported->filter(function ($method) {
            return in_array($method::getId(), $this->allowedPaymentMethodIds);
        });
    }

    /**
     * 지원되는(구현된) 결제수단 리턴
     * @return bool
     */
    public function getSupportedPaymentMethods()
    {
        return collect($this->supportedPaymentMethods)
        ->filter(function ($method) {
            // PaymentMethod 클래스여야만 적용됨
            return is_subclass_of($method, ShopPaymentMethodInterface::class);
        });
    }

    /**
     * 결제수단 지원여부 확인
     * @param  string  $method
     * @return bool
     */
    public function isSupported($method)
    {
        return in_array($method, $this->supportedPaymentMethods);
    }

    /**
     * @param object $merchantId
     *
     * @return static
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @param object $merchantToken
     *
     * @return static
     */
    public function setMerchantToken($merchantToken)
    {
        $this->merchantToken = $merchantToken;
    }

    /**
     * 결제페이지 준비 (결제를 위한 추가적인 HTML을 리턴)
     * Prepare payment page (Returns additional html to insert)
     *
     * @param  string             $submitUrl
     * @return string
     */
    public function preparePayment($submitUrl)
    {
        if ($this->order->status_id !== OrderStatus::PENDING_PAYMENT) {
            $message = trans('shop::payments.messages.cannot pay');

            return "
            <script>
            alert('$message');
            history.back();
            </script>
            ";
        }

        return "";
    }

    /**
     * 결제실행버튼 스크립트
     * Get payment button javascript
     *
     * @return string
     */
    public function getPayButtonOnClick()
    {
        return $this->payButtonOnClick ?: 'this.form.submit()';
    }

    /**
     * 결제하기
     * Method to run pay
     *
     * @param  array $data
     * @return ShopTransactionInterface|null
     */
    public function pay($data = null)
    {
        return null;
    }

    /**
     * 결제성공시
     * Callback after payment
     *
     * @param  int $amount
     * @param  string $bankName
     * @param  string $bankAccount
     * @param  array  $additionalData
     * @return ShopTransactionInterface|null
     */
    protected function onPaySucceed($amount, $bankName = '', $bankAccount = '', $additionalData = [])
    {
        $this->order->status_id = OrderStatus::PENDING;
        $this->order->save();

        $userId = isset(Auth::user()->id)?Auth::user()->id:1;

        return $this->order->placeTransaction(
            $userId,
            $this->getId(),
            $this->order->payment_method,
            $this->transactionId,
            $this->order->currency_code,
            $amount,
            $this->message,
            $bankName,
            $bankAccount,
            $additionalData
        );
    }

    /**
     * 취소하기
     * Method to run cancel
     *
     * @param  Transaction $transaction
     * @param  string      $reason
     * @return ShopTransactionInterface|null
     */
    public function cancel(Transaction $transaction, $reason = null)
    {
        return null;
    }

    /**
     * 취소성공시
     * Callback after payment
     *
     * @param  Transaction $transaction
     * @param  string      $reason
     * @return ShopTransactionInterface|null
     */
    protected function onCancelSucceed(Transaction $transaction, $reason = null)
    {
        $transaction->cancelled_at = Carbon::now();
        $transaction->cancel_reason = $reason;
        $transaction->save();

        $this->order->status_id = OrderStatus::CANCELED;
        $this->order->save();

        return $transaction;
    }

    /**
     * 주문시 처음 세팅되는 status id
     * Returns initial status id (used when placing order)
     *
     * @return string
     */
    public function getInitialStatusId()
    {
        return $this->initialStatusId;
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return ['id' => $this->id];
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
