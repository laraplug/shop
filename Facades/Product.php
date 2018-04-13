<?php

namespace Modules\Shop\Facades;

use Illuminate\Support\Facades\Facade;

class Product extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'shop.product';
    }
}
