<?php

namespace Modules\Shop\Repositories;

use Modules\Core\Repositories\BaseRepository;

interface ShopRepository extends BaseRepository
{

    /**
     * Find By Subdomain
     * @param  string $subdomain
     * @return \Modules\Shop\Entities\Shop
     */
    public function findBySubdomain($subdomain);

}
