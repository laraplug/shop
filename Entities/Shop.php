<?php

namespace Modules\Shop\Entities;

use Modules\Shop\Repositories\PaymentGatewayManager;
use Modules\Shop\Repositories\ShippingGatewayManager;

use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{

    protected $table = 'shop__shops';
    protected $fillable = [
        'subdomain',
        'name',
        'description',
        'company_name',
        'owner_name',
        'email',
        'postcode',
        'address',
        'address_detail',
        'phone',
        'fax',
        'lat',
        'lng',
        'currency_code',
        'theme',
    ];

    public function currency()
    {
        return $this->hasOne(Currency::class, 'code', 'currency_code');
    }

    /**
     * Get Products belongs to Shop
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function products()
    {
        $pivotTable = (new ShopProduct)->getTable();
        return $this->belongsToMany(Product::class, $pivotTable);
    }

    /**
     * GatewayConfig Relation
     * @return mixed
     */
    public function paymentGatewayConfigs()
    {
        return $this->hasMany(PaymentGatewayConfig::class);
    }

    public function getPaymentGatewaysAttribute()
    {
        return $this->paymentGatewayConfigs->map(function($config) {
            $gateway = app(PaymentGatewayManager::class)->find($config->gateway_id);
            if($gateway) $gateway->setConfig($config);
            return $gateway;
        })->filter();
    }

    /**
     * GatewayConfig Relation
     * @return mixed
     */
    public function shippingGatewayConfigs()
    {
        return $this->hasMany(ShippingGatewayConfig::class);
    }

    public function getShippingGatewaysAttribute()
    {
        return $this->shippingGatewayConfigs->map(function($config) {
            $gateway = app(ShippingGatewayManager::class)->find($config->gateway_id);
            if($gateway) $gateway->setConfig($config);
            return $gateway;
        })->filter();
    }

    public function getDomainAttribute()
    {
        $baseDomain = config('session.domain');
        return ltrim($this->subdomain.$baseDomain, '.');
    }

}
