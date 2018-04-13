@extends('layouts.master')

@section('content-header')
    <h1>
        {{ trans('shop::shops.title.shops') }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard.index') }}"><i class="fa fa-dashboard"></i> {{ trans('core::core.breadcrumb.home') }}</a></li>
        <li class="active">{{ trans('shop::shops.title.shops') }}</li>
    </ol>
@stop

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="btn-group pull-right" style="margin: 0 15px 15px 0;">
                    <a href="{{ route('admin.shop.shop.create') }}" class="btn btn-primary btn-flat" style="padding: 4px 10px;">
                        <i class="fa fa-pencil"></i> {{ trans('shop::shops.button.create shop') }}
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
                                <th>{{ trans('shop::shops.domain') }}</th>
                                <th>{{ trans('shop::shops.name') }}</th>
                                <th>{{ trans('shop::shops.currency_code') }}</th>
                                <th>{{ trans('shop::shops.theme') }}</th>
                                <th>{{ trans('core::core.table.created at') }}</th>
                                <th data-sortable="false">{{ trans('core::core.table.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (isset($shops)): ?>
                            <?php foreach ($shops as $shop): ?>
                            <tr>
                                <td>
                                    <a href="{{ route('admin.shop.shop.edit', [$shop->id]) }}">
                                        {{ $shop->id }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.shop.shop.edit', [$shop->id]) }}">
                                        {{ $shop->domain }}
                                        @if(app()->environment('local'))
                                            <br /> local.{{ $shop->domain }}
                                        @endif
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.shop.shop.edit', [$shop->id]) }}">
                                        {{ $shop->name }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.shop.shop.edit', [$shop->id]) }}">
                                        {{ Currency::getCurrencyName($shop->currency_code, true) }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.shop.shop.edit', [$shop->id]) }}">
                                        {{ $shop->theme }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.shop.shop.edit', [$shop->id]) }}">
                                        {{ $shop->created_at }}
                                    </a>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.shop.shop.edit', [$shop->id]) }}" class="btn btn-default btn-flat"><i class="fa fa-pencil"></i></a>
                                        @if($shop->id > 1)
                                        <button class="btn btn-danger btn-flat" data-toggle="modal" data-target="#modal-delete-confirmation" data-action-target="{{ route('admin.shop.shop.destroy', [$shop->id]) }}"><i class="fa fa-trash"></i></button>
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
                                <th>{{ trans('shop::shops.domain') }}</th>
                                <th>{{ trans('shop::shops.name') }}</th>
                                <th>{{ trans('shop::shops.description') }}</th>
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
        <dd>{{ trans('shop::shops.title.create shop') }}</dd>
    </dl>
@stop

@push('js-stack')
    <script type="text/javascript">
        $( document ).ready(function() {
            $(document).keypressAction({
                actions: [
                    { key: 'c', route: "<?= route('admin.shop.shop.create') ?>" }
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
