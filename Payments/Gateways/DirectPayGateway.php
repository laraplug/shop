<?php

namespace Modules\Shop\Payments\Gateways;

use Modules\Order\Entities\OrderStatus;

use Modules\Shop\Payments\Methods\DirectBank;

/**
 * DirectPay Gateway
 * 직접결제 게이트웨이
 */

class DirectPayGateway extends PaymentGateway
{

    /**
     * @var int
     */
    protected $initialStatusId = OrderStatus::PENDING_PAYMENT_APPROVAL;

    /**
     * @var string
     */
    protected $options = [
        'bank_name' => '',
        'account_number' => '',
        'account_name' => '',
    ];

    /**
     * @inheritDoc
     */
    public function getOptionName($key)
    {
        return trans("shop::payments.bank_infos.$key");
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return 'direct';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return trans('shop::payments.gateways.direct_pay');
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalData()
    {
        if($this->paymentMethod == DirectBank::class) {
            return [
                trans('shop::payments.bank_info') => [
                    'bank_name' => $this->getOptionValue('bank_name'),
                    'account_number' => $this->getOptionValue('account_number'),
                    'account_name' => $this->getOptionValue('account_name'),
                ]
            ];
        }
        return [];
    }

    /**
     * 지원되는 결제타입 설정
     * @var int
     */
    protected $supportedPaymentMethods = [
        DirectBank::class
    ];

    /**
     * @inheritDoc
     */
    public function preparePayment($submitUrl)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function pay($data = null)
    {
        return null;
    }

}
