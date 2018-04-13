<?php

namespace Modules\Shop\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Modules\Core\Http\Controllers\Admin\AdminBaseController;
use Modules\Shop\Entities\ShippingGatewayConfig;
use Modules\Shop\Entities\Shop;
use Modules\Shop\Http\Requests\GatewayConfigRequest;
use Modules\Shop\Repositories\ShippingGatewayManager;

class ShippingGatewayConfigController extends AdminBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param  Shop $shop
     * @return Response
     */
    public function index(Shop $shop)
    {
        $configs = $shop->shippingGatewayConfigs()->get();

        return view('shop::admin.shippinggatewayconfigs.index', compact('configs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  Shop $shop
     * @return Response
     */
    public function create(Shop $shop)
    {
        $gatewayIds = $shop->shippingGatewayConfigs()->pluck('gateway_id');
        $shippingGateways = app(ShippingGatewayManager::class)->all()->except($gatewayIds);
        $shippingMethods = $this->getShippingMethods($shippingGateways);
        return view('shop::admin.shippinggatewayconfigs.create', compact('shop', 'shippingGateways', 'shippingMethods'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Shop $shop
     * @param  GatewayConfigRequest $request
     * @return Response
     */
    public function store(Shop $shop, GatewayConfigRequest $request)
    {
        $shop->shippingGatewayConfigs()->create($request->all());

        return redirect()->route('admin.shop.shop.edit', $shop->id)
            ->withSuccess(trans('core::core.messages.resource created', ['name' => trans('shop::shippinggatewayconfigs.title.shippinggatewayconfigs')]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Shop $shop
     * @param  ShippingGatewayConfig $gatewayConfig
     * @return Response
     */
    public function edit(Shop $shop, ShippingGatewayConfig $gatewayConfig)
    {
        $shippingMethods = $gatewayConfig->gateway->getSupportedShippingMethods()->map(function ($method) {
            return [
                'value' => $method::getId(),
                'label' => $method::getName(),
            ];
        })->values();

        return view('shop::admin.shippinggatewayconfigs.edit', compact('shop', 'gatewayConfig', 'shippingMethods'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Shop $shop
     * @param  ShippingGatewayConfig $gatewayConfig
     * @param  GatewayConfigRequest $request
     * @return Response
     */
    public function update(Shop $shop, ShippingGatewayConfig $gatewayConfig, GatewayConfigRequest $request)
    {
        $gatewayConfig->fill($request->all());
        $gatewayConfig->save();

        return redirect()->route('admin.shop.shop.edit', $shop->id)
            ->withSuccess(trans('core::core.messages.resource updated', ['name' => trans('shop::shippinggatewayconfigs.title.shippinggatewayconfigs')]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Shop $shop
     * @param  ShippingGatewayConfig $gatewayConfig
     * @return Response
     */
    public function destroy(Shop $shop, ShippingGatewayConfig $gatewayConfig)
    {
        $gatewayConfig->delete();

        return redirect()->route('admin.shop.shop.edit', $shop->id)
            ->withSuccess(trans('core::core.messages.resource deleted', ['name' => trans('shop::shippinggatewayconfigs.title.shippinggatewayconfigs')]));
    }

    protected function getShippingMethods($shippingGateways)
    {
        return $shippingGateways->mapWithKeys(function ($gateway) {
            $methods = $gateway->getSupportedShippingMethods();
            $methods = collect($methods)->map(function ($method) {
                return [
                    'value' => $method::getId(),
                    'label' => $method::getName(),
                ];
            })->values();

            return [$gateway->getId() => $methods];
        });
    }
}
