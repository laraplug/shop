<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => '/shop', 'middleware' => ['bindings']], function (Router $router) {
    # Login
    $router->post('login', [
        'as' => 'api.shop.auth.login',
        'uses' => 'AuthController@postLogin'
    ]);

    $router->group(['middleware' => ['api.token']], function (Router $router) {

        $router->get('user', [
            'as' => 'api.shop.auth.get',
            'uses' => 'AuthController@getUser'
        ]);

    });

});
