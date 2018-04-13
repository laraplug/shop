<?php

namespace Modules\Shop\Contracts;

/**
 * Interface for Product Options
 */
interface ShopProductOptionInterface
{

    /**
     * Is collection
     * @return int [description]
     */
    public function getIsCollectionAttribute(): int;

    /**
     * Is system
     */
    public function getIsSystemAttribute(): int;

}
