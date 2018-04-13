<?php

namespace Modules\Shop\Payments\Gateways\Nicepay;

/*____________________________________________________________

Copyright (C) 2016 NICE IT&T
*
* 해당 라이브러리는 수정하시는경우 승인및 취소에 문제가 발생할 수 있습니다.
* 임의로 수정된 코드에 대한 책임은 전적으로 수정자에게 있음을 알려드립니다.

*	@ description		: 주요 변수 설정.
*	@ name				: NicepayLiteCommon.php
*	@ auther			: NICEPAY I&T (tech@nicepay.co.kr)
*	@ date				:
*	@ modify


*____________________________________________________________
*/

/*HTTP SERVER INFO*/
//----------------
//PRODUCTION
//----------------

/*LOG LEVEL*/
define("CRITICAL", 1);
define("ERROR", 2);
define("NOTICE", 3);
define("INFO", 5);
define("DEBUG", 7);

/*HTTP CALL ERROR CODE*/

/*NicepayLite ERROR CODE*/
define("ERR_WRONG_HOME", "PL01");
define("ERR_OPENLOG", "PL02");
define("ERR_SSLCONN", "PL03");
define("ERR_CONN", "PL04");
define("READ_TIMEOUT_ERR", "PL05");
define("ERR_WRONG_ACTIONTYPE", "PL10");
define("ERR_WRONG_PARAMETER", "PL11");
define("ERR_MAKE_PLAINTEXT", "PL20");
define("ERR_FAIL_TRANSPORT", "PL30");
define("ERR_NO_RESPONSE", "PL40");

/*-----------------------------------------------------*/
/* Global Function                                     */
/*-----------------------------------------------------*/
function Base64Encode( $str )
{
  return substr(chunk_split(base64_encode( $str ),64,"\n"),0,-1)."\n";
}
function GetMicroTime()
{
    list($usec, $sec) = explode(" ", microtime(true));
    return (float)$usec + (float)$sec;
}
function SetTimestamp()
{
    $m = explode(' ',microtime());
    list($totalSeconds, $extraMilliseconds) = array($m[1], (int)round($m[0]*1000,3));
    return date("Y-m-d H:i:s", $totalSeconds) . ":$extraMilliseconds";
}
function SetTimestamp1()
{
    $m = explode(' ',microtime());
    list($totalSeconds, $extraMilliseconds) = array($m[1], (int)round($m[0]*10000,4));
    return date("ymdHis", $totalSeconds) . "$extraMilliseconds";
}

function genTID($mid,$svcCd,$svcPrdtCd){

		$buffer = "";
		$nanotime = microtime(true);

		$nanoString = str_replace(".","",$nanotime,strlen($nanotime));

		$nanoStrLength = strlen($nanoString);

		$yyyyMMddHHmmss = date("YmdHis");

		$appendNanoStr = substr($nanoString,10,1);

		$buffer = $mid;
		$buffer .= $svcCd;
		$buffer .= $svcPrdtCd;

		$buffer .= substr($yyyyMMddHHmmss,2,strlen($yyyyMMddHHmmss));
		$buffer .= $appendNanoStr;

		$buffer .= rand(0, 9);
		$buffer .= rand(0, 9);
		$buffer .= rand(0, 9);
		return $buffer;
	}


/*-----------------------------------------------------*/
/* Http Proxy Class		                               */
/* HTTP												   */
/* HTTPS( PHP5.1.4 & OpenSSL 필요)               	   */
/*-----------------------------------------------------*/
class HttpClient
{
    const HTTP_SERVER = "web.nicepay.co.kr";
    const HTTP_PORT = 80;

    const HTTP_SSL_SERVER = "web.nicepay.co.kr";
    const HTTP_SSL_PORT = 443;

    /*TIMEOUT*/
    const TIMEOUT_CONNECT = 5;
    const TIMEOUT_READ = 25;

    public $sock = 0;
    public $host;
    public $port;
    public $ssl;
    public $status;
    public $headers="";
    public $body="";
    public $reqeust;
	public $errorcode;
	public $errormsg;


