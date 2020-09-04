<?php

namespace Modules\Shop\Support;

use Illuminate\Support\Collection;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Jenssegers\Agent\Facades\Agent;
use Modules\Order\Entities\OrderStatus;
use Modules\Order\Repositories\OrderRepository;
use Modules\Shop\Contracts\ShopItemInterface;
use Modules\Shop\Contracts\ShopProductOptionInterface;
use Modules\Shop\Entities\Shop;
use Modules\Shop\Repositories\CurrencyRepository;
use Modules\Shop\Repositories\PaymentGatewayManager;
use Modules\Shop\Repositories\ShopRepository;

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
        if ($id) {
            $this->shop = Shop::find($id);
        } else {
            $this->shop = Request::route('shop');
        }

        return $this;
    }

    /**
     * Get current shop id
     * @return mixed
     */
    public function id()
    {
        return $this->shop ? $this->shop->id : 0;
    }

    /**
     * Get current shop
     * @return mixed
     */
    public function model()
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
        return $this->model() ? $this->model()->currency_code :  $this->getCurrencies()->first()->code;
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
        if (!$user) {
            return false;
        }
        $data['shop_id'] = $this->shop->id;
        $data['user_id'] = $user->id;

        $data['ip'] = Request::ip();
        $data['user_agent'] = Agent::browser();

        $data['currency_code'] = $this->shop->currency->code;
        $data['currency_value'] = $this->shop->currency->value;

        $data['status_id'] = OrderStatus::PENDING_PAYMENT;

//        items 에도 부가가치세 정보를 넣어주기 위해 foreach로 돌린다 20200904 Ho
        foreach ($items as $item){
            $item->total = $item->price;
            if($item->product['tax_free']){
                $item->tax_free = $item->price;
                $item->price = 0;
            }else{
                $totalPrice = $item->price;
                $price = floor($totalPrice/1.1);
                $tax = $totalPrice - $price;
                $item->price = $price;
                $item->tax = $tax;
            }
        }
        $data['items'] = $items;
        // Save order data
        $order = $this->order->create($data);
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
        return $this->shop->paymentGateways->flatMap(function ($gateway) {
            $result = [];
            foreach ($gateway->getAllowedPaymentMethods() as $method) {
                $result[$gateway->getId() . '|' . $method::getId()] = $method;
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
        return $this->shop->shippingGateways->mapWithKeys(function ($gateway) {
            $result = [];
            foreach ($gateway->getAllowedShippingMethods() as $method) {
                $result[$gateway->getId() . '|' . $method::getId()] = $method;
            }

            return $result;
        });
    }

    /**
     * 상품 단가 계산
     * Calculate Single Item Price
     * @param  ShopItemInterface $item
     * @return int
     */
    public function calculateUnitPrice(ShopItemInterface $item)
    {
        $salePrice = (int) $item['product']['sale_price'];

        $unitPrice = $salePrice;

        if (!empty($item['options'])) {
            foreach ($item['options'] as $option) {
                if (!$option->is_collection) {
                    continue;
                }

                if ($value = $option->values->firstWhere('code', $option->value)) {
                    $priceValue = (int) $value['price_value'];
                    if ($value['price_type'] == 'FIXED') {
                        $unitPrice += $priceValue;
                    } elseif ($value['price_type'] == 'PERCENTAGE') {
                        $unitPrice += $salePrice * ($priceValue / 100);
                        $unitPrice = round($unitPrice);
                    }
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

        //면세일경우 unitPrice는 0이되고, 아닐경우는 부가세 제외하고 적용 20200903 Ho
            $unitPrice = ($item['product']['tax_free'])? 0:$unitPrice/1.1;

            $quantity = (int) $item['quantity'];
            $totalPrice += $unitPrice * $quantity;
        }

        return floor($totalPrice);
    }
    //총 금액에서 공급가를 빼서 부가가치세를 계산 20200903 Ho

    /**
     * @param $items
     * @param $totalPrice
     * @return float|int
     */
    public function calculateTax($items){
        $totalPrice = $this->calculateTotalPrice($items);
        $total = 0;

        foreach ($items as $item) {
            if($item->product['tax_free']){
                continue;
            }
            $unitPrice = $item->price;
            $quantity = (int) $item['quantity'];
            $total += $unitPrice * $quantity;
        }
        return $totalTax = $total - $totalPrice;

    }

    public function calculateTaxFree($items){
        $totalTaxFree = 0;
        foreach ($items as $item) {
            if($item->product['tax_free']){
                $taxFree = $item->price;
                $quantity = (int) $item['quantity'];
                $totalTaxFree += $taxFree * $quantity;

            }
        }
        return $totalTaxFree;
    }
    /**
     * 상품옵션값 이름 가져오기
     * Get Product OptionValue's name
     * @param ShopProductOptionInterface $option
     * @param mixed                      $value
     * @return string
     */
    public function getOptionValueName(ShopProductOptionInterface $option, $value): string
    {
        // 없으면 기본값 string
        $value = $value ?: '';

        if (!$option['is_collection']) {
            return $value;
        }

        // If collection value, stored value is code
        foreach ($option['values'] as $v) {
            if ($v['code'] == $value) {
                return $v['name'] ?: '';
            }
        }
        return '';
    }
}
