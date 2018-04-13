<?php

namespace Modules\Shop\Payments\Gateways\Nicepay;

extract($_POST);
extract($_GET);

/*____________________________________________________________
Copyright (C) 2016 NICE IT&T
*
* 해당 라이브러리는 수정하시는경우 승인및 취소에 문제가 발생할 수 있습니다.
* 임의로 수정된 코드에 대한 책임은 전적으로 수정자에게 있음을 알려드립니다.
*
*	@ description		: SSL 전문 통신을 담당한다.
*	@ name				: NicepayLite.php
*	@ auther			: NICEPAY I&T (tech@nicepay.co.kr)
*	@ date				:
*	@ modify
*
*	2013.05.24			Update Log
*	2016.08.11			라이브러리 정리
*____________________________________________________________
*/

class NicepayLite
{
	// configuration Parameter
	var $m_NicepayHome;			// 로그 경로

	// requestPage Parameter
	var $m_EdiDate;				// 처리 일시
	var $m_MerchantKey;			// 상점에 부여된 고유 키
	var $m_Price;				// 결제 금액
	var $m_HashedString;		// 주요 데이터 hash값
	var $m_VBankExpDate;		// 가상계좌 입금 마감일
	var $m_MerchantServerIp;	// 상점 서버 아이피
	var $m_UserIp;				// 구매자 아이피

	// resultPage Parameter
	var $m_GoodsName;			// 상품명
	var $m_Amt;					// 상품 가격
	var $m_Moid;				// 상점 주문번호
	var $m_BuyerName;			// 구매자 이름
	var $m_BuyerEmail;			// 구매자 이메일
	var $m_BuyerTel;			// 구매자 전화번호
	var $m_MallUserID;			// 구매자 상점 아이디
	var $m_MallReserved;		// 상점 고유필드
	var $m_GoodsCl;				// 상품 유형
	var $m_MID;					// 상점 아이디
	var $m_MallIP;				// 상점 서버 아이피 **
	var $m_TrKey;				// 암호화 데이터
	var $m_EncryptedData;		// 실제 암호화 데이터
	var $m_PayMethod;			// 결제 수단
	var $m_TransType;
	var $m_ActionType;
	var $m_LicenseKey;


	var $m_ReceiptAmt;			//현금영수증 발급 금액
	var $m_ReceiptSupplyAmt;	//현금영수증 공급액
	var $m_ReceiptVAT;			//현금영수증 부가세액
	var $m_ReceiptServiceAmt;	//현금영수증 서비스액
	var $m_ReceiptType;			//현금영수증 구분
	var $m_ReceiptTypeNo;		//


	// payResult
	//var $m_BuyerName;
	//var $m_MallUserID;
	//var $m_GoodsName;
	//var $m_PayMethod;
	//var $m_MID;
	//var $m_Moid;
	//var $m_Amt;
	//var $m_VbankExpDate;
	var $m_ResultCode;			// 결과 코드
	var $m_ResultMsg;			// 결과 메시지
	var $m_ErrorCD;				// 에러 코드
	var $m_ErrorMsg;			// 에러메시지
	var $m_AuthDate;			// 승인 시각
	var $m_AuthCode;			// 승인 번호
	var $m_TID;					// 거래 아이디
	var $m_CardCode;			// 카드 코드
	var $m_CardName;			// 승인 카드사 이름
	var $m_CardNo;				// 카드 번호
	var $m_CardQuota;			// 할부개월
	var $m_BankCode;			// 은행 코드
	var $m_BankName;			// 승인 은행 이름
	var $m_Carrier;				// 이통사 코드
	var $m_DestAddr;			//
	var $m_VbankBankCode;		// 가상계좌 은행 코드
	var $m_VbankBankName;		// 가상계좌 은행 이름
	var $m_VbankNum;			// 가상계좌 번호

	var $m_charSet;				// 캐릭터셋

