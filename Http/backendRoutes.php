<?php

use Illuminate\Routing\Router;

use Modules\Shop\Entities\PaymentGatewayConfig;
use Modules\Shop\Entities\ShippingGatewayConfig;

/** @var Router $router */

$router->group(['prefix' =>'/shop'], function (Router $router) {
    $router->bind('shop', function ($id) {
        return app('Modules\Shop\Repositories\ShopRepository')->find($id);
    });
    $router->get('shops', [
        'as' => 'admin.shop.shop.index',
        'uses' => 'ShopController@index',
        'middleware' => 'can:shop.shops.index'
    ]);
    $router->get('shops/create', [
        'as' => 'admin.shop.shop.create',
        'uses' => 'ShopController@create',
        'middleware' => 'can:shop.shops.create'
    ]);
    $router->post('shops', [
        'as' => 'admin.shop.shop.store',
        'uses' => 'ShopController@store',
        'middleware' => 'can:shop.shops.create'
    ]);
    $router->get('shops/{shop}/edit', [
        'as' => 'admin.shop.shop.edit',
        'uses' => 'ShopController@edit',
        'middleware' => 'can:shop.shops.edit'
    ]);
    $router->put('shops/{shop}', [
        'as' => 'admin.shop.shop.update',
        'uses' => 'ShopController@update',
        'middleware' => 'can:shop.shops.edit'
    ]);
    $router->delete('shops/{shop}', [
        'as' => 'admin.shop.shop.destroy',
        'uses' => 'ShopController@destroy',
        'middleware' => 'can:shop.shops.destroy'
    ]);

    $router->group(['prefix' =>'/shops/{shop}'], function (Router $router) {
        $router->bind('paymentgatewayconfig', function ($id) {
            return PaymentGatewayConfig::find($id);
        });
        $router->get('paymentgatewayconfigs/create', [
            'as' => 'admin.shop.paymentgatewayconfig.create',
            'uses' => 'PaymentGatewayConfigController@create',
            'middleware' => 'can:shop.paymentgatewayconfigs.create'
        ]);
        $router->post('paymentgatewayconfigs', [
            'as' => 'admin.shop.paymentgatewayconfig.store',
            'uses' => 'PaymentGatewayConfigController@store',
            'middleware' => 'can:shop.paymentgatewayconfigs.create'
        ]);
        $router->get('paymentgatewayconfigs/{paymentgatewayconfig}/edit', [
            'as' => 'admin.shop.paymentgatewayconfig.edit',
            'uses' => 'PaymentGatewayConfigController@edit',
            'middleware' => 'can:shop.paymentgatewayconfigs.edit'
        ]);
        $router->post('paymentgatewayconfigs/{paymentgatewayconfig}', [
            'as' => 'admin.shop.paymentgatewayconfig.update',
            'uses' => 'PaymentGatewayConfigController@update',
            'middleware' => 'can:shop.paymentgatewayconfigs.edit'
        ]);
        $router->delete('paymentgatewayconfigs/{paymentgatewayconfig}', [
            'as' => 'admin.shop.paymentgatewayconfig.destroy',
            'uses' => 'PaymentGatewayConfigController@destroy',
            'middleware' => 'can:shop.paymentgatewayconfigs.destroy'
        ]);

        $router->bind('shippinggatewayconfig', function ($id) {
            return ShippingGatewayConfig::find($id);
        });
        $router->get('shippinggatewayconfigs/create', [
            'as' => 'admin.shop.shippinggatewayconfig.create',
            'uses' => 'ShippingGatewayConfigController@create',
            'middleware' => 'can:shop.shippinggatewayconfigs.create'
        ]);
        $router->post('shippinggatewayconfigs', [
            'as' => 'admin.shop.shippinggatewayconfig.store',
            'uses' => 'ShippingGatewayConfigController@store',
            'middleware' => 'can:shop.shippinggatewayconfigs.create'
        ]);
        $router->get('shippinggatewayconfigs/{shippinggatewayconfig}/edit', [
            'as' => 'admin.shop.shippinggatewayconfig.edit',
            'uses' => 'ShippingGatewayConfigController@edit',
            'middleware' => 'can:shop.shippinggatewayconfigs.edit'
        ]);
        $router->post('shippinggatewayconfigs/{shippinggatewayconfig}', [
            'as' => 'admin.shop.shippinggatewayconfig.update',
            'uses' => 'ShippingGatewayConfigController@update',
            'middleware' => 'can:shop.shippinggatewayconfigs.edit'
        ]);
        $router->delete('shippinggatewayconfigs/{shippinggatewayconfig}', [
            'as' => 'admin.shop.shippinggatewayconfig.destroy',
            'uses' => 'ShippingGatewayConfigController@destroy',
            'middleware' => 'can:shop.paymentgatewayconfigs.destroy'
        ]);

    });

    $router->bind('currency', function ($id) {
        return app('Modules\Shop\Repositories\CurrencyRepository')->find($id);
    });
    $router->get('currencies', [
        'as' => 'admin.shop.currency.index',
        'uses' => 'CurrencyController@index',
        'middleware' => 'can:shop.currencies.index'
    ]);
    $router->get('currencies/create', [
        'as' => 'admin.shop.currency.create',
        'uses' => 'CurrencyController@create',
        'middleware' => 'can:shop.currencies.create'
    ]);
    $router->post('currencies', [
        'as' => 'admin.shop.currency.store',
        'uses' => 'CurrencyController@store',
        'middleware' => 'can:shop.currencies.create'
    ]);
    $router->get('currencies/{currency}/edit', [
        'as' => 'admin.shop.currency.edit',
        'uses' => 'CurrencyController@edit',
        'middleware' => 'can:shop.currencies.edit'
    ]);
    $router->put('currencies/{currency}', [
        'as' => 'admin.shop.currency.update',
        'uses' => 'CurrencyController@update',
        'middleware' => 'can:shop.currencies.edit'
    ]);
    $router->delete('currencies/{currency}', [
        'as' => 'admin.shop.currency.destroy',
        'uses' => 'CurrencyController@destroy',
        'middleware' => 'can:shop.currencies.destroy'
    ]);
// append



});
