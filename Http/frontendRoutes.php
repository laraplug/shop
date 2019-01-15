<?php

use Illuminate\Routing\Router;
/** @var Router $router */

$router->group(['middleware' => ['domain.shop']], function($router) {

    $router->group(['prefix' =>'/category'], function (Router $router) {
        $router->bind('slugs', function ($slugs) {
            return app('Modules\Product\Repositories\CategoryRepository')->getBySlugs(explode('/', $slugs));
        });
        $router->get('{slugs}', [
            'as' => 'shop.product.category',
            'uses' => 'ProductController@category'
        ])->where('slugs', '(.*)');
    });

    $router->get('/search', [
        'as' => 'shop.product.search',
        'uses' => 'PublicController@index'
    ]);

    $router->get('/detail/{product}', [
        'as' => 'shop.product.detail',
        'uses' => 'ProductController@detail'
    ]);

    $router->get('/article', [
        'as' => 'shop.article.index',
        'uses' => 'ArticleController@articles'
    ]);

    $router->get('/article/{article}', [
        'as' => 'shop.article.view',
        'uses' => 'ArticleController@articleView'
    ]);

    // Cart View
    $router->get('/cart', [
        'as' => 'shop.cart',
        'uses' => 'CartController@view'
    ]);

    $router->group(['middleware' => ['logged.in']], function (Router $router) {
        $router->bind('order', function ($id) {
            return app('Modules\Order\Repositories\OrderRepository')->find($id);
        });
        // 주문내용 뷰
        // Checkout View (Order Prepare)
        $router->get('/checkout/cart', [
            'as' => 'shop.checkout.cart.view',
            'uses' => 'OrderController@createFromCart'
        ]);

        // 주문저장
        // Order Save
        $router->post('/checkout', [
            'as' => 'shop.checkout.cart.store',
            'uses' => 'OrderController@storeCart'
        ]);

        // 결제화면
        // Pay View
        $router->get('/orders/{order}/pay', [
            'as' => 'shop.order.pay.view',
            'uses' => 'OrderController@payForm'
        ]);

        // 결제처리
        // Pay Processing
        $router->post('/orders/{order}/pay', [
            'as' => 'shop.order.pay.store',
            'uses' => 'OrderController@pay'
        ]);

        // 결제처리
        // Pay Processing
        $router->post('/orders/{order}/cancel', [
            'as' => 'shop.order.pay.cancel',
            'uses' => 'OrderController@cancel'
        ]);

        // 마이페이지 - 대시보드
        // My Page
        $router->get('/my/dashboard', [
            'as' => 'shop.my',
            'uses' => 'MyController@dashboard'
        ]);

        // 마이페이지 - 주문목록
        // Order Index
        $router->get('/my/orders', [
            'as' => 'shop.my.order.index',
            'uses' => 'MyController@orders'
        ]);

        // 마이페이지 - 주문상세
        // Order View
        $router->get('/my/orders/{order}', [
            'as' => 'shop.my.order.view',
            'uses' => 'MyController@orderView'
        ]);

        // 마이페이지 - 주문 거래명세서
        // Order Form
        $router->get('/my/orders/form/{order}', [
            'as' => 'shop.my.order.form',
            'uses' => 'MyController@orderForm'
        ]);

        // 프로필
        // Profile
        $router->get('/my/profile', [
            'as' => 'shop.my.profile',
            'uses' => 'MyController@profile'
        ]);

        $router->post('/my/profile', [
            'as' => 'shop.my.profile.store',
            'uses' => 'MyController@profileStore'
        ]);

    });

});
