<?php

namespace Modules\Shop\Contracts;

/**
 * 결제수단 인터페이스
 * PaymentMethod Interface
 */

interface ShopPaymentMethodInterface
{

    /**
     * ID리턴
     * Get Id
     * @return string
     */
    public static function getId();

    /**
     * 이름 리턴
     * Get Name
     * @return string
     */
    public static function getName();

    /**
     * 설명
     * Description
     */
    public static function getDescription();

}
