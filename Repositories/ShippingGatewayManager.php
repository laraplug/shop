<?php

namespace Modules\Shop\Repositories;

use Modules\Shop\Shippings\Gateways\ShippingGateway;

/**
 * Manager for various payment gateways
 */
class ShippingGatewayManager
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

    public function register(ShippingGateway $entity)
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