	// 취소 관련
	var $m_CancelAmt;			// 취소 금액
	var $m_CancelMsg;			// 취소 메시지
	var $m_CancelPwd;           // 취소 패스워드
	var $m_PartialCancelCode; 	// 부분취소 코드

	var $m_ExpDate;				// 입금 예정일자
	var $m_ReqName;				// 입금자
	var $m_ReqTel;				// 입금자 연락처

	// 공통
	var $m_uri;					// 처리 uri
	var $m_ssl;					// 보안접속 여부
	var $m_queryString = array(); // 쿼리 스트링
	var $m_ResultData = array();  // 결과 array

	// 빌링 관련
	var $m_BillKey;             // 빌키
	var $m_ExpYear;             // 카드 유효기간
	var $m_ExpMonth;            // 카드 유효기간
	var $m_IDNo;                // 주민번호
	var $m_CardPwd;             // 카드 비밀번호
	var $m_CancelFlg;			// 삭제요청 플래그

	var $m_CartType;			// 장바구니 인지 판별 여부

	var $m_DeliveryCoNm;		// 배송 업체
	var $m_InvoiceNum;			// 송장 번호
	var $m_BuyerAddr;			// 배송지주소
	var $m_RegisterName;		// 등록자이름
	var $m_BuyerAuthNum;		// 식별자 (주민번호)
	var $m_ReqType;				// 요청 타입
	var $m_ConfirmMail;			// 이메일 발송 여부

	var $m_log;					// 로그 사용 유무
	var $m_debug;				// 로그 타입 설정



	// 총 4가지의 일을 해야함.
	// 1. 각 주요 필드의 hash 값생성
	// 2. 가상계좌 입금일 설정
	// 3. 사용자 IP 설정
	// 4. 상점 서버 아이피 설정
	function requestProcess() {
		// hash 처리
		$this->m_EdiDate = date("YmdHis");
		$str_temp = $this->m_EdiDate.$this->m_MID.$this->m_Price.$this->m_MerchantKey;
		//echo($str_temp);
		$this->m_HashedString = base64_encode( md5($str_temp ));

		// 가상계좌 입금일 설정
		$this->m_VBankExpDate = date("Ymd",strtotime("+3 day",time()));

		// 사용자 IP 설정
		$this->m_UserIp = $_SERVER['REMOTE_ADDR'];

		// 상점 서버아이피 설정
		$this->m_MerchantServerIp = $_SERVER['SERVER_ADDR'];
	}