    public function __construct($ssl)
	{
		if( $ssl == "true" )
		{
            $this->host = static::HTTP_SSL_SERVER;
            $this->port = static::HTTP_SSL_PORT;
            $this->ssl = "ssl://";
		}
        else
        {
            $this->host = static::HTTP_SERVER;
            $this->port = static::HTTP_PORT;
        }
    }

	function HttpConnect($NICELog)
	{
	    $NICELog->WriteLog("Connect to ".$this->ssl.$this->host.":".$this->port );
        if (!$this->sock = @fsockopen( $this->ssl.$this->host, $this->port, $errno, $errstr, static::TIMEOUT_CONNECT))
		{
			$this->errorcode = $errno;
            switch($errno)
			{
                case -3:
                    $this->errormsg = 'Socket creation failed (-3)';
                case -4:
                    $this->errormsg = 'DNS lookup failure (-4)';
                case -5:
                    $this->errormsg = 'Connection refused or timed out (-5)';
                default:
                    $this->errormsg = 'Connection failed ('.$errno.')';
                $this->errormsg .= ' '.$errstr;
            }
			return false;
        }
		$NICELog->WriteLog($this->ssl.$this->host.":".$this->port." Server Connect OK" );
		return true;
	}

	function HttpRequest($uri, $data, $NICELog)
	{
    	$this->headers="";
    	$this->body="";

		$postdata = $this->buildQueryString($data);

		/*Write*/
		$request  = "POST ".$uri." HTTP/1.0\r\n";
		$request .= "Connection: close\r\n";
		$request .= "Host: ".$this->host."\r\n";
		$request .= "Content-type: application/x-www-form-urlencoded\r\n";
		$request .= "Content-length: ".strlen($postdata)."\r\n";
		$request .= "Accept: */*\r\n";
		$request .= "\r\n";
		$request .= $postdata."\r\n";
		$request .= "\r\n";
		fwrite($this->sock, $request);

		$NICELog->WriteLog("MSG_TO_SVR::[".$uri."]" );

		/*Read*/
		stream_set_blocking($this->sock, FALSE );

		$atStart = true;
		$IsHeader = true;
		$timeout = false;
		$start_time= time();
		while ( !feof($this->sock) && !$timeout )
		{
			$line = fgets($this->sock, 4096);
			$diff=time()-$start_time;
			if( $diff >= static::TIMEOUT_READ )
			{
				$timeout = true;
			}
			if( $IsHeader )
			{
				if( $line == "" ) //for stream_set_blocking
				{
					continue;
				}
				if( substr( $line, 0, 2 ) == "\r\n" )  //end of header
				{
					$IsHeader = false;
					continue;
				}
  				$this->headers .= $line;
            	if ($atStart)
				{
                	$atStart = false;
                	if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m))
					{
                    	$this->errormsg = "Status code line invalid: ".htmlentities($line);
						fclose( $this->sock );
                    	return false;
                	}
                	$http_version = $m[1];
                	$this->status = $m[2];
                	$status_string = $m[3];
                	continue;
				}
            }
			else
			{
  				$this->body .= $line;
			}
		}
		fclose( $this->sock );

		if( $timeout )
		{
			$this->errorcode = READ_TIMEOUT_ERR;
            $this->errormsg = "Socket Timeout(".$diff."SEC)";
			$NICELog->WriteLog($this->errormsg );
			return false;
		}

		return true;
	}

    function buildQueryString($data)
	{
        $querystring = '';
        if (is_array($data))
		{
            foreach ($data as $key => $val)
			{
                if (is_array($val))
				{
                    foreach ($val as $val2)
					{
												if( $key != "key" )
                        	$querystring .= urlencode($key).'='.urlencode($val2).'&';
                    }
                }
				else
				{
										if( $key != "key" )
                    	$querystring .= urlencode($key).'='.urlencode($val).'&';
                }
            }
            $querystring = substr($querystring, 0, -1);
        }
		else
		{
            $querystring = $data;
        }
        return $querystring;
    }
	function NetCancel()
	{
		return true;
	}
    function getStatus()
	{
        return $this->status;
    }
    function getBody()
	{
        return $this->body;
    }
    function getHeaders()
	{
        return $this->headers;
    }
    function getErrorMsg()
	{
        return $this->errormsg;
    }
    function getErrorCode()
	{
        return $this->errorcode;
    }



}

?>
