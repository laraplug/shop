<?php

namespace Modules\Shop\Facades;

use Illuminate\Support\Facades\Facade;

class Category extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'shop.category';
    }
}
