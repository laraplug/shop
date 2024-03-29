<?php

namespace Modules\Shop\Payments\Gateways;

use Modules\Order\Entities\Transaction;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Modules\Shop\Exceptions\GatewayException;
use Jenssegers\Agent\Facades\Agent;

use Modules\Shop\Payments\Gateways\Nicepay\NicepayLite;
use Modules\Shop\Payments\Methods\Card;

/**
 * Nicepay Gateway 나이스페이 게이트웨이
 *
 * NicepayLite를 기반으로 제작되었습니다
 *
 * @author Darron Park
 * @copyright Laraplug
 * @license MIT
 * @package Laraplug\Nicepay
 */
class NicepayGateway extends PaymentGateway
{
    /**
     * 나이스페이 전송타입 : 일반
     * @var int
     */
    const TRANS_TYPE_NORMAL = 0;
    /**
     * 나이스페이 전송타입 : 에스크로
     * @var int
     */
    const TRANS_TYPE_ESCROW = 1;
    /**
     * 나이스페이 상품종류 : 콘텐츠
     * @var int
     */
    const GOODS_CL_CONTENTS = 0;
    /**
     * 나이스페이 상품종류 : 실물
     * @var int
     */
    const GOODS_CL_REAL = 0;

    /**
     * 나이스페이 취소코드 : 전체
     * @var int
     */
    const CANCEL_CODE_TOTAL = 0;

