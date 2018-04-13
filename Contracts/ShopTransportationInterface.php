<?php

namespace Modules\Shop\Contracts;

/**
 * 배송내역 인터페이스
 * Transportation Interface
 */

interface ShopTransportationInterface
{
    /**
     * One-to-One relations with the order model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order();

}
