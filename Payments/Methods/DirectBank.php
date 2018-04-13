<?php

namespace Modules\Shop\Payments\Methods;

use Modules\Shop\Contracts\ShopPaymentMethodInterface;

/**
 * DirectBank
 * 무통장입금
 */
class DirectBank implements ShopPaymentMethodInterface
{
    /**
     * @inheritDoc
     */
    public static function getId()
    {
        return 'direct_bank';
    }

    /**
     * @inheritDoc
     */
    public static function getName()
    {
        return trans('shop::payments.methods.direct_bank');
    }

    /**
     * @inheritDoc
     */
    public static function getDescription()
    {
        return trans('shop::payments.messages.direct bank terms');
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return static::getId();
    }

}
