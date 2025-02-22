<?php

namespace Modules\Shop\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Events\BuildingSidebar;
use Modules\Core\Events\LoadingBackendTranslations;
use Modules\Core\Traits\CanPublishConfiguration;
use Modules\Media\Image\ThumbnailManager;
use Modules\Shop\Events\Handlers\RegisterShopSidebar;
use Modules\Shop\Http\Middleware\ShopDomainResolver;
use Modules\Shop\Payments\Gateways\DirectPayGateway;
use Modules\Shop\Payments\Gateways\NicepayGateway;
use Modules\Shop\Payments\Methods\Card;
use Modules\Shop\Payments\Methods\DirectBank;
use Modules\Shop\Repositories\PaymentGatewayManager;
use Modules\Shop\Repositories\PaymentMethodManager;
use Modules\Shop\Repositories\ShippingGatewayManager;
use Modules\Shop\Repositories\ShippingMethodManager;
use Modules\Shop\Shippings\Gateways\DirectShippingGateway;
use Modules\Shop\Shippings\Methods\Pickup;
use Modules\Shop\Support\CategoryHelper;
use Modules\Shop\Support\CurrencyHelper;
use Modules\Shop\Support\ProductHelper;
use Modules\Shop\Support\ShopHelper;

class ShopServiceProvider extends ServiceProvider
{
    use CanPublishConfiguration;
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * @var array
     */
    protected $middleware = [
        'domain.shop' => ShopDomainResolver::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings();
        $this->app['events']->listen(BuildingSidebar::class, RegisterShopSidebar::class);

        $this->app['events']->listen(LoadingBackendTranslations::class, function (LoadingBackendTranslations $event) {
            $event->load('shops', array_dot(trans('shop::shops')));
            $event->load('currencies', array_dot(trans('shop::currencies')));
            // append translations
        });
    }

    public function boot()
    {
        $this->registerMiddleware();
        $this->registerThumbnails();

        $this->publishConfig('shop', 'config');
        $this->publishConfig('shop', 'permissions');

        // Set sidebar-group of attribute as shop
        config()->set('asgard.attribute.config.sidebar-group', trans('shop::shops.title.shops'));

        // Set domain.shop middleware on every page
        config()->set('asgard.page.config.middleware', 'domain.shop');

        // Register Payment Gateways
        $this->app[PaymentGatewayManager::class]->register(new DirectPayGateway());
        $this->app[PaymentGatewayManager::class]->register(new NicepayGateway());

        // Register Payment Methods
        $this->app[PaymentMethodManager::class]->register(new DirectBank());
        $this->app[PaymentMethodManager::class]->register(new Card());

        // Register Shipping Gateways
        $this->app[ShippingGatewayManager::class]->register(new DirectShippingGateway());

        // Register Shipping Methods
        $this->app[ShippingMethodManager::class]->register(new Pickup());

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

    private function registerMiddleware()
    {
        foreach ($this->middleware as $name => $class) {
            $this->app['router']->aliasMiddleware($name, $class);
        }
    }

    private function registerBindings()
    {
        $this->app->singleton('shop', function ($app) {
            return new ShopHelper($app['Modules\Shop\Repositories\CurrencyRepository'], $app['Modules\Order\Repositories\OrderRepository'], $app[PaymentGatewayManager::class]);
        });
        $this->app->singleton('shop.product', function ($app) {
            return new ProductHelper(
                $app['shop'],
                $app['Modules\Product\Repositories\CategoryRepository']
            );
        });
        $this->app->singleton('shop.category', function ($app) {
            return new CategoryHelper($app['shop'], $app['Modules\Product\Repositories\CategoryRepository']);
        });
        $this->app->singleton('currency', function ($app) {
            return new CurrencyHelper();
        });
        $this->app->singleton(PaymentGatewayManager::class, function () {
            return new PaymentGatewayManager();
        });
        $this->app->singleton(PaymentMethodManager::class, function () {
            return new PaymentMethodManager();
        });
        $this->app->singleton(ShippingGatewayManager::class, function () {
            return new ShippingGatewayManager();
        });
        $this->app->singleton(ShippingMethodManager::class, function () {
            return new ShippingMethodManager();
        });
        $this->app->bind(
            'Modules\Shop\Repositories\ShopRepository',
            function () {
                $repository = new \Modules\Shop\Repositories\Eloquent\EloquentShopRepository(new \Modules\Shop\Entities\Shop());

                if (! config('app.cache')) {
                    return $repository;
                }

                return new \Modules\Shop\Repositories\Cache\CacheShopDecorator($repository);
            }
        );
        $this->app->bind(
            'Modules\Shop\Repositories\CurrencyRepository',
            function () {
                $repository = new \Modules\Shop\Repositories\Eloquent\EloquentCurrencyRepository(new \Modules\Shop\Entities\Currency());

                if (! config('app.cache')) {
                    return $repository;
                }

                return new \Modules\Shop\Repositories\Cache\CacheCurrencyDecorator($repository);
            }
        );

        // add bindings
    }

    private function registerThumbnails()
    {
//        $this->app[ThumbnailManager::class]->registerThumbnail('largeThumb', [
//            'resize' => [
//                'width' => null,
//                'height' => 900,
//                'callback' => function ($constraint) {
//                    $constraint->aspectRatio();
//                    $constraint->upsize();
//                },
//            ],
//        ]);

    }
}
