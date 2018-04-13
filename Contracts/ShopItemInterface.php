<?php

namespace Modules\Shop\Contracts;

/**
 * 장바구니 & 주문 아이템 인터페이스
 * Cart & Order Item interface
 */

interface ShopItemInterface
{

    /**
     * Returns product
     *
     * @return mixed
     */
    public function getProductAttribute();

    /**
     * Returns price
     *
     * @return string
     */
    public function getPriceAttribute();

    /**
     * Returns quantity
     *
     * @return string
     */
    public function getQuantityAttribute();

    /**
     * Returns selected options.
     *
     * @return mixed
     */
    public function getOptionsAttribute();

    /**
     * Returns total
     *
     * @return string
     */
    public function getTotalAttribute();

}
