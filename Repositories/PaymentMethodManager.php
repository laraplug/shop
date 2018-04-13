<?php

namespace Modules\Shop\Repositories;

use Modules\Shop\Contracts\ShopPaymentMethodInterface;

use Modules\Shop\Payments\Gateways\PaymentGateway;

/**
 * Manager for various payment methods
 */
class PaymentMethodManager
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

    public function register(ShopPaymentMethodInterface $entity)
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
