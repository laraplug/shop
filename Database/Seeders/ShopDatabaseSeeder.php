<?php

namespace Modules\Shop\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Shop\Entities\Shop;
use Modules\Shop\Entities\Currency;

use Modules\Shop\Repositories\PaymentGatewayManager;
use Modules\Shop\Repositories\ShippingGatewayManager;
use Illuminate\Database\Eloquent\Model;

use Modules\Shop\Payments\Methods\DirectBank;

use Modules\Shop\Shippings\Methods\Pickup;

class ShopDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        if(!$currency = Currency::first()) {
            $currency = Currency::create([
                'code' => 'USD',
                'value' => 1,
                'enabled' => 1,
            ]);
        }

        if(!$shop = Shop::first()) {
            $shop = Shop::create([
                'subdomain' => '',
                'name' => 'Default Shop',
                'lat' => 0,
                'lng' => 0,
                'currency_code' => $currency->code,
                'theme' => 'Flatly',
            ]);
        }

        if(!$paymentGatewayConfig = $shop->paymentGatewayConfigs()->first()) {
            $paymentGateway = app(PaymentGatewayManager::class)->find('direct');
            $paymentGatewayConfig = $shop->paymentGatewayConfigs()->create([
                'gateway_id' => $paymentGateway->getId(),
                'merchant_id' => '',
                'merchant_token' => '',
                'enabled_method_ids' => [DirectBank::getId()],
                'options' => []
            ]);
        }

        if(!$shippingGatewayConfig = $shop->shippingGatewayConfigs()->first()) {
            $shippingGateway = app(ShippingGatewayManager::class)->find('direct');
            $shippingGatewayConfig = $shop->shippingGatewayConfigs()->create([
                'gateway_id' => $shippingGateway->getId(),
                'merchant_id' => '',
                'merchant_token' => '',
                'enabled_method_ids' => [Pickup::getId()],
                'options' => []
            ]);
        }

        $this->call(SentinelGroupSeedTableSeeder::class);
    }
}
