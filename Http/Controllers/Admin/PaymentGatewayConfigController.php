<?php

namespace Modules\Shop\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Modules\Core\Http\Controllers\Admin\AdminBaseController;
use Modules\Shop\Entities\PaymentGatewayConfig;
use Modules\Shop\Entities\Shop;
use Modules\Shop\Http\Requests\GatewayConfigRequest;
use Modules\Shop\Repositories\PaymentGatewayManager;

class PaymentGatewayConfigController extends AdminBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param  Shop $shop
     * @return Response
     */
    public function index(Shop $shop)
    {
        $configs = $shop->paymentGatewayConfigs()->get();

        return view('shop::admin.paymentgatewayconfigs.index', compact('configs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  Shop $shop
     * @return Response
     */
    public function create(Shop $shop)
    {
        $gatewayIds = $shop->paymentGatewayConfigs()->pluck('gateway_id');
        $paymentGateways = app(PaymentGatewayManager::class)->all()->except($gatewayIds);
        $paymentMethods = $this->getPaymentMethods($paymentGateways);

        return view('shop::admin.paymentgatewayconfigs.create', compact('shop', 'paymentGateways', 'paymentMethods'));
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
        $shop->paymentGatewayConfigs()->create($request->all());

        return redirect()->route('admin.shop.shop.edit', $shop->id)
            ->withSuccess(trans('core::core.messages.resource created', ['name' => trans('shop::paymentgatewayconfigs.title.paymentgatewayconfigs')]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Shop $shop
     * @param  PaymentGatewayConfig $gatewayConfig
     * @return Response
     */
    public function edit(Shop $shop, PaymentGatewayConfig $gatewayConfig)
    {
        $paymentMethods = $gatewayConfig->gateway->getSupportedPaymentMethods()->map(function ($method) {
            return [
                'value' => $method::getId(),
                'label' => $method::getName(),
            ];
        });
        $gatewayOptions = $gatewayConfig->gateway->getOptions();

        return view('shop::admin.paymentgatewayconfigs.edit', compact('shop', 'gatewayConfig', 'paymentMethods', 'gatewayOptions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Shop $shop
     * @param  PaymentGatewayConfig $gatewayConfig
     * @param  GatewayConfigRequest $request
     * @return Response
     */
    public function update(Shop $shop, PaymentGatewayConfig $gatewayConfig, GatewayConfigRequest $request)
    {
        $gatewayConfig->fill($request->all());
        $gatewayConfig->save();

        return redirect()->route('admin.shop.shop.edit', $shop->id)
            ->withSuccess(trans('core::core.messages.resource updated', ['name' => trans('shop::paymentgatewayconfigs.title.paymentgatewayconfigs')]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Shop $shop
     * @param  PaymentGatewayConfig $gatewayConfig
     * @return Response
     */
    public function destroy(Shop $shop, PaymentGatewayConfig $gatewayConfig)
    {
        $gatewayConfig->delete();

        return redirect()->route('admin.shop.shop.edit', $shop->id)
            ->withSuccess(trans('core::core.messages.resource deleted', ['name' => trans('shop::paymentgatewayconfigs.title.paymentgatewayconfigs')]));
    }

    protected function getPaymentMethods($paymentGateways)
    {
        return $paymentGateways->mapWithKeys(function ($gateway) {
            $methods = $gateway->getAllowedPaymentMethods();
            $methods = collect($methods)->map(function ($method) {
                return [
                    'value' => $method::getId(),
                    'label' => $method::getName(),
                ];
            });

            return [$gateway->getId() => $methods];
        });
    }
}
