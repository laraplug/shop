<?php

namespace Modules\Shop\Facades;

use Illuminate\Support\Facades\Facade;

class Shop extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'shop';
    }
}
