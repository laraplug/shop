<?php

namespace Modules\Shop\Contracts;

/**
 * 배송수단 인터페이스
 * ShippingMethod Interface
 */

interface ShopShippingMethodInterface
{

    /**
     * @param int $fee
     */
    public function __construct(int $fee = 0);

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

    /**
     * 금액 리턴
     * Get Fee
     * @return int
     */
    public function getFee();

}
