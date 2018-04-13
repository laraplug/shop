<?php

namespace Modules\Shop\Payments\Gateways;

use Modules\Shop\Exceptions\GatewayException;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

use Modules\Shop\Payments\Methods\Card;

use Modules\Shop\Payments\Gateways\Nicepay\NicepayLite;

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
        'transType' => self::TRANS_TYPE_NORMAL,
        'goodsCl' => self::GOODS_CL_CONTENTS,
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
        $this->api->m_BuyerEmail = $this->order->payment_email;
        $this->api->m_BuyerName = $this->order->payment_name;
        $this->api->m_BuyerTel = $this->order->payment_phone;
        $this->api->m_GoodsName = $this->order->name;
        $this->api->m_EdiDate = date("YmdHis");

        $csrf_field = csrf_field();
        $hashString = bin2hex(hash('sha256', $this->api->m_EdiDate.$this->api->m_MID.$this->api->m_Price.$this->api->m_MerchantKey, true));
        $ip = Request::ip();
        // 전송타입
        $transType = $this->getOptionValue('transType');
        $goodsCl = $this->getOptionValue('goodsCl');

        $this->api->requestProcess();
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
        $csrf_field
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
    </form>
HTML;
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

        if (!isset($this->merchantId))
            throw new GatewayException('상점ID가 설정되지 않았습니다', 0);

        if (!isset($this->merchantToken))
            throw new GatewayException('상점Key가 설정되지 않았습니다', 0);

        if ($order->total <= 0)
            throw new GatewayException('상품가격이 0', 0);

        $this->api->m_NicepayHome   = storage_path('/logs/');               // 로그 디렉토리 설정
        $this->api->m_ActionType    = "PYO";                  // ActionType
        $this->api->m_charSet       = "UTF8";                 // 인코딩
        $this->api->m_ssl           = "true";                 // 보안접속 여부
        $this->api->m_Price         = $order->total;          // 금액
        $this->api->m_NetCancelAmt  = $order->total;          // 취소 금액
        $this->api->m_NetCancelPW   = "";                     // 결제 취소 패스워드 설정

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
        $this->api->m_GoodsCl       = $data['GoodsCl'];               // 실물 or 컨텐츠
        $this->api->m_Moid          = $data['Moid'];                  // 주문번호
        $this->api->m_MallUserID    = $user->email;                         // 회원ID
        $this->api->m_MID           = $data['MID'];                   // MID
        //$this->api->m_MallIP        = $data['MallIP'];                // Mall IP
        $this->api->m_MerchantKey   = $this->merchantToken;           // 상점키
        $this->api->m_LicenseKey    = $this->merchantToken;           // 상점키
        $this->api->m_TransType     = $data['TransType'];             // 일반 or 에스크로
        $this->api->m_TrKey         = $data['TrKey'];                 // 거래키
        $this->api->m_PayMethod     = $data['PayMethod'];             // 결제수단
        $this->api->startAction();

        /*
        *******************************************************
        * <결제 성공 여부 확인>
        *******************************************************
        */
        $resultCode = $this->api->m_ResultData["ResultCode"];
        $this->message = $this->api->m_ResultData["ResultMsg"];

        if($data['PayMethod'] == "CARD"){
            // 신용카드(정상 결과코드:3001)
            if ($resultCode !== "3001")
                throw new GatewayException('카드결제 에러: '.$this->message, 0);
        }

        $this->transactionId = $this->api->m_ResultData["TID"];
        $amount = $this->api->m_ResultData["Amt"];

        return $this->onPaySucceed($amount);
    }

}