	// https connection 을 해서 승인 요청을 함.
	function startAction() {
		if (trim($this->m_ActionType) == "" ) {
			$this->MakeErrorMsg( ERR_WRONG_ACTIONTYPE , "actionType 설정이 잘못되었습니다.");
			return;
		}

		$NICELog = new NicepayLiteLog( $this->m_log, $this->m_debug, $this->m_ActionType );

		if(!$NICELog->StartLog($this->m_NicepayHome,$this->m_MID))
		{
			$this->MakeErrorMsg( ERR_OPENLOG, "로그파일을 열수가 없습니다.");
			return;
		}

		// 취소인 경우,
		if (trim($this->m_ActionType) == "CLO" ) {
			// validation
			if(trim($this->m_TID) == "") {
				$this->MakeErrorMsg( ERR_WRONG_PARAMETER, "요청페이지 파라메터가 잘못되었습니다. [TID]");
				return;
			} else if (trim($this->m_CancelAmt) == "" ) {
				$this->MakeErrorMsg( ERROR_WRONG_PARAMETER, "요청페이지 파라메터가 잘못되었습니다. [CancelAmt]");
				return;
			} else if (trim($this->m_CancelMsg) == "" ) {
				$this->MakeErrorMsg( ERROR_WRONG_PARAMETER, "요청페이지 파라메터가 잘못되었습니다. [CancelMsg]");
				return;
			}

			$this->m_uri = "/lite/cancelProcess.jsp";
			unset($this->m_queryString);

			$this->m_queryString = $_POST;
			$this->m_queryString["MID"]			= substr($this->m_TID, 0,10);
			$this->m_queryString["TID"]			= $this->m_TID;
			$this->m_queryString["CancelAmt"]	= $this->m_CancelAmt;
			$this->m_queryString["CancelMsg"]	= $this->m_CancelMsg;
			$this->m_queryString["CancelPwd"]	= $this->m_CancelPwd;
			$this->m_queryString["PartialCancelCode"] = $this->m_PartialCancelCode;
			$this->m_queryString["CartType"]	= $this->m_CartType;

			if($this->m_charSet =="UTF8"){
				$this->m_queryString["CancelMsg"] = mb_convert_encoding($this->m_queryString["CancelMsg"], "EUC-KR", "UTF-8");
			}

			$NICELog->WriteLog($this->m_queryString["TID"] );
		//입금 후 취소
		}else if (trim($this->m_ActionType) == "DPO" ) {
			if(trim($this->m_TID) == "") {
				$this->MakeErrorMsg( ERR_WRONG_PARAMETER, "요청페이지 파라메터가 잘못되었습니다. [TID]");
				return;
			} else if (trim($this->m_CancelAmt) == "" ) {
				$this->MakeErrorMsg( ERROR_WRONG_PARAMETER, "요청페이지 파라메터가 잘못되었습니다. [CancelAmt]");
				return;
			} else if (trim($this->m_CancelMsg) == "" ) {
				$this->MakeErrorMsg( ERROR_WRONG_PARAMETER, "요청페이지 파라메터가 잘못되었습니다. [CancelMsg]");
				return;
			}

			$this->m_uri = "/lite/setOffProcess.jsp";
			unset($this->m_queryString);

			$this->m_queryString["MID"]			= substr($this->m_TID, 0,10);
			$this->m_queryString["TID"]			= $this->m_TID;
			$this->m_queryString["CancelAmt"]	= $this->m_CancelAmt;
			$this->m_queryString["CancelMsg"]	= $this->m_CancelMsg;
			$this->m_queryString["PartialCancelCode"] = $this->m_PartialCancelCode;
			$this->m_queryString["ExpDate"]		= $this->m_ExpDate;
			$this->m_queryString["ReqName"]		= $this->m_ReqName;
			$this->m_queryString["ReqTel"]		= $this->m_ReqTel;

			if($this->m_charSet =="UTF8"){
				$this->m_queryString["CancelMsg"] = mb_convert_encoding($this->m_queryString["CancelMsg"], "EUC-KR", "UTF-8");
			}

			$NICELog->WriteLog($this->m_queryString["TID"] );

		// 신용카드 빌링
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "BILL" ) {

			$this->m_uri = "/lite/billingProcess.jsp";
			unset($this->m_queryString);
			$this->m_TID = genTID($this->m_MID,"01","16");

			$this->m_queryString = $_POST;
			$this->m_queryString["BillKey"]		= $this->m_BillKey;
			$this->m_queryString["BuyerName"]	= $this->m_BuyerName;
            $this->m_queryString["BuyerTel"]	= $this->m_BuyerTel;
            $this->m_queryString["BuyerEmail"]	= $this->m_BuyerEmail;
			$this->m_queryString["Amt"]			= $this->m_Amt;
			$this->m_queryString["MID"]			= $this->m_MID;
			$this->m_queryString["TID"]			= $this->m_TID;
			$this->m_queryString["EncodeKey"]	= $this->m_LicenseKey;
			$this->m_queryString["MallIP"]		= $_SERVER['SERVER_NAME'];
			$this->m_queryString["actionType"]	= $this->m_ActionType;
			$this->m_queryString["PayMethod"]	= $this->m_PayMethod;
			$this->m_queryString["Moid"]		= $this->m_Moid;
			$this->m_queryString["GoodsName"]	= $this->m_GoodsName;
			$this->m_queryString["CardQuota"]	= $this->m_CardQuota;

			if($this->m_charSet =="UTF8"){
				$this->m_queryString["BuyerName"] = mb_convert_encoding($this->m_queryString["BuyerName"], "EUC-KR", "UTF-8");
				$this->m_queryString["GoodsName"] = mb_convert_encoding($this->m_queryString["GoodsName"], "EUC-KR", "UTF-8");
			}

		   $NICELog->WriteLog($this->m_queryString["TID"]);

		// 핸드폰 빌링
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "MOBILE_BILLING" ) {

		   $this->m_uri = "/lite/mobileBillingProcess.jsp";

			unset($this->m_queryString);

			$this->m_queryString = $_POST;
			$this->m_queryString["BillKey"]		= $this->m_BillKey;   // new
			$this->m_queryString["BuyerName"]	= $this->m_BuyerName;
			$this->m_queryString["Amt"]			= $this->m_Amt;
			$this->m_queryString["MID"]			= $this->m_MID;
			$this->m_queryString["EncodeKey"]	= $this->m_LicenseKey;
			$this->m_queryString["MallIP"]		= $_SERVER['SERVER_NAME'];
			$this->m_queryString["actionType"]	= $this->m_ActionType;
			$this->m_queryString["PayMethod"]	= $this->m_PayMethod;
			$this->m_queryString["Moid"]		= $this->m_Moid;
			$this->m_queryString["GoodsName"]	= $this->m_GoodsName;
			$this->m_queryString["CardQuota"]	= $this->m_CardQuota;

			if($this->m_charSet =="UTF8"){
				$this->m_queryString["BuyerName"] = mb_convert_encoding($this->m_queryString["BuyerName"], "EUC-KR", "UTF-8");
				$this->m_queryString["GoodsName"] = mb_convert_encoding($this->m_queryString["GoodsName"], "EUC-KR", "UTF-8");
			}

		   $NICELog->WriteLog($this->m_queryString["TID"]);

		// 빌키 발급
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "BILLKEY" ) {
			$this->m_queryString = $_POST;
			$this->m_uri = "/lite/billkeyProcess.jsp";
			unset($this->m_queryString);

			$this->m_queryString["BillKey"]		= $this->m_BillKey;
			$this->m_queryString["CardNo"]		= $this->m_CardNo;   // new
			$this->m_queryString["ExpYear"]		= $this->m_ExpYear;
			$this->m_queryString["ExpMonth"]	= $this->m_ExpMonth;
			$this->m_queryString["IDNo"]		= $this->m_IDNo;
			$this->m_queryString["CardPw"]		= $this->m_CardPw;
			$this->m_queryString["MID"]			= $this->m_MID;
			$this->m_queryString["EncodeKey"]	= $this->m_LicenseKey;
			$this->m_queryString["MallIP"]		= $_SERVER['SERVER_NAME'];
			$this->m_queryString["actionType"]	= $this->m_ActionType;
			$this->m_queryString["PayMethod"]	= $this->m_PayMethod;
			$this->m_queryString["CancelFlg"]	= $this->m_CancelFlg;

			$NICELog->WriteLog($this->m_queryString["TID"]);

		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "ESCROW" ) {

		    // 에스크로 배송 등록
			$this->m_uri = "/lite/escrowProcess.jsp";
			unset($this->m_queryString);

			$this->m_queryString["MID"] = $this->m_MID;
			$this->m_queryString["TID"] = $this->m_TID;
			$this->m_queryString["DeliveryCoNm"] = $this->m_DeliveryCoNm;
			$this->m_queryString["InvoiceNum"] = $this->m_InvoiceNum;
			$this->m_queryString["BuyerAddr"] = $this->m_BuyerAddr;
			$this->m_queryString["RegisterName"] = $this->m_RegisterName;
			$this->m_queryString["BuyerAuthNum"] = $this->m_BuyerAuthNum;
			$this->m_queryString["ReqType"] = $this->m_ReqType;
			$this->m_queryString["MallIP"] = $_SERVER['SERVER_NAME'];
			$this->m_queryString["actionType"] = $this->m_ActionType;
			$this->m_queryString["PayMethod"] = $this->m_PayMethod;
			$this->m_queryString["EncodeKey"] = $this->m_LicenseKey;

			$this->m_queryString["ConfirmMail"] = $this->m_ConfirmMail;

			if($this->m_charSet =="UTF8"){
				$this->m_queryString["BuyerAddr"] = mb_convert_encoding($this->m_BuyerAddr, "EUC-KR", "UTF-8");
				$this->m_queryString["RegisterName"] = mb_convert_encoding($this->m_RegisterName, "EUC-KR", "UTF-8");
				$this->m_queryString["DeliveryCoNm"] = mb_convert_encoding($this->m_DeliveryCoNm, "EUC-KR", "UTF-8");
			}

			$NICELog->WriteLog($this->m_queryString["TID"]);


		// 지급 대행 서브몰 등록
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "OM_SUB_INS" ) {

		    $this->m_uri = "/lite/payproxy/subMallSetProcess.jsp";

			unset($this->m_queryString);
			$this->m_queryString = $_POST;

			$this->m_queryString["EncodeKey"] = $this->m_LicenseKey;

		// 서브몰 이체
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "OM_SUB_PAY" ) {

		    $this->m_uri = "/lite/payproxy/subMallIcheProcess.jsp";

			unset($this->m_queryString);
			$this->m_queryString = $_POST;

			$this->m_queryString["EncodeKey"] = $this->m_LicenseKey;

		// SMS
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "SMS_REQ" ) {

		    $this->m_uri = "/api/sendSmsForETAX.jsp";

			unset($this->m_queryString);
			$this->m_queryString = $_POST;

			$this->m_queryString["EncodeKey"] = $this->m_LicenseKey;

		 // 현금영수증,
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "RECEIPT" ) {


			$this->m_uri = "/lite/cashReceiptProcess.jsp";
			unset($this->m_queryString);

			$this->m_queryString["MID"]			= $this->m_MID;
			$this->m_queryString["TID"]			= $this->m_MID."04"."01".SetTimestamp1();
			$this->m_queryString["GoodsName"]	= $this->m_GoodsName;
			$this->m_queryString["BuyerName"]	= $this->m_BuyerName;
			$this->m_queryString["Amt"]			= $this->m_Amt;
			$this->m_queryString["ReceiptAmt"]	= $this->m_ReceiptAmt;
			$this->m_queryString["ReceiptSupplyAmt"] = $this->m_ReceiptSupplyAmt;
			$this->m_queryString["ReceiptVAT"]	= $this->m_ReceiptVAT;
			$this->m_queryString["ReceiptServiceAmt"] = $this->m_ReceiptServiceAmt;
			$this->m_queryString["ReceiptType"]	= $this->m_ReceiptType;
			$this->m_queryString["ReceiptTypeNo"] = $this->m_ReceiptTypeNo;
			$this->m_queryString["EncodeKey"]	= $this->m_LicenseKey;
			$this->m_queryString["actionType"]	= $this->m_ActionType;
			$this->m_queryString["PayMethod"]	= $this->m_PayMethod;
			$this->m_queryString["CancelPwd"]	= $this->m_CancelPwd;
			$this->m_queryString["CancelAmt"]	= $this->m_Amt;
			$this->m_queryString["MallIP"]		= $_SERVER['SERVER_NAME'];
			$NICELog->WriteLog($this->m_queryString["TID"]);

		// 승인인 경우,
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) != "RECEIPT" ) {

			if(trim($_POST["MID"]) == "") {
				$this->MakeErrorMsg( ERROR_WRONG_PARAMETER, "요청페이지 파라메터가 잘못되었습니다. [MID]");
				return;
			} else if (trim($_POST["Amt"]) == "" ) {
				$this->MakeErrorMsg( ERROR_WRONG_PARAMETER, "요청페이지 파라메터가 잘못되었습니다. [Amt]");
				return;

			}

			$this->m_uri = "/lite/payProcess.jsp";
			unset($this->m_queryString);

			$this->m_queryString = $_POST;
			$this->m_queryString["EncodeKey"] = $this->m_LicenseKey;
			$this->m_queryString["TID"]  = "";

			if($this->m_charSet =="UTF8"){
				$this->m_queryString["BuyerName"] = mb_convert_encoding($this->m_queryString["BuyerName"], "EUC-KR", "UTF-8");
				$this->m_queryString["GoodsName"] = mb_convert_encoding($this->m_queryString["GoodsName"], "EUC-KR", "UTF-8");
				$this->m_queryString["BuyerAddr"] = mb_convert_encoding($this->m_queryString["BuyerAddr"], "EUC-KR", "UTF-8");
			}

		}

