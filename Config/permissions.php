<?php

return [
    'shop.shops' => [
        'index' => 'shop::shops.list resource',
        'create' => 'shop::shops.create resource',
        'edit' => 'shop::shops.edit resource',
        'destroy' => 'shop::shops.destroy resource',
    ],
    'shop.currencies' => [
        'index' => 'shop::currencies.list resource',
        'create' => 'shop::currencies.create resource',
        'edit' => 'shop::currencies.edit resource',
        'destroy' => 'shop::currencies.destroy resource',
    ],
    'shop.paymentgatewayconfigs' => [
        'index' => 'shop::paymentgatewayconfigs.list resource',
        'create' => 'shop::paymentgatewayconfigs.create resource',
        'edit' => 'shop::paymentgatewayconfigs.edit resource',
        'destroy' => 'shop::paymentgatewayconfigs.destroy resource',
    ],
    'shop.shippinggatewayconfigs' => [
        'index' => 'shop::shippinggatewayconfigs.list resource',
        'create' => 'shop::shippinggatewayconfigs.create resource',
        'edit' => 'shop::shippinggatewayconfigs.edit resource',
        'destroy' => 'shop::shippinggatewayconfigs.destroy resource',
    ],
// append



];
