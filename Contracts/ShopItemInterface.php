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
     * @return ShopProductInterface
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

    /**
     * 하위 상품을 가져옴
     * Get Children Items
     * @return array
     */
    public function getChildrenAttribute();

    /**
     * 주문용 배열로 변환
     * @param ShopItemInterface|null $parentItem  부모 주문아이템 (children일때만 넘어옴)
     * @return array
     */
    public function toOrderItemArray(ShopItemInterface $parentItem = null);


}
