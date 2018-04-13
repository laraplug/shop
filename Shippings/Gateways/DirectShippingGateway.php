<?php

namespace Modules\Shop\Shippings\Gateways;

use Modules\Order\Entities\OrderStatus;

use Modules\Shop\Shippings\Methods\Pickup;

/**
 * Direct Shipping Gateway
 * 직접배송 게이트웨이
 */

class DirectShippingGateway extends ShippingGateway
{

    /**
     * @var int
     */
    protected $initialStatusId = OrderStatus::PROCESSING;

    protected $options = [
        'pickup_fee' => 0,
    ];

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return 'direct';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return trans('shop::shippings.gateways.direct');
    }

    /**
     * 지원되는 배송타입 설정
     * @var int
     */
    protected $supportedShippingMethods = [
        Pickup::class
    ];

    /**
     * @inheritDoc
     */
    public function getFee($method)
    {
        if($method == Pickup::class) {
            return $this->getOptionValue('pickup_fee');
        }
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getOptionName($key)
    {
        return trans("shop::shippings.options.$key");
    }

    /**
     * @inheritDoc
     */
    public function prepareShipping($submitUrl)
    {
        return false;
    }

}
