<table class="table">
    <tbody>
        <tr>
            <th>#</th>
            <th>{{ trans('shop::paymentgatewayconfigs.gateway_id') }}</th>
            <th>{{ trans('shop::paymentgatewayconfigs.merchant_id') }}</th>
            <th>{{ trans('shop::paymentgatewayconfigs.merchant_token') }}</th>
            <th>{{ trans('shop::paymentgatewayconfigs.enabled_methods') }}</th>
            <th>{{ trans('shop::paymentgatewayconfigs.options') }}</th>
            <th>{{ trans('core::core.table.actions') }}</th>
        </tr>
        @foreach ($shop->paymentGatewayConfigs as $config)
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
                <a class="btn btn-default btn-flat" href="{{ route('admin.shop.paymentgatewayconfig.edit', ['shop'=>$shop->id, 'paymentgatewayconfig'=>$config->id]) }}">
                    <i class="fa fa-pencil"></i>
                </a>
                <button class="btn btn-danger btn-flat" data-toggle="modal" data-target="#modal-delete-confirmation" data-action-target="{{ route('admin.shop.paymentgatewayconfig.destroy', ['shop'=>$shop->id,'paymentgatewayconfig'=>$config->id]) }}">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
        @endforeach
        <tr>
            <td colspan="7">
                <a href="{{ route('admin.shop.paymentgatewayconfig.create', $shop->id) }}" class="btn btn-primary btn-sm">
                    {{ trans('shop::paymentgatewayconfigs.button.create paymentgatewayconfig') }}
                </a>
            </td>
        </tr>
  </tbody>
</table>
