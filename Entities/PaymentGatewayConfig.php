<?php

namespace Modules\Shop\Entities;

use Modules\Shop\Repositories\PaymentMethodManager;
use Modules\Shop\Repositories\PaymentGatewayManager;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewayConfig extends Model
{

    protected $table = 'shop__payment_gateway_configs';
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
        if($gateway = app(PaymentGatewayManager::class)->find($this->gateway_id)) {
            $gateway->setConfig($this);
        }
        return $gateway;
    }

    public function getEnabledMethodsAttribute()
    {
        $methods = $this->enabled_method_ids;
        return collect($methods)->map(function($methodId) {
            return app(PaymentMethodManager::class)->find($methodId);
        });
    }
}
