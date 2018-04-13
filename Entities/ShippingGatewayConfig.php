<?php

namespace Modules\Shop\Entities;

use Modules\Shop\Repositories\ShippingMethodManager;
use Modules\Shop\Repositories\ShippingGatewayManager;

use Illuminate\Database\Eloquent\Model;

class ShippingGatewayConfig extends Model
{

    protected $table = 'shop__shipping_gateway_configs';
    protected $fillable = [
        'gateway_id',
        'merchant_id',
        'merchant_token',
        'enabled_method_ids',
        'options',
    ];
    protected $casts = [
        'enabled_method_ids' => 'array',
        'options' => 'array',
    ];

    public function getOptionsAttribute($value)
    {
        return $value ? json_decode($value) : [];
    }

    public function getGatewayAttribute()
    {
        if($gateway = app(ShippingGatewayManager::class)->find($this->gateway_id)) {
            $gateway->setConfig($this);
        }
        return $gateway;
    }

    public function getEnabledMethodsAttribute()
    {
        $methods = $this->enabled_method_ids;
        return collect($methods)->map(function($methodId) {
            return app(ShippingMethodManager::class)->find($methodId);
        });
    }
}
