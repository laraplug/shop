<?php

namespace Modules\Shop\Shippings\Gateways;

use JsonSerializable;

use Modules\Shop\Entities\ShippingGatewayConfig;

use Modules\Shop\Contracts\ShopOrderInterface;
use Modules\Shop\Contracts\ShopTransactionInterface;
use Modules\Shop\Contracts\ShopTransportationInterface;
use Modules\Shop\Contracts\ShopShippingMethodInterface;
use Modules\Order\Entities\OrderStatus;
use Illuminate\Support\Facades\Auth;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * 배송 게이트웨이
 * Shipping Gateway
 */
abstract class ShippingGateway implements Arrayable, Jsonable, JsonSerializable
{

    /**
     * 배송모듈 API 객체
     * Shipping module's API context
     * @var object
     */
    protected $api;

    /**
     * 배송모듈 API 판매자ID
     * Shipping module's API merchant id
     * @var object
     */
    protected $merchantId;

    /**
     * 배송모듈 API 토큰
     * Shipping module's API merchant token
     * @var object
     */
    protected $merchantToken;

    /**
     * 배송에 사용될 주문
     * Order Model for shiping
     * @var ShopOrderInterface
     */
    protected $order;

    /**
     * 사용가능한 배송수단 목록
     * @var array
     */
    protected $supportedShippingMethods = [];

    /**
     * 허용된 배송수단 목록
     * @var array
     */
    protected $allowedShippingMethodIds = [];

    /**
     * 현재 세팅된 배송수단
     * @var string
     */
    protected $shippingMethod = null;

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
    protected $transportationId;

    /**
     * Status id before ship order.
     *
     * @var string
     */
    protected $initialStatusId = OrderStatus::PROCESSING;

    /**
     * 배송폼 전송시 실행될 자바스크립트
     * Javascript Method name to be called when ship form submit
     * @var object
     */
    protected $shipButtonOnClick;

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
     * @param  ShippingGatewayConfig $config
     * @return void
     */
    public function setConfig(ShippingGatewayConfig $config)
    {
        $this->merchantId = $config->merchant_id;
        $this->merchantToken = $config->merchant_token;
        $this->allowedShippingMethodIds = $config->enabled_method_ids;

        foreach ($config->options as $key => $value) {
            if(isset($this->options[$key])) $this->options[$key] = $value;
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
        $supported = $this->getSupportedShippingMethods();
        $this->shippingMethod = $supported->first(function($method) use ($order) {
            return $method::getId() == $order->shipping_method_id;
        });
    }


    /**
     * 허용된 배송수단 리턴
     * @return bool
     */
    public function getAllowedShippingMethods()
    {
        return $this->getSupportedShippingMethods()->only($this->allowedShippingMethodIds);
    }

    /**
     * 지원되는(구현된) 배송수단 리턴
     * @return bool
     */
    public function getSupportedShippingMethods()
    {
        return collect($this->supportedShippingMethods)
        ->filter(function($method) {
            // ShippingMethod 클래스여야만 적용됨
            return is_subclass_of($method, ShopShippingMethodInterface::class);
        })
        ->mapWithKeys(function($method) {
            return [$method::getId() => new $method($this->getFee($method))];
        });
    }

    /**
     * 배송수단 지원여부 확인
     * @param  string  $method
     * @return bool
     */
    public function isSupported($method)
    {
        return in_array($method, $this->supportedShippingMethods);
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
     * 배송페이지 준비 (배송를 위한 추가적인 HTML을 리턴)
     * Prepare shipment page (Returns additional html to insert)
     *
     * @param  string             $submitUrl
     * @return string
     */
    public function prepareShipping($submitUrl)
    {
        if($this->order->status_id !== OrderStatus::PROCESSED) {
            $message = trans('shop::shippings.messages.cannot ship');
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
     * 배송실행버튼 스크립트
     * Get shipment button javascript
     *
     * @return string
     */
    public function getShipButtonOnClick()
    {
        return $this->shipButtonOnClick ?: 'this.form.submit()';
    }

    /**
     * 배송시작버튼
     * Method to run shipping
     *
     * @param  array $data
     * @return ShopTransportationInterface|null
     */
    public function ship($data = null)
    {
        return null;
    }

    /**
     * 배송시작성공시 콜백
     * Callback after shipping
     *
     * @param  int $fee
     * @return ShopTransportationInterface|null
     */
    protected function onShipSucceed($fee)
    {
        $this->order->status_id = OrderStatus::SHIPPING;
        $this->order->save();

        $userId = Auth::user()->id;
        return $this->order->placeTransportation(
            $userId,
            $this->getId(),
            $this->order->shipping_method,
            $this->transportationId,
            $this->order->currency_code,
            $fee,
            $this->message
        );
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
     * 배송요금 가져오기
     * @param  ShopShippingMethodInterface $method
     * @return int
     */
    public function getFee($method)
    {
        return 0;
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
