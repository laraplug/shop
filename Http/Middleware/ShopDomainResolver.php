<?php

namespace Modules\Shop\Http\Middleware;

use Closure;
use Modules\Shop\Repositories\ShopRepository;

class ShopDomainResolver
{

    /**
     * @var ShopRepository
     */
    private $shop;

    /**
     * @param ShopRepository $shop
     */
    public function __construct(ShopRepository $shop)
    {
        $this->shop = $shop;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();
        if(app()->environment('local')) {
            $host = str_replace('local.', '', $host);
        }
        $segments = explode('.', $host);
        $subdomain = count($segments) > 2 ? array_first(explode('.', $host)) : '';
        $shop = $this->shop->findBySubdomain($subdomain);
        if(!$shop) {
            abort(404);
        }

        $request->route()->setParameter('shop', $shop);

        // Enable shop theme
        app('stylist')->activate($shop->theme, true);

        return $next($request);
    }
}
