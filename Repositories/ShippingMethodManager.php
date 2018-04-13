<?php

namespace Modules\Shop\Repositories;

use Modules\Shop\Contracts\ShopShippingMethodInterface;

/**
 * Manager for various shipping methods
 */
class ShippingMethodManager
{
    /**
     * Array of registered entities.
     * @var array
     */
    private $entities = [];

    public function all()
    {
        return collect($this->entities);
    }

    public function register(ShopShippingMethodInterface $entity)
    {
        $this->entities[$entity->getId()] = $entity;
    }

    public function find(string $id)
    {
        return array_get($this->entities, $id, null);
    }

    public function first()
    {
        return collect($this->entities)->first();
    }

}