		$httpclient = new HttpClient( $this->m_ssl );

		//connect
		if( !$httpclient->HttpConnect($NICELog) )
		{
			$NICELog->WriteLog('Server Connect Error!!' . $httpclient->getErrorMsg() );
			$resultMsg = $httpclient->getErrorMsg()."서버연결을 할 수가 없습니다.";
			if( $this->m_ssl == "true" )
			{
				$resultMsg .= "<br>귀하의 서버는 SSL통신을 지원하지 않습니다. 결제처리파일에서 m_ssl=false로 셋팅하고 시도하세오.".$httpclient->host;
				$this->MakeErrorMsg( ERR_SSLCONN, $resultMsg);
			}
			else
			{
				$this->MakeErrorMsg( ERR_CONN, $resultMsg);
			}

			$NICELog->CloseNiceLog("");

			return;
		}

		//request
		  if( !$httpclient->HttpRequest($this->m_uri, $this->m_queryString, $NICELog) ) {
			// 요청 오류시 처리
			$NICELog->WriteLog('POST Error!!' . $httpclient->getErrorMsg() );
			$this->MakeErrorMsg(ERR_NO_RESPONSE,"서버 응답 오류");
			//NET CANCEL Start---------------------------------
			if( $httpclient->getErrorCode() == READ_TIMEOUT_ERR )
			{
				$NICELog->WriteLog("Net Cancel Start" );

  			$this->m_uri = "/lite/cancelProcess.jsp";
  			unset($this->m_queryString);
  			$this->m_queryString["MID"] = substr( $this->m_TID, 0,10);
  			$this->m_queryString["TID"] = $this->m_TID;
  			$this->m_queryString["CancelAmt"] = $this->m_NetCancelAmt;
  			$this->m_queryString["CancelMsg"] = "NICE_NET_CANCEL";
  			$this->m_queryString["CancelPwd"] = $this->m_NetCancelPW;
			$this->m_queryString["NetCancelCode"] = "1";

  			$NICELog->WriteLog($this->m_queryString["TID"]);

			if( !$httpclient->HttpConnect($NICELog) )
				{
					$NICELog->WriteLog('Server Connect Error!!' . $httpclient->getErrorMsg() );
					$resultMsg = $httpclient->getErrorMsg()."서버연결을 할 수가 없습니다.";
					$this->MakeErrorMsg( ERR_CONN, $resultMsg);
					$NICELog->CloseNiceLog( $this->m_resultMsg );
					return;
				}
				if( !$httpclient->HttpRequest($this->m_uri, $this->m_queryString, $NICELog) &&
					($httpclient->getErrorCode() == READ_TIMEOUT_ERR) )
				{
					$NICELog->WriteLog("Net Cancel FAIL" );
					if( $this->m_ActionType == "PYO")
						$this->MakeErrorMsg( ERR_NO_RESPONSE, "승인여부 확인요망");
					else if( $this->m_ActionType == "CLO")
						$this->MakeErrorMsg( ERR_NO_RESPONSE, "취소여부 확인요망");
				}
				else
				{
					$NICELog->WriteLog("Net Cancel SUCESS" );
				}
			}
			//NET CANCEL End---------------------------------
			$this->ParseMsg($httpclient->getBody(),$NICELog);
			$NICELog->CloseNiceLog( $this->m_resultMsg );
			return;

		}

