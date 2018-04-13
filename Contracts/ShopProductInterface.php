<?php

namespace Modules\Shop\Contracts;

/**
 * 상품 인터페이스
 * Item Interface for Order and Cart
 */
interface ShopProductInterface
{

    /**
     * Returns model key
     * @return int
     */
    public function getKey();

}
