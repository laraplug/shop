<?php

namespace Modules\Shop\Support;

use Modules\Shop\Entities\Shop;
use Modules\Shop\Contracts\ShopOrderInterface;
use Modules\Shop\Repositories\ShopRepository;
use Modules\Shop\Repositories\CurrencyRepository;
use Modules\Shop\Repositories\PaymentGatewayManager;
use Modules\Order\Entities\OrderStatus;

use Modules\Order\Repositories\OrderRepository;

use Jenssegers\Agent\Facades\Agent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ShopHelper
{
    /**
     * @var Shop
     */
    private $shop;

    /**
     * @var CurrencyRepository
     */
    private $currency;

    /**
     * @var OrderRepository
     */
    private $order;

    /**
     * @var PaymentGatewayManager
     */
    private $gateways;

    /**
     * @param ShopRepository $shop
     * @param CurrencyRepository $currency
     * @param OrderRepository $order
     *
     */
    public function __construct(CurrencyRepository $currency, OrderRepository $order, PaymentGatewayManager $gateways)
    {
        $this->shop = Request::route('shop');
        $this->currency = $currency;
        $this->order = $order;
        $this->gateways = $gateways;
    }

    /**
     * Get current shop
     * @param  int $id
     * @return mixed
     */
    public function instance($id = 0)
    {
        if($id) {
            $this->shop = Shop::find($id);
        }
        else {
            $this->shop = Request::route('shop');
        }
        return $this;
    }

    /**
     * Get current shop
     * @return mixed
     */
    public function shop()
    {
        return $this->shop;
    }

    /**
     * Get Currencies
     * @return array
     */
    public function getCurrencies()
    {
        return $this->currency->all();
    }

    /**
     * Format Money
     * @param  int $price
     * @return string
     */
    public function money($price)
    {
        return money($price, $this->getCurrencyCode());
    }

    /**
     * Get Currency by code
     * @param string $code
     * @return array
     */
    public function currency($code = null)
    {
        $code = $code ?: $this->shop->currency_code;
        return $this->currency->getByAttributes(['code'=>$code])->first();
    }

    /**
     * Get currency code for current shop
     * @return mixed
     */
    protected function getCurrencyCode()
    {
        return $this->shop() ? $this->shop()->currency_code :  $this->getCurrencies()->first()->code;
    }

    /**
     * Place an Order
     * @param  array $data
     * @param  array $items
     * @return mixed
     */
    public function placeOrder(array $data, array $items)
    {
        $user = Auth::user();
        if(!$user) return false;

        $data['shop_id'] = $this->shop->id;
        $data['user_id'] = $user->id;

        $data['ip'] = Request::ip();
        $data['user_agent'] = Agent::browser();

        $data['currency_code'] = $this->shop->currency->code;
        $data['currency_value'] = $this->shop->currency->value;

        $data['status_id'] = OrderStatus::PENDING_PAYMENT;

        // Save order data
        $order = $this->order->create($data);

        // Save Items
        $order->importItems($items);

        // Set initial status of gateway
        $order->status_id = $order->payment_gateway->getInitialStatusId();
        $order->save();

        return $order;
    }

    /**
     * 게이트웨이별 결제수단 목록 가져오기
     * Get PaymentMethods of Gateways
     * @return array
     */
    public function getPaymentMethods()
    {
        return $this->shop->paymentGateways->flatMap(function($gateway) {
            $result = [];
            foreach ($gateway->getAllowedPaymentMethods() as $method) {
                $result[$gateway->getId().'|'.$method::getId()] = $method;
            }
            return $result;
        });
    }

    /**
     * 게이트웨이별 배송수단 목록 가져오기
     * Get PaymentMethods of Gateways
     * @return array
     */
    public function getShippingMethods()
    {
        return $this->shop->shippingGateways->mapWithKeys(function($gateway) {
            $result = [];
            foreach ($gateway->getAllowedShippingMethods() as $method) {
                $result[$gateway->getId().'|'.$method::getId()] = $method;
            }
            return $result;
        });
    }

    /**
     * 게이트웨이별 배송수단별 요금목록 가져오기
     * Get PaymentMethod Fees of Gateways
     * @return array
     */
    public function getShippingMethodFees()
    {
        return $this->shop->shippingGateways->mapWithKeys(function($gateway) {
            $result = [];
            foreach ($gateway->getAllowedShippingMethods() as $method) {
                $result[$gateway->getId().'|'.$method::getId()] = $method->getFee();
            }
            return $result;
        });
    }

    /**
     * 상품 단가 계산
     * Calculate Single Item Price
     * @param  array $item
     * @return int
     */
    public function calculateUnitPrice($item)
    {
        if(!isset($item['product'])) return 0;
        $salePrice = (int) $item['product']['sale_price'];
        $unitPrice = $salePrice;
        if (!empty($item['options'])) {
            foreach ($item['options'] as $option) {
                $priceValue = (int) $option['price_value'];
                if ($option['price_type'] == 'FIXED') {
                    $unitPrice += $priceValue;
                } elseif ($option['price_type'] == 'PERCENTAGE') {
                    $unitPrice += $salePrice * ($priceValue / 100);
                    $unitPrice = round($unitPrice);
                }
            }
        }
        return $unitPrice;
    }

    /**
     * 상품목록의 합계 계산
     * Caculate Total Price of Items
     * @param  array $items
     * @return int
     */
    public function calculateTotalPrice($items)
    {
        $totalPrice = 0;
        foreach ($items as $item) {
            $unitPrice = $this->calculateUnitPrice($item);
            $quantity = (int) $item['quantity'];
            $totalPrice += $unitPrice * $quantity;
        }

        return $totalPrice;
    }


}
