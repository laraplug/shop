<?php

namespace Modules\Shop\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Shop\Entities\Shop;
use Modules\Core\Foundation\Theme\ThemeManager;
use Modules\Shop\Http\Requests\CreateShopRequest;
use Modules\Shop\Http\Requests\UpdateShopRequest;
use Modules\Shop\Repositories\ShopRepository;
use Modules\Core\Http\Controllers\Admin\AdminBaseController;

class ShopController extends AdminBaseController
{
    /**
     * @var ShopRepository
     */
    private $shop;

    /**
     * @var ThemeManager
     */
    private $themeManager;

    public function __construct(ShopRepository $shop, ThemeManager $themeManager)
    {
        parent::__construct();

        $this->shop = $shop;
        $this->themeManager = $themeManager;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $shops = $this->shop->all();

        return view('shop::admin.shops.index', compact('shops'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $themes = $this->themeManager->allPublicThemes();
        return view('shop::admin.shops.create', compact('themes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateShopRequest $request
     * @return Response
     */
    public function store(CreateShopRequest $request)
    {
        $this->shop->create($request->all());

        return redirect()->route('admin.shop.shop.index')
            ->withSuccess(trans('core::core.messages.resource created', ['name' => trans('shop::shops.title.shops')]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Shop $shop
     * @return Response
     */
    public function edit(Shop $shop)
    {
        $themes = $this->themeManager->allPublicThemes();
        return view('shop::admin.shops.edit', compact('shop', 'themes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Shop $shop
     * @param  UpdateShopRequest $request
     * @return Response
     */
    public function update(Shop $shop, UpdateShopRequest $request)
    {
        $this->shop->update($shop, $request->all());

        return redirect()->route('admin.shop.shop.index')
            ->withSuccess(trans('core::core.messages.resource updated', ['name' => trans('shop::shops.title.shops')]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Shop $shop
     * @return Response
     */
    public function destroy(Shop $shop)
    {
        $this->shop->destroy($shop);

        return redirect()->route('admin.shop.shop.index')
            ->withSuccess(trans('core::core.messages.resource deleted', ['name' => trans('shop::shops.title.shops')]));
    }
}
