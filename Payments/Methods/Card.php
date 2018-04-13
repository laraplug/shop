<?php

namespace Modules\Shop\Payments\Methods;

use Modules\Shop\Contracts\ShopPaymentMethodInterface;

/**
 * Card
 * 카드결제
 */
class Card implements ShopPaymentMethodInterface
{

    /**
     * @inheritDoc
     */
    public static function getId()
    {
        return 'card';
    }

    /**
     * @inheritDoc
     */
    public static function getName()
    {
        return trans('shop::payments.methods.card');
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
    public function __toString()
    {
        return static::getId();
    }

}
