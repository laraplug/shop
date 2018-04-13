<?php

namespace Modules\Shop\Repositories;

use Modules\Shop\Payments\Gateways\PaymentGateway;

/**
 * Manager for various payment gateways
 */
class PaymentGatewayManager
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

    public function register(PaymentGateway $entity)
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
