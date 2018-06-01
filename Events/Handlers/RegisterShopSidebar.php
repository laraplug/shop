<?php

namespace Modules\Shop\Events\Handlers;

use Maatwebsite\Sidebar\Group;
use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Modules\Core\Events\BuildingSidebar;
use Modules\User\Contracts\Authentication;

class RegisterShopSidebar implements \Maatwebsite\Sidebar\SidebarExtender
{
    /**
     * @var Authentication
     */
    protected $auth;

    /**
     * @param Authentication $auth
     *
     * @internal param Guard $guard
     */
    public function __construct(Authentication $auth)
    {
        $this->auth = $auth;
    }

    public function handle(BuildingSidebar $sidebar)
    {
        $sidebar->add($this->extendWith($sidebar->getMenu()));
    }

    /**
     * @param Menu $menu
     * @return Menu
     */
    public function extendWith(Menu $menu)
    {
        $menu->group(trans('shop::shops.title.shops'), function (Group $group) {
            $group->weight(config('asgard.shop.config.sidebar-weight'));
            $group->item(trans('shop::shops.title.shops'), function (Item $item) {
                $item->icon('fa fa-shopping-bag');
                $item->weight(30);
                $item->route('admin.shop.shop.index');
                $item->authorize(
                    $this->auth->hasAccess('shop.shops.index')
                );
            });
            $group->item(trans('shop::currencies.title.currencies'), function (Item $item) {
                $item->icon('fa fa-money');
                $item->weight(30);
                $item->route('admin.shop.currency.index');
                $item->authorize(
                    $this->auth->hasAccess('shop.currencies.index')
                );
            });
// append


        });

        return $menu;
    }
}
