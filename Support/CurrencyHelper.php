<?php

namespace Modules\Shop\Support;

use Akaunting\Money\Currency;

use Illuminate\Support\Facades\Lang;

class CurrencyHelper
{

    /**
     * Get Currencies
     * @return array
     */
    public function getCurrencies()
    {
        return Currency::getCurrencies();
    }

    /**
     * Get Currencies
     * @param string $code
     * @param bool $withSymbol
     * @return array
     */
    public function getCurrencyName($code, $withSymbol = false)
    {
        $currency = currency($code)->toArray()[$code];
        $symbol = $withSymbol ? ' '.$currency['symbol'] : '';
        if(Lang::has("shop::currencies.names.$code")) {
            return trans("shop::currencies.names.$code").$symbol;
        }
        return $currency['name'];
    }


}
