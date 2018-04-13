<?php

namespace Modules\Shop\Shippings\Methods;

use Modules\Shop\Contracts\ShopShippingMethodInterface;

/**
 * 택배
 * Courier
 */
class Courier implements ShopShippingMethodInterface
{

    /**
     * @inheritDoc
     */
    public static function getId()
    {
        return 'courier';
    }

    /**
     * @inheritDoc
     */
    public static function getName()
    {
        return trans('shop::shippings.methods.courier');
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
    public static function getFee()
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return static::getId();
    }

}
