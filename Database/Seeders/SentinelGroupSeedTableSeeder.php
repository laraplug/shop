<?php

namespace Modules\Shop\Database\Seeders;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class SentinelGroupSeedTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Save the permissions
        $group = Sentinel::findRoleBySlug('admin');

        $group->addPermission('shop.shops.index');
        $group->addPermission('shop.shops.create');
        $group->addPermission('shop.shops.edit');
        $group->addPermission('shop.shops.destroy');

        $group->addPermission('shop.currencies.index');
        $group->addPermission('shop.currencies.create');
        $group->addPermission('shop.currencies.edit');
        $group->addPermission('shop.currencies.destroy');

        $group->addPermission('shop.paymentgatewayconfigs.index');
        $group->addPermission('shop.paymentgatewayconfigs.create');
        $group->addPermission('shop.paymentgatewayconfigs.edit');
        $group->addPermission('shop.paymentgatewayconfigs.destroy');

        $group->addPermission('shop.shippinggatewayconfigs.index');
        $group->addPermission('shop.shippinggatewayconfigs.create');
        $group->addPermission('shop.shippinggatewayconfigs.edit');
        $group->addPermission('shop.shippinggatewayconfigs.destroy');

        $group->save();
    }
}
