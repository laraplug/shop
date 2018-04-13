<?php

namespace Modules\Shop\Repositories\Cache;

use Modules\Shop\Repositories\CurrencyRepository;
use Modules\Core\Repositories\Cache\BaseCacheDecorator;

class CacheCurrencyDecorator extends BaseCacheDecorator implements CurrencyRepository
{
    public function __construct(CurrencyRepository $currency)
    {
        parent::__construct();
        $this->entityName = 'shop.currencies';
        $this->repository = $currency;
    }
}
