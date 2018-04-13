<?php

namespace Modules\Shop\Entities;

use Modules\Shop\Entities\Shop;
use Modules\Product\Entities\Product;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ShopProduct extends Pivot
{
    protected $table = 'shop__shop_product';
    protected $fillable = [
        'shop_id',
        'product_id',
    ];

    public function shop() {
        return $this->belongsTo(Shop::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

}
