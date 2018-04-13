<?php

namespace Modules\Shop\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Shop\Entities\Currency;
use Modules\Shop\Http\Requests\CurrencyRequest;
use Modules\Shop\Repositories\CurrencyRepository;
use Modules\Core\Http\Controllers\Admin\AdminBaseController;

class CurrencyController extends AdminBaseController
{
    /**
     * @var CurrencyRepository
     */
    private $currency;

    public function __construct(CurrencyRepository $currency)
    {
        parent::__construct();

        $this->currency = $currency;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $currencies = $this->currency->all();

        return view('shop::admin.currencies.index', compact('currencies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('shop::admin.currencies.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CurrencyRequest $request
     * @return Response
     */
    public function store(CurrencyRequest $request)
    {
        $this->currency->create($request->all());

        return redirect()->route('admin.shop.currency.index')
            ->withSuccess(trans('core::core.messages.resource created', ['name' => trans('shop::currencies.title.currencies')]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Currency $currency
     * @return Response
     */
    public function edit(Currency $currency)
    {
        return view('shop::admin.currencies.edit', compact('currency'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Currency $currency
     * @param  CurrencyRequest $request
     * @return Response
     */
    public function update(Currency $currency, CurrencyRequest $request)
    {
        $this->currency->update($currency, $request->all());

        return redirect()->route('admin.shop.currency.index')
            ->withSuccess(trans('core::core.messages.resource updated', ['name' => trans('shop::currencies.title.currencies')]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Currency $currency
     * @return Response
     */
    public function destroy(Currency $currency)
    {
        $this->currency->destroy($currency);

        return redirect()->route('admin.shop.currency.index')
            ->withSuccess(trans('core::core.messages.resource deleted', ['name' => trans('shop::currencies.title.currencies')]));
    }
}
