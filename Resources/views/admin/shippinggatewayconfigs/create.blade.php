@extends('layouts.master')

@section('content-header')
    <h1>
        {{ trans('shop::shippinggatewayconfigs.title.create shippinggatewayconfig') }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard.index') }}"><i class="fa fa-dashboard"></i> {{ trans('core::core.breadcrumb.home') }}</a></li>
        <li><a href="{{ route('admin.shop.shop.edit', $shop->id) }}">{{ trans('shop::shippinggatewayconfigs.title.shippinggatewayconfigs') }}</a></li>
        <li class="active">{{ trans('shop::shippinggatewayconfigs.title.create shippinggatewayconfig') }}</li>
    </ol>
@stop

@section('content')
    {!! Form::open(['route' => ['admin.shop.shippinggatewayconfig.store', $shop->id], 'method' => 'post']) !!}
    <div class="row">
        <div class="col-md-12">

            <div class="box box-primary">
                <div class="box-body">

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('gateway_id') ? 'has-error' : '' }}">
                                <label for="gateway_id">{{ trans('shop::shippinggatewayconfigs.gateway_id') }}</label>
                                <select class="form-control" name="gateway_id" id="gateway_id">
                                    @foreach ($shippingGateways as $gateway)
                                        <option value="{{ $gateway->getId() }}" {{ $gateway->getId() == old('gateway_id')  ? 'selected' : '' }}>
                                            {{ $gateway->getName() }}
                                        </option>
                                    @endforeach
                                </select>
                                {!! $errors->first('theme', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('enabled_method_ids') ? 'has-error' : '' }}">
                                <label for="enabled_method_ids">{{ trans('shop::shippinggatewayconfigs.enabled_methods') }}</label>
                                <select class="selectize" name="enabled_method_ids[]" id="enabled_method_ids" multiple>
                                </select>
                                {!! $errors->first('theme', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            {!! Form::normalInput('merchant_id', trans('shop::shippinggatewayconfigs.merchant_id'), $errors) !!}
                        </div>
                        <div class="col-sm-6">
                            {!! Form::normalInput('merchant_token', trans('shop::shippinggatewayconfigs.merchant_token'), $errors) !!}
                        </div>
                    </div>

                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary btn-flat">{{ trans('core::core.button.create') }}</button>
                    <a class="btn btn-danger pull-right btn-flat" href="{{ route('admin.shop.shop.edit', $shop->id)}}">
                        <i class="fa fa-times"></i> {{ trans('core::core.button.cancel') }}
                    </a>
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
    <script>
        $( document ).ready(function() {
            $('input[type="checkbox"].flat-blue, input[type="radio"].flat-blue').iCheck({
                checkboxClass: 'icheckbox_flat-blue',
                radioClass: 'iradio_flat-blue'
            });

            $('.selectize').selectize({
                labelField: "label",
                valueField: "value",
                searchField: 'label'
            });

            var shippingMethods = {!! $shippingMethods->toJson() !!};
            console.log(shippingMethods);
            $('[name=gateway_id]').change(function() {
                var gatewayId = $(this).val();
                var methods = shippingMethods[gatewayId];
                var selectize = $("[name='enabled_method_ids[]']")[0].selectize;
                selectize.clear();
                selectize.clearOptions();
                selectize.load(function(callback) {
                    callback(methods);
                });
            })
            .change();
        });
    </script>
@endpush
