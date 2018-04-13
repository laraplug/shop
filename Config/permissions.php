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
    'shop.gatewayconfigs' => [
        'index' => 'shop::gatewayconfigs.list resource',
        'create' => 'shop::gatewayconfigs.create resource',
        'edit' => 'shop::gatewayconfigs.edit resource',
        'destroy' => 'shop::gatewayconfigs.destroy resource',
    ],
// append



];
