<?php

namespace Modules\Shop\Entities;

use Illuminate\Support\Facades\Lang;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{

    protected $table = 'shop__currencies';
    protected $fillable = [
        'code',
        'value',
    ];

    protected $appends = [
        'name',
        'precision',
        'subunit',
        'symbol',
        'symbol_first',
        'decimal_mark',
        'thousands_separator',
    ];

    protected function data()
    {
        return currency($this->code)->toArray()[$this->code];
    }

    public function getNameAttribute()
    {
        if(Lang::has("shop::currencies.names.$this->code")) {
            return trans("shop::currencies.names.$this->code");
        }
        return $this->data()['name'];
    }

    public function getPrecisionAttribute()
    {
        return $this->data()['precision'];
    }

    public function getSubunitAttribute()
    {
        return $this->data()['subunit'];
    }

    public function getSymbolAttribute()
    {
        return $this->data()['symbol'];
    }

    public function getSymbolFirstAttribute()
    {
        return $this->data()['symbol_first'];
    }

    public function getDecimalMarkAttribute()
    {
        return $this->data()['decimal_mark'];
    }

    public function getThousandsSeparatorAttribute()
    {
        return $this->data()['thousands_separator'];
    }

}
