<?php

namespace Modules\Shop\Repositories\Cache;

use Modules\Shop\Repositories\ShopRepository;
use Modules\Core\Repositories\Cache\BaseCacheDecorator;

class CacheShopDecorator extends BaseCacheDecorator implements ShopRepository
{
    public function __construct(ShopRepository $shop)
    {
        parent::__construct();
        $this->entityName = 'shop.shops';
        $this->repository = $shop;
    }
}
