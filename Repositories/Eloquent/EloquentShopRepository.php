<?php

namespace Modules\Shop\Repositories\Eloquent;

use Modules\Shop\Repositories\ShopRepository;
use Modules\Core\Repositories\Eloquent\EloquentBaseRepository;

class EloquentShopRepository extends EloquentBaseRepository implements ShopRepository
{

    /**
     * @inheritDoc
     */
    public function findBySubdomain($subdomain)
    {
        return $this->allWithBuilder()->where('subdomain', $subdomain)->first();
    }
}
