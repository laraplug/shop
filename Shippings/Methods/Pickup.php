<?php

namespace Modules\Shop\Shippings\Methods;

use Modules\Shop\Contracts\ShopShippingMethodInterface;

/**
 * 픽업배송
 * Pickup
 */
class Pickup implements ShopShippingMethodInterface
{

    protected $fee;

    /**
     * @param int $fee
     */
    public function __construct(int $fee = 0)
    {
        $this->fee = $fee;
    }


    /**
     * @inheritDoc
     */
    public static function getId()
    {
        return 'pickup';
    }

    /**
     * @inheritDoc
     */
    public static function getName()
    {
        return trans('shop::shippings.methods.pickup');
    }

    /**
     * @inheritDoc
     */
    public static function getDescription()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return static::getId();
    }

}
