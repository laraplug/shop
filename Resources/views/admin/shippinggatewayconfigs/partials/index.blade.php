<table class="table">
    <tbody>
        <tr>
            <th>#</th>
            <th>{{ trans('shop::shippinggatewayconfigs.gateway_id') }}</th>
            <th>{{ trans('shop::shippinggatewayconfigs.merchant_id') }}</th>
            <th>{{ trans('shop::shippinggatewayconfigs.merchant_token') }}</th>
            <th>{{ trans('shop::shippinggatewayconfigs.enabled_methods') }}</th>
            <th>{{ trans('shop::shippinggatewayconfigs.options') }}</th>
            <th>{{ trans('core::core.table.actions') }}</th>
        </tr>
        @foreach ($shop->shippingGatewayConfigs as $config)
        <tr>
            <td>{{ $loop->index + 1 }}</td>
            <td>{{ $config->gateway->getName() }}</td>
            <td>{{ $config->merchant_id }}</td>
            <td>{{ $config->merchant_token }}</td>
            <td>
              @foreach ($config->enabled_methods as $method)
                  {{ $method::getName() }}
              @endforeach
            </td>
            <td>
              @foreach ($config->options as $key => $value)
                  <p>{{ $config->gateway->getOptionName($key) }} :  {{ $value }}</p>
              @endforeach
            </td>
            <td>
                <a class="btn btn-default btn-flat" href="{{ route('admin.shop.shippinggatewayconfig.edit', ['shop'=>$shop->id, 'shippinggatewayconfig'=>$config->id]) }}">
                    <i class="fa fa-pencil"></i>
                </a>
                <button class="btn btn-danger btn-flat" data-toggle="modal" data-target="#modal-delete-confirmation" data-action-target="{{ route('admin.shop.shippinggatewayconfig.destroy', ['shop'=>$shop->id,'shippinggatewayconfig'=>$config->id]) }}">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
        @endforeach
        <tr>
            <td colspan="7">
                <a href="{{ route('admin.shop.shippinggatewayconfig.create', $shop->id) }}" class="btn btn-primary btn-sm">
                    {{ trans('shop::shippinggatewayconfigs.button.create shippinggatewayconfig') }}
                </a>
            </td>
        </tr>
  </tbody>
</table>
