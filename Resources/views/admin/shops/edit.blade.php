@extends('layouts.master')

@section('content-header')
    <h1>
        {{ trans('shop::shops.title.edit shop') }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard.index') }}"><i class="fa fa-dashboard"></i> {{ trans('core::core.breadcrumb.home') }}</a></li>
        <li><a href="{{ route('admin.shop.shop.index') }}">{{ trans('shop::shops.title.shops') }}</a></li>
        <li class="active">{{ trans('shop::shops.title.edit shop') }}</li>
    </ol>
@stop

@section('content')
    {!! Form::open(['route' => ['admin.shop.shop.update', $shop->id], 'method' => 'put']) !!}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h4 class="box-title">{{ trans('shop::shops.title.shop info') }}</h4>
                </div>
                <div class="box-body">

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('subdomain') ? 'has-error' : '' }}" style="display: inline-block">
                                <label for="subdomain">{{ trans('shop::shops.subdomain') }}</label>
                                <div class="input-group">
                                  <input type="text" name="subdomain" id="subdomain" class="form-control" style="" value="{{ old('subdomain', $shop->subdomain) }}" />
                                  <span class="input-group-addon">{{ config('session.domain') }}</span>
                                </div>
                                {!! $errors->first('currency_code', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            {!! Form::normalInput('name', trans('shop::shops.name'), $errors, $shop) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            {!! Form::normalInput('owner_name', trans('shop::shops.owner_name'), $errors, $shop) !!}
                        </div>
                        <div class="col-sm-6">
                            {!! Form::normalInput('email', trans('shop::shops.email'), $errors, $shop) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            {!! Form::normalInput('phone', trans('shop::shops.phone'), $errors, $shop) !!}
                        </div>
                        <div class="col-sm-6">
                            {!! Form::normalInput('fax', trans('shop::shops.fax'), $errors, $shop) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            {!! Form::normalInput('postcode', trans('shop::shops.postcode'), $errors, $shop) !!}
                        </div>
                        <div class="col-sm-6">
                            {!! Form::normalInput('address', trans('shop::shops.address'), $errors, $shop) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            {!! Form::normalInput('address_detail', trans('shop::shops.address_detail'), $errors, $shop) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            {!! Form::normalInput('lat', trans('shop::shops.lat'), $errors, $shop) !!}
                        </div>
                        <div class="col-sm-6">
                            {!! Form::normalInput('lng', trans('shop::shops.lng'), $errors, $shop) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('currency_code') ? 'has-error' : '' }}">
                                <label for="currency_code">{{ trans('shop::shops.currency_code') }}</label>
                                <select class="selectize" name="currency_code" id="currency_code">
                                    @foreach (Shop::getCurrencies() as $currency)
                                        <option value="{{ $currency->code }}" {{ $currency->code == old('currency_code', $shop->currency_code)  ? 'selected' : '' }}>
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
                                        <option value="{{ $name }}" {{ $name == old('theme', $shop->theme)  ? 'selected' : '' }}>
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
                    <button type="submit" class="btn btn-primary btn-flat">{{ trans('core::core.button.update') }}</button>
                    <a class="btn btn-danger pull-right btn-flat" href="{{ route('admin.shop.shop.index')}}"><i class="fa fa-times"></i> {{ trans('core::core.button.cancel') }}</a>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h4 class="box-title">{{ trans('shop::paymentgatewayconfigs.title.paymentgatewayconfigs') }}</h4>
                </div>
                <div class="box-body">
                    @include('shop::admin.paymentgatewayconfigs.partials.index')
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h4 class="box-title">{{ trans('shop::shippinggatewayconfigs.title.shippinggatewayconfigs') }}</h4>
                </div>
                <div class="box-body">
                    @include('shop::admin.shippinggatewayconfigs.partials.index')
                </div>
            </div>

        </div>
    </div>
    {!! Form::close() !!}
    @include('core::partials.delete-modal')
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
