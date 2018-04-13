@extends('layouts.master')

@section('content-header')
    <h1>
        {{ trans('shop::currencies.title.create currency') }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard.index') }}"><i class="fa fa-dashboard"></i> {{ trans('core::core.breadcrumb.home') }}</a></li>
        <li><a href="{{ route('admin.shop.currency.index') }}">{{ trans('shop::currencies.title.currencies') }}</a></li>
        <li class="active">{{ trans('shop::currencies.title.create currency') }}</li>
    </ol>
@stop

@section('content')
    {!! Form::open(['route' => ['admin.shop.currency.store'], 'method' => 'post']) !!}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">

                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                <label for="code">{{ trans('shop::currencies.code') }}</label>
                                <select class="selectize" name="code" id="code">
                                    @foreach (Currency::getCurrencies() as $code => $currency)
                                        <option value="{{ $code }}" {{ $code == old('code', trans('shop::currencies.default_code'))  ? 'selected' : '' }}>
                                            {{ Currency::getCurrencyName($code, true) }}
                                        </option>
                                    @endforeach
                                </select>
                                {!! $errors->first('code', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            {!! Form::normalInput('value', trans('shop::currencies.value'), $errors, (object)['value'=>1]) !!}
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary btn-flat">{{ trans('core::core.button.create') }}</button>
                    <a class="btn btn-danger pull-right btn-flat" href="{{ route('admin.shop.currency.index')}}"><i class="fa fa-times"></i> {{ trans('core::core.button.cancel') }}</a>
                </div>

            </div> {{-- end nav-tabs-custom --}}
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
                    { key: 'b', route: "<?= route('admin.shop.currency.index') ?>" }
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
