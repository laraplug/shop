@extends('layouts.master')

@section('content-header')
    <h1>
        {{ trans('shop::currencies.title.currencies') }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard.index') }}"><i class="fa fa-dashboard"></i> {{ trans('core::core.breadcrumb.home') }}</a></li>
        <li class="active">{{ trans('shop::currencies.title.currencies') }}</li>
    </ol>
@stop

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="btn-group pull-right" style="margin: 0 15px 15px 0;">
                    <a href="{{ route('admin.shop.currency.create') }}" class="btn btn-primary btn-flat" style="padding: 4px 10px;">
                        <i class="fa fa-pencil"></i> {{ trans('shop::currencies.button.create currency') }}
                    </a>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header">
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="data-table table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ trans('shop::currencies.code') }}</th>
                                <th>{{ trans('shop::currencies.name') }}</th>
                                <th>{{ trans('shop::currencies.value') }}</th>
                                <th>{{ trans('core::core.table.created at') }}</th>
                                <th data-sortable="false">{{ trans('core::core.table.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (isset($currencies)): ?>
                            <?php foreach ($currencies as $currency): ?>
                            <tr>
                                <td>
                                    <a href="{{ route('admin.shop.currency.edit', [$currency->id]) }}">
                                        {{ $currency->id }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.shop.currency.edit', [$currency->id]) }}">
                                        {{ $currency->code }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.shop.currency.edit', [$currency->id]) }}">
                                        {{ $currency->name }} {{ $currency->symbol }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.shop.currency.edit', [$currency->id]) }}">
                                        {{ $currency->value }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.shop.currency.edit', [$currency->id]) }}">
                                        {{ $currency->created_at }}
                                    </a>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.shop.currency.edit', [$currency->id]) }}" class="btn btn-default btn-flat"><i class="fa fa-pencil"></i></a>
                                        @if($currency->id > 1)
                                        <button class="btn btn-danger btn-flat" data-toggle="modal" data-target="#modal-delete-confirmation" data-action-target="{{ route('admin.shop.currency.destroy', [$currency->id]) }}"><i class="fa fa-trash"></i></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>{{ trans('shop::currencies.code') }}</th>
                                <th>{{ trans('shop::currencies.name') }}</th>
                                <th>{{ trans('shop::currencies.value') }}</th>
                                <th>{{ trans('core::core.table.created at') }}</th>
                                <th>{{ trans('core::core.table.actions') }}</th>
                            </tr>
                            </tfoot>
                        </table>
                        <!-- /.box-body -->
                    </div>
                </div>
                <!-- /.box -->
            </div>
        </div>
    </div>
    @include('core::partials.delete-modal')
@stop

@section('footer')
    <a data-toggle="modal" data-target="#keyboardShortcutsModal"><i class="fa fa-keyboard-o"></i></a> &nbsp;
@stop
@section('shortcuts')
    <dl class="dl-horizontal">
        <dt><code>c</code></dt>
        <dd>{{ trans('shop::currencies.title.create currency') }}</dd>
    </dl>
@stop

@push('js-stack')
    <script type="text/javascript">
        $( document ).ready(function() {
            $(document).keypressAction({
                actions: [
                    { key: 'c', route: "<?= route('admin.shop.currency.create') ?>" }
                ]
            });
        });
    </script>
    <?php $locale = locale(); ?>
    <script type="text/javascript">
        $(function () {
            $('.data-table').dataTable({
                "paginate": true,
                "lengthChange": true,
                "filter": true,
                "sort": true,
                "info": true,
                "autoWidth": true,
                "order": [[ 0, "desc" ]],
                "language": {
                    "url": '<?php echo Module::asset("core:js/vendor/datatables/{$locale}.json") ?>'
                }
            });
        });
    </script>
@endpush
