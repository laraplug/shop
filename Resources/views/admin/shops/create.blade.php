@extends('layouts.master')

@section('content-header')
    <h1>
        {{ trans('shop::shops.title.create shop') }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard.index') }}"><i class="fa fa-dashboard"></i> {{ trans('core::core.breadcrumb.home') }}</a></li>
        <li><a href="{{ route('admin.shop.shop.index') }}">{{ trans('shop::shops.title.shops') }}</a></li>
        <li class="active">{{ trans('shop::shops.title.create shop') }}</li>
    </ol>
@stop

@section('content')
    {!! Form::open(['route' => ['admin.shop.shop.store'], 'method' => 'post']) !!}
    <div class="row">
        <div class="col-md-12">

            <div class="box box-primary">
                <div class="box-body">

                    <div class="row">
                        <div class="col-sm-6">
                            {!! Form::normalInput('domain', trans('shop::shops.domain'), $errors) !!}
                        </div>
                        <div class="col-sm-6">
                            {!! Form::normalInput('name', trans('shop::shops.name'), $errors) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            {!! Form::normalInput('owner_name', trans('shop::shops.owner_name'), $errors) !!}
                        </div>
                        <div class="col-sm-6">
                            {!! Form::normalInput('email', trans('shop::shops.email'), $errors) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            {!! Form::normalInput('phone', trans('shop::shops.phone'), $errors) !!}
                        </div>
                        <div class="col-sm-6">
                            {!! Form::normalInput('fax', trans('shop::shops.fax'), $errors) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            {!! Form::normalInput('postcode', trans('shop::shops.postcode'), $errors) !!}
                        </div>
                        <div class="col-sm-6">
                            {!! Form::normalInput('address', trans('shop::shops.address'), $errors) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            {!! Form::normalInput('address_detail', trans('shop::shops.address_detail'), $errors) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            {!! Form::normalInput('lat', trans('shop::shops.lat'), $errors, (object)['lat'=>0]) !!}
                        </div>
                        <div class="col-sm-6">
                            {!! Form::normalInput('lng', trans('shop::shops.lng'), $errors, (object)['lng'=>0]) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('currency_code') ? 'has-error' : '' }}">
                                <label for="currency_code">{{ trans('shop::shops.currency_code') }}</label>
                                <select class="selectize" name="currency_code" id="currency_code">
                                    @foreach (Shop::getCurrencies() as $currency)
                                        <option value="{{ $currency->code }}" {{ $currency->code == old('currency_code', trans('shop::currencies.default_code'))  ? 'selected' : '' }}>
                                            {{ Currency::getCurrencyName($currency->code, true) }}
                                        </option>
                                    @endforeach
                                </select>
                                {!! $errors->first('currency_code', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('theme') ? 'has-error' : '' }}">
                                <label for="theme">{{ trans('shop::shops.theme') }}</label>
                                <select class="selectize" name="theme" id="theme">
                                    @foreach ($themes as $name => $theme)
                                        <option value="{{ $name }}" {{ $name == old('theme')  ? 'selected' : '' }}>
                                            {{ $theme->getName() }}
                                        </option>
                                    @endforeach
                                </select>
                                {!! $errors->first('theme', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>
                    </div>

                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary btn-flat">{{ trans('core::core.button.create') }}</button>
                    <a class="btn btn-danger pull-right btn-flat" href="{{ route('admin.shop.shop.index')}}"><i class="fa fa-times"></i> {{ trans('core::core.button.cancel') }}</a>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop

@section('footer')
    <a data-toggle="modal" data-target="#keyboardShortcutsModal"><i class="fa fa-keyboard-o"></i></a> &nbsp;
@stop
@section('shortcuts')
    <dl class="dl-horizontal">
        <dt><code>b</code></dt>
        <dd>{{ trans('core::core.back to index') }}</dd>
    </dl>
@stop

@push('js-stack')
    <script type="text/javascript">
        $( document ).ready(function() {
            $(document).keypressAction({
                actions: [
                    { key: 'b', route: "<?= route('admin.shop.shop.index') ?>" }
                ]
            });
        });
    </script>
    <script>
        $( document ).ready(function() {
            $('input[type="checkbox"].flat-blue, input[type="radio"].flat-blue').iCheck({
                checkboxClass: 'icheckbox_flat-blue',
                radioClass: 'iradio_flat-blue'
            });
            $('.selectize').selectize();
        });
    </script>
@endpush
