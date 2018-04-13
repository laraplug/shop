@extends('layouts.master')

@section('content-header')
    <h1>
        {{ trans('shop::paymentgatewayconfigs.title.edit paymentgatewayconfig') }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard.index') }}"><i class="fa fa-dashboard"></i> {{ trans('core::core.breadcrumb.home') }}</a></li>
        <li><a href="{{ route('admin.shop.shop.edit', $shop->id) }}">{{ trans('shop::paymentgatewayconfigs.title.paymentgatewayconfigs') }}</a></li>
        <li class="active">{{ trans('shop::paymentgatewayconfigs.title.edit paymentgatewayconfig') }}</li>
    </ol>
@stop

@section('content')
    {!! Form::open(['route' => ['admin.shop.paymentgatewayconfig.update', 'shop'=>$shop->id,'paymentgatewayconfig'=>$gatewayConfig->id], 'method' => 'post']) !!}
    <div class="row">
        <div class="col-md-12">

            <div class="box box-primary">
                <div class="box-body">

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('gateway_id') ? 'has-error' : '' }}">
                                <label for="gateway_id">{{ trans('shop::paymentgatewayconfigs.gateway_id') }}</label>
                                <select class="form-control" name="gateway_id" id="gateway_id" readonly>
                                        <option value="{{ $gatewayConfig->gateway->getId() }}" selected>
                                            {{ $gatewayConfig->gateway->getName() }}
                                        </option>
                                </select>
                                {!! $errors->first('gateway_id', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('enabled_method_ids') ? 'has-error' : '' }}">
                                <label for="enabled_method_ids">{{ trans('shop::paymentgatewayconfigs.enabled_methods') }}</label>
                                <select class="selectize" name="enabled_method_ids[]" id="enabled_method_ids" multiple>
                                </select>
                                {!! $errors->first('enabled_method_ids', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            {!! Form::normalInput('merchant_id', trans('shop::paymentgatewayconfigs.merchant_id'), $errors, $gatewayConfig) !!}
                        </div>
                        <div class="col-sm-6">
                            {!! Form::normalInput('merchant_token', trans('shop::paymentgatewayconfigs.merchant_token'), $errors, $gatewayConfig) !!}
                        </div>
                    </div>

                    @foreach ($gatewayOptions as $key => $value)
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group {{ $errors->has("options[$key]") ? 'has-error' : '' }}">
                                <label for="options[{{$key}}]">{{$key}}</label>
                                <input placeholder="{{$key}}" name="options[{{$key}}]" type="text" value="{{ old("options[$key]", array_get($gatewayConfig->options, $key, '')) }}" id="options[{{$key}}]" class="form-control">
                                {!! $errors->first("options[$key]", '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary btn-flat">{{ trans('core::core.button.update') }}</button>
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
                labelField: 'label',
                valueField: 'value',
                searchField: 'label'
            });

            var paymentMethods = {!! $paymentMethods->toJson() !!};
            var selectedMethods = {!! json_encode($gatewayConfig->enabled_method_ids) !!};
            console.log(paymentMethods);
            $('[name=gateway_id]').change(function() {
                var gatewayId = $(this).val();
                var selectize = $("[name='enabled_method_ids[]']")[0].selectize;
                selectize.clear();
                selectize.clearOptions();
                selectize.load(function(callback) {
                    callback(paymentMethods);
                });
                // 처음로드될때 선택했던 메소드들 불러옴
                if(selectedMethods) selectize.setValue(selectedMethods);
                selectedMethods = null;
            })
            .change();
        });
    </script>
@endpush