		if ( $httpclient->getStatus() == "200" ) {
		    $this->ParseMsg($httpclient->getBody(),$NICELog);
			$NICELog->WriteLog("TID -> "."[".$this->m_ResultData['TID']."]");
			$NICELog->WriteLog($this->m_ResultData['ResultCode']."[".$this->m_ResultData['ResultMsg']."]");
			$NICELog->CloseNiceLog("");
		}else {
			$NICELog->WriteLog('SERVER CONNECT FAIL:' . $httpclient->getStatus().$httpclient->getErrorMsg().$httpclient->getHeaders() );
			$resultMsg = $httpclient->getStatus()."서버에러가 발생했습니다.";
			$this->MakeErrorMsg( ERR_NO_RESPONSE, $resultMsg);

			//NET CANCEL Start---------------------------------
			if( $httpclient->getStatus() != 200 )
			{
				$NICELog->WriteLog("Net Cancel Start");

				//Set Field
				$this->m_uri = "/lite/cancelProcess.jsp";
				unset($this->m_queryString);
				$this->m_queryString["MID"] = substr( $this->m_TID, 0,10);
				$this->m_queryString["TID"] = $this->m_TID;
				$this->m_queryString["CancelAmt"] = $this->m_NetCancelAmt;
				$this->m_queryString["CancelMsg"] = "NICE_NET_CANCEL";
				$this->m_queryString["CancelPwd"] = $this->m_NetCancelPW;
				$this->m_queryString["NetCancelCode"] = "1";

				if( !$httpclient->HttpConnect($NICELog) )
				{
					$NICELog->WriteLog('Server Connect Error!!' . $httpclient->getErrorMsg() );
					$resultMsg = $httpclient->getErrorMsg()."서버연결을 할 수가 없습니다.";
					$this->MakeErrorMsg( ERR_CONN, $resultMsg);
					$NICELog->CloseNiceLog( $this->m_resultMsg );
					return;
				}
				if( !$httpclient->HttpRequest($this->m_uri, $this->m_queryString, $NICELog) )
				{
					$NICELog->WriteLog("Net Cancel FAIL" );
					if( $this->m_ActionType == "PYO")
						$this->MakeErrorMsg( ERR_NO_RESPONSE, "승인여부 확인요망");
					else if( $this->m_ActionType == "CLO")
						$this->MakeErrorMsg( ERR_NO_RESPONSE, "취소여부 확인요망");
				}
				else
				{
					$NICELog->WriteLog("Net Cancel SUCESS" );
				}
			}
			//NET CANCEL End---------------------------------

			$this->ParseMsg($httpclient->getBody(),$NICELog);
			$NICELog->CloseNiceLog("");
			return;
		  }
	}

	// 에러 메시지 처리
	function MakeErrorMsg($err_code, $err_msg)
	{
		$this->m_ResultCode = $err_code;
		$this->m_ResultMsg = "[".$err_code."][".$err_msg."]";
		$this->m_ResultData["ResultCode"] = $err_code;
		$this->m_ResultData["ResultMsg"] =  $err_msg;
	}

	// 결과메시지 파싱
	function ParseMsg($result_string,$NICELog) {
	    $string_arr = explode("|", trim($result_string));
	    for ($num = 0; $num < count($string_arr); $num++) {
	        $parse_str = explode("=", $string_arr[$num]);
			if($this->m_charSet =="UTF8"){
				$this->m_ResultData[$parse_str[0]] = mb_convert_encoding($parse_str[1], "UTF-8", "EUC-KR");
			}else{
				$this->m_ResultData[$parse_str[0]] = $parse_str[1];
			}
	    }
	}

}

?>