    /**
     * 나이스페이 취소코드 : 부분취소
     * @var int
     */
    const CANCEL_CODE_PARTIAL = 1;

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return 'nicepay';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return trans('shop::payments.gateways.nicepay');
    }

    /**
     * 나이스페이에서 지원되는 결제타입 설정
     * @var int
     */
    protected $supportedPaymentMethods = [
        Card::class,
        // 'BANK',
        // 'CELLPHONE',
        // 'VBANK',
    ];

    /**
     * @var string
     */
    protected $options = [
        'TransType' => self::TRANS_TYPE_NORMAL,
        'GoodsCl' => self::GOODS_CL_CONTENTS,
        'CancelPw' => '',
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->api = new NicepayLite;
    }

    /**
     * @inheritDoc
     */
    public function preparePayment($callbackUrl)
    {
        $order = $this->order;
        $paymentMethod = strtoupper($order->payment_method);

        $this->api->m_MerchantKey = $this->merchantToken;
        $this->api->m_MID = $this->merchantId;
        $this->api->m_Moid = $this->order->id;
        $this->api->m_Price = $this->order->total;
//        세금관련 파라미터 추가 20200904 Ho
        $this->api->m_SupplyAmt = $this->order->total_supply_amount; //공급가
        $this->api->m_GoodsVat = $this->order->total_tax_amount; //부가가치세
        $this->api->m_TaxFreeAmt = $this->order->total_tax_free_amount;//면세금액

        $this->api->m_BuyerEmail = $this->order->payment_email;
        $this->api->m_BuyerName = $this->order->payment_name;
        $this->api->m_BuyerTel = $this->order->payment_phone;
        $this->api->m_GoodsName = $this->order->name;
        $this->api->m_EdiDate = date("YmdHis");

        $csrf_field = csrf_field();
        $hashString = bin2hex(hash('sha256', $this->api->m_EdiDate . $this->api->m_MID . $this->api->m_Price . $this->api->m_MerchantKey, true));
        $ip = Request::ip();
        // 전송타입
        $transType = $this->getOptionValue('TransType');
        $goodsCl = $this->getOptionValue('GoodsCl');
        $this->api->requestProcess();
        // 모바일결제
        if(Agent::isMobile()) {
            $this->payButtonOnClick = 'goPay()';

            return <<<HTML
            <script type="text/javascript">
            //스마트폰 결제 요청
            function goPay() {
                document.payForm.submit();
                document.charset = "euc-kr";
            }
            </script>
            <form name="payForm" method="post" target="_self" action="https://web.nicepay.co.kr/smart/paySmart.jsp" accept-charset="euc-kr">
                {$csrf_field}
                <!-- 정보 -->
                <input type="hidden" name="PayMethod" value="$paymentMethod" />
                <input type="hidden" name="GoodsName" value="{$this->api->m_GoodsName}" />
                <input type="hidden" name="GoodsCnt" value="{$order->count}" />
                <input type="hidden" name="Amt" value="{$this->api->m_Price}" />
                <input type="hidden" name="BuyerName" value="{$this->api->m_BuyerName}" />
                <input type="hidden" name="BuyerTel" value="{$this->api->m_BuyerTel}" />
                <input type="hidden" name="BuyerAddr" value="{$order->payment_address}" />
                <input type="hidden" name="Moid" value="{$this->api->m_Moid}" />
                <input type="hidden" name="MID" value="{$this->api->m_MID}" />
                <!-- IP -->
                <input type="hidden" name="UserIP" value="$ip" />
                <!-- 옵션 -->
                <input type="hidden" name="CharSet" value="utf-8"/>
                <input type="hidden" name="ReturnURL" value="{$callbackUrl}"/>
                <input type="hidden" name="BuyerEmail" value="{$this->api->m_BuyerEmail}" />
                <input type="hidden" name="TransType" value="{$transType}" />
                <input type="hidden" name="GoodsCl" value="{$goodsCl}" />
                <!-- 변경 불가 -->
                <input type="hidden" name="EdiDate" value="{$this->api->m_EdiDate}" />
                <input type="hidden" name="EncryptData" value="$hashString" />
                <!-- 면세관련 설정 (1617 오류 해결) -->
                <!-- 필드명:SupplyAmt / 사이즈:12 / 설명:공급가 액 -->
                <input type="hidden" name="SupplyAmt" value="{$this->api->m_SupplyAmt}">
                <!-- 필드명:GoodsVat / 사이즈:12 / 설명:부가가 치세 -->
                <input type="hidden" name="GoodsVat" value="{$this->api->m_GoodsVat}">
                <!-- 필드명:ServiceAmt / 사이즈:12 / 설명:봉사료 -->
                <input type="hidden" name="ServiceAmt" value="0">
                <!-- 필드명:TaxFreeAmt / 사이즈:12 / 설명:면세 금액 -->
                <input type="hidden" name="TaxFreeAmt" value="{$this->api->m_TaxFreeAmt}">
            </form>
HTML;
        }
        // PC결제
        else {
            $this->payButtonOnClick = 'nicepayStart()';

            return <<<HTML
            <script src="https://web.nicepay.co.kr/flex/js/nicepay_tr_utf.js" type="text/javascript"></script>
            <script type="text/javascript">
            //결제창 최초 요청시 실행됩니다.
            function nicepayStart(){
                goPay(document.payForm);
            }
            //결제 최종 요청시 실행됩니다. <<'nicepaySubmit()' 이름 수정 불가능>>
            function nicepaySubmit(){
                document.payForm.submit();
            }
            //결제창 종료 함수 <<'nicepayClose()' 이름 수정 불가능>>
            function nicepayClose(){
                alert("결제가 취소 되었습니다");
            }
            </script>
            <form name="payForm" method="post" action="$callbackUrl">
                {$csrf_field} 
                <!-- 정보 -->
                <input type="hidden" name="PayMethod" value="$paymentMethod" />
                <input type="hidden" name="GoodsName" value="{$this->api->m_GoodsName}" />
                <input type="hidden" name="GoodsCnt" value="{$order->count}" />
                <input type="hidden" name="Amt" value="{$this->api->m_Price}" />
                <input type="hidden" name="BuyerName" value="{$this->api->m_BuyerName}" />
                <input type="hidden" name="BuyerTel" value="{$this->api->m_BuyerTel}" />
                <input type="hidden" name="BuyerAddr" value="{$order->payment_address}" />
                <input type="hidden" name="Moid" value="{$this->api->m_Moid}" />
                <input type="hidden" name="MID" value="{$this->api->m_MID}" />
                <!-- IP -->
                <input type="hidden" name="UserIP" value="$ip" />
                <!-- 옵션 -->
                <input type="hidden" name="BuyerEmail" value="{$this->api->m_BuyerEmail}" />
                <input type="hidden" name="TransType" value="{$transType}" />
                <input type="hidden" name="GoodsCl" value="{$goodsCl}" />
                <!-- 변경 불가 -->
                <input type="hidden" name="EdiDate" value="{$this->api->m_EdiDate}" />
                <input type="hidden" name="EncryptData" value="$hashString" />
                <input type="hidden" name="TrKey" value="" />
                <!-- 면세관련 설정 (1617 오류 해결) -->
                <!-- 필드명:SupplyAmt / 사이즈:12 / 설명:공급가 액 -->
                <input type="hidden" name="SupplyAmt" value="{$this->api->m_SupplyAmt}">
                <!-- 필드명:GoodsVat / 사이즈:12 / 설명:부가가 치세 -->
                <input type="hidden" name="GoodsVat" value="{$this->api->m_GoodsVat}">
                <!-- 필드명:ServiceAmt / 사이즈:12 / 설명:봉사료 -->
                <input type="hidden" name="ServiceAmt" value="0">
                <!-- 필드명:TaxFreeAmt / 사이즈:12 / 설명:면세 금액 -->
                <input type="hidden" name="TaxFreeAmt" value="{$this->api->m_TaxFreeAmt}">
            </form>
HTML;
        }

    }

    /**
     * Called by shop to charge order's amount.
     *
     * @param array $data
     *
     * @return bool
     */
    public function pay($data = null)
    {
        $order = $this->order;
        $user = Auth::user();

        if (!isset($this->merchantId)) {
            throw new GatewayException('상점ID가 설정되지 않았습니다', 0);
        }

        if (!isset($this->merchantToken)) {
            throw new GatewayException('상점Key가 설정되지 않았습니다', 0);
        }

        if ($order->total <= 0) {
            throw new GatewayException('상품가격이 0', 0);
        }

        $this->api->m_NicepayHome   = $this->getLogPath();               // 로그 디렉토리 설정
        $this->api->m_ActionType    = "PYO";                  // ActionType
        $this->api->m_charSet       = "UTF8";                 // 인코딩
        $this->api->m_ssl           = "true";                 // 보안접속 여부
        $this->api->m_Price         = $order->total;          // 금액

        /*
        *******************************************************
        * <결제 결과 필드>
        *******************************************************
        */
        $this->api->m_BuyerName     = $data['BuyerName'];             // 구매자명
        $this->api->m_BuyerEmail    = $data['BuyerEmail'];            // 구매자이메일
        $this->api->m_BuyerTel      = $data['BuyerTel'];              // 구매자연락처
        $this->api->m_GoodsName     = $data['GoodsName'];             // 상품명
        $this->api->m_GoodsCnt      = $data['GoodsCnt'];            // 상품개수
        $this->api->m_GoodsCl       = $this->getOptionValue('GoodsCl');  // 실물 or 컨텐츠
        $this->api->m_Moid          = $data['Moid'];                  // 주문번호
        $this->api->m_MallUserID    = isset($user->email)?$user->email:$data['email'];                         // 회원ID
        $this->api->m_MID           = $data['MID'];                   // MID
        //$this->api->m_MallIP        = $data['MallIP'];                // Mall IP
        $this->api->m_MerchantKey   = $this->merchantToken;           // 상점키
        $this->api->m_LicenseKey    = $this->merchantToken;           // 상점키
        $this->api->m_TransType     = $this->getOptionValue('TransType');  // 일반 or 에스크로
        $this->api->m_TrKey         = $data['TrKey'];                 // 거래키
        $this->api->m_PayMethod     = $data['PayMethod'];             // 결제수단

        // 모바일결제에서 BuyerAddr이 누락되어 수동으로 넣어줌
        $_POST['BuyerAddr'] = $order->payment_address;

        $this->api->startAction();

        /*
        *******************************************************
        * <결제 성공 여부 확인>
        *******************************************************
        */
        $resultCode = $this->api->m_ResultData["ResultCode"];
        $this->message = $this->api->m_ResultData["ResultMsg"];

        $bankName = '';
        $bankAccount = '';
        $additionalData = [];

        if ($data['PayMethod'] == "CARD") {
            // 신용카드(정상 결과코드:3001)
            if ($resultCode !== "3001") {
                throw new GatewayException('카드결제 에러: ' . $this->message, 0);
            }

            $bankName = $this->api->m_ResultData["CardName"];
            $bankAccount = $this->api->m_ResultData["CardNo"];
            $additionalData['installment']  = $this->api->m_ResultData["CardQuota"];
        }

        $this->transactionId = $this->api->m_ResultData["TID"];
        $amount = $this->api->m_ResultData["Amt"];

        return $this->onPaySucceed($amount, $bankName, $bankAccount, $additionalData);
    }

    /**
     * @inheritDoc
     */
    public function cancel(Transaction $transaction, $reason = null)
    {
        if (!$transaction) {
            throw new GatewayException('취소할 거래가 설정되지 않았습니다', 0);
        }

        if ($transaction->amount <= 0) {
            throw new GatewayException('거래금액이 올바르지 않습니다', 0);
        }

        if (!isset($this->merchantId)) {
            throw new GatewayException('상점ID가 설정되지 않았습니다', 0);
        }

        $this->transactionId = $transaction->gateway_transaction_id;

        $this->api->m_NicepayHome       = $this->getLogPath();               // 로그 디렉토리 설정
        $this->api->m_ActionType        = "CLO";                            // 취소 요청 선언
        $this->api->m_CancelAmt         = $transaction->amount;             // 취소 금액 설정
        $this->api->m_TID               = $transaction->gateway_transaction_id;  // 취소 TID 설정
        $this->api->m_CancelMsg         = $reason;                     // 취소 사유
        $this->api->m_PartialCancelCode = self::CANCEL_CODE_TOTAL;     // 전체 취소, 부분 취소 여부 설정
        $this->api->m_CancelPwd         = $this->getOptionValue('CancelPw');       // 취소 비밀번호 설정
        $this->api->m_ssl               = "true";                 // 보안접속 여부
        $this->api->m_charSet           = "UTF8";                 // 인코딩

        $this->api->startAction();

        /*
        *******************************************************
        * <취소 성공 여부 확인>
        *******************************************************
        */

        $resultCode = $this->api->m_ResultData["ResultCode"];
        $this->message = $this->api->m_ResultData["ResultMsg"];

        // 취소(정상 결과코드:2001)
        if ($resultCode !== "2001") {
            throw new GatewayException('결제취소 에러: ' . $this->message, 0);
        }

        $amount = $this->api->m_ResultData["CancelAmt"];

        return $this->onCancelSucceed($transaction, $reason);
    }
}
