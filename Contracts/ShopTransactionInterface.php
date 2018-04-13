<?php

namespace Modules\Shop\Contracts;

/**
 * 결제내역 인터페이스
 * Transaction Interface
 */

interface ShopTransactionInterface
{
    /**
     * One-to-One relations with the order model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order();

}
