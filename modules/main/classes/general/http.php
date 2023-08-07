<?php

use Bitrix\Main;
use Bitrix\Main\Web;

class CHTTP
{
	var $url = '';
	var $status = 0;
	var $result = '';
	var $fp = null;
	var $headers = array();
	var $cookies = array();
	var $http_timeout = 30;
	var $user_agent;
	var $follow_redirect = false;
	var $errno;
	var $errstr;
	var $additional_headers = array();

	private $redirectMax = 5;
	private $redirectsMade = 0;
	private static $lastSetStatus = "";

	public function __construct()
	{
		$defaultOptions = \Bitrix\Main\Config\Configuration::getValue("http_client_options");
		if(isset($defaultOptions["socketTimeout"]))
		{
			$this->http_timeout = intval($defaultOptions["socketTimeout"]);
		}

		$this->user_agent = 'BitrixSM ' . __CLASS__ . ' class';
	}

	/**
	 * @deprecated Use \Bitrix\Main\Web\Uri::toAbsolute().
	 */
	public static function URN2URI($urn, $server_name = '')
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if(preg_match("/^[a-z]+:\\/\\//", $urn))
		{
			$uri = $urn;
		}
		else
		{
			if($APPLICATION->IsHTTPS())
				$proto = "https://";
			else
				$proto = "http://";

			if($server_name <> '')
				$server_name = preg_replace("/:(443|80)$/", "", $server_name);
			else
				$server_name = preg_replace("/:(443|80)$/", "", $_SERVER["HTTP_HOST"]);

			$uri = $proto.$server_name.$urn;
		}
		return $uri;
	}

	public function Download($url, $file)
	{
		if (is_resource($file))
		{
			$this->fp = $file;
		}
		else
		{
			CheckDirPath($file);
			$this->fp = fopen($file, "wb");
		}

		if(is_resource($this->fp))
		{
			$res = $this->HTTPQuery('GET', $url);

			if (!is_resource($file))
			{
				fclose($this->fp);
				$this->fp = null;
			}

			return $res && ($this->status == 200);
		}
		return false;
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 */
	public function Get($url)
	{
		if ($this->HTTPQuery('GET', $url))
		{
			return $this->result;
		}
		return false;
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 */
	public function Post($url, $arPostData)
	{
		$postdata = static::PrepareData($arPostData);

		if($this->HTTPQuery('POST', $url, $postdata))
		{
			return $this->result;
		}
		return false;
	}

	public static function PrepareData($arPostData, $prefix = '')
	{
		$str = '';

		if(!is_array($arPostData))
		{
			$str = $arPostData;
		}
		else
		{
			foreach ($arPostData as $key => $value)
			{
				$name = $prefix == "" ? urlencode($key) : $prefix."[".urlencode($key)."]";

				if(is_array($value))
				{
					$str .= static::PrepareData($value, $name);
				}
				else
				{
					$str .= '&'.$name.'='.urlencode($value);
				}
			}
		}

		if($prefix == '' && mb_substr($str, 0, 1) == '&')
		{
			$str = mb_substr($str, 1);
		}

		return $str;
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 */
	public function HTTPQuery($method, $url, $postdata = '')
	{
		if(is_resource($this->fp))
			$file_pos = ftell($this->fp);

		$this->redirectsMade = 0;

		while (true)
		{
			$this->url = $url;
			$arUrl = $this->ParseURL($url);
			if (!$this->Query($method, $arUrl['host'], $arUrl['port'], $arUrl['path_query'], $postdata, $arUrl['proto']))
			{
				return false;
			}

			if(
				$this->follow_redirect
				&& isset($this->headers['Location'])
				&& $this->headers['Location'] <> ''
			)
			{
				$url = $this->headers['Location'];
				if($this->redirectsMade < $this->redirectMax)
				{
					//When writing to file we have to discard
					//redirect body
					if(is_resource($this->fp))
					{
						/** @noinspection PhpUndefinedVariableInspection */
						ftruncate($this->fp, $file_pos);
						fseek($this->fp, $file_pos, SEEK_SET);
					}
					$this->redirectsMade++;
					continue;
				}
				else
				{
					trigger_error("Maximum number of redirects (".$this->redirectMax.") has been reached at URL ".$url, E_USER_WARNING);
					return false;
				}
			}
			else
			{
				break;
			}
		}
		return true;
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 */
	public function Query($method, $host, $port, $path, $postdata = false, $proto = '', $post_content_type = 'N', $dont_wait_answer = false)
	{
		$this->status = 0;
		$this->result = '';
		$this->headers = array();
		$this->cookies = array();
		$fp = fsockopen($proto.$host, $port, $this->errno, $this->errstr, $this->http_timeout);
		if ($fp)
		{
			$strRequest = "$method $path HTTP/1.0\r\n";
			$strRequest .= "Connection: close\r\n";
			$strRequest .= "User-Agent: {$this->user_agent}\r\n";
			$strRequest .= "Accept: */*\r\n";
			$strRequest .= "Host: $host\r\n";
			$strRequest .= "Accept-Language: en\r\n";

			foreach ($this->additional_headers as $key => $value)
				$strRequest .= $key.": ".$value."\r\n";

			if ($method == 'POST' || $method == 'PUT')
			{
				if ('N' !== $post_content_type)
					$strRequest .= $post_content_type == '' ? '' : "Content-type: ".$post_content_type."\r\n";
				else
					$strRequest.= "Content-type: application/x-www-form-urlencoded\r\n";

				if(!array_key_exists("Content-Length", $this->additional_headers))
					$strRequest.= "Content-Length: ".strlen($postdata) . "\r\n";
			}
			$strRequest .= "\r\n";
			fwrite($fp, $strRequest);

			if ($method == 'POST' || $method == 'PUT')
			{
				if(is_resource($postdata))
				{
					while(!feof($postdata))
						fwrite($fp, fread($postdata, 1024*1024));
				}
				else
				{
					fwrite($fp, $postdata);
				}
			}

			if ($dont_wait_answer)
			{
				fclose($fp);
				return true;
			}

			$headers = "";
			while(!feof($fp))
			{
				$line = fgets($fp, 4096);
				if($line == "\r\n" || $line === false)
				{
					//$line = fgets($fp, 4096);
					break;
				}
				$headers .= $line;
			}
			$this->ParseHeaders($headers);

			if(is_resource($this->fp))
			{
				while(!feof($fp))
				{
					$buf = fread($fp, 40960);
					if ($buf === false)
						break;
					fwrite($this->fp, $buf);
					fflush($this->fp);
				}
			}
			else
			{
				$this->result = "";
				while(!feof($fp))
				{
					$buf = fread($fp, 4096);
					if ($buf === false)
						break;
					$this->result .= $buf;
				}
			}

			fclose($fp);

			return true;
		}

		/** @global CMain $APPLICATION */
		global $APPLICATION;
		$APPLICATION->ThrowException(
			sprintf("Error connecting to %s:%s. Error code: \"%s\", error description: \"%s\"",
				$this->errstr,
				$this->errno,
				$host,
				$port
			)
		);
		return false;
	}

	public function SetAuthBasic($user, $pass)
	{
		$this->additional_headers['Authorization'] = "Basic ".base64_encode($user.":".$pass);
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\Uri
	 */
	public static function ParseURL($url)
	{
		$arUrl = parse_url($url);

		$arUrl['proto'] = '';
		if (array_key_exists('scheme', $arUrl))
		{
			$arUrl['scheme'] = mb_strtolower($arUrl['scheme']);
		}
		else
		{
			$arUrl['scheme'] = 'http';
		}

		if (!array_key_exists('port', $arUrl))
		{
			if ($arUrl['scheme'] == 'https')
			{
				$arUrl['port'] = 443;
			}
			else
			{
				$arUrl['port'] = 80;
			}
		}

		if ($arUrl['scheme'] == 'https')
		{
			$arUrl['proto'] = 'ssl://';
		}

		$arUrl['path_query'] = array_key_exists('path', $arUrl) ? $arUrl['path'] : '/';
		if (array_key_exists('query', $arUrl) && $arUrl['query'] <> '')
		{
			$arUrl['path_query'] .= '?' . $arUrl['query'];
		}

		return $arUrl;
	}

	public function ParseHeaders($strHeaders)
	{
		$arHeaders = explode("\n", $strHeaders);
		foreach ($arHeaders as $k => $header)
		{
			if ($k == 0)
			{
				if (preg_match(',HTTP\S+ (\d+),', $header, $arFind))
				{
					$this->status = intval($arFind[1]);
				}
			}
			elseif(strpos($header, ':') !== false)
			{
				$arHeader = explode(':', $header, 2);
				if ($arHeader[0] == 'Set-Cookie')
				{
					if (($pos = mb_strpos($arHeader[1], ';')) !== false && $pos > 0)
					{
						$cookie = trim(mb_substr($arHeader[1], 0, $pos));
					}
					else
					{
						$cookie = trim($arHeader[1]);
					}
					$arCookie = explode('=', $cookie, 2);
					$this->cookies[$arCookie[0]] = rawurldecode($arCookie[1]);
				}
				else
				{
					$this->headers[$arHeader[0]] = trim($arHeader[1]);
				}
			}
		}
	}

	public function setFollowRedirect($follow)
	{
		$this->follow_redirect = $follow;
	}

	public function setRedirectMax($n)
	{
		$this->redirectMax = $n;
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 */
	public static function sGet($url, $follow_redirect = false) //static get
	{
		$ob = new CHTTP();
		$ob->setFollowRedirect($follow_redirect);
		return $ob->Get($url);
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 */
	public static function sPost($url, $arPostData, $follow_redirect = false) //static post
	{
		$ob = new CHTTP();
		$ob->setFollowRedirect($follow_redirect);
		return $ob->Post($url, $arPostData);
	}

	public function SetAdditionalHeaders($arHeader=array())
	{
		foreach($arHeader as $name => $value)
		{
			$name = str_replace(array("\r","\n"), "", $name);
			$value = str_replace(array("\r","\n"), "", $value);
			$this->additional_headers[$name] = $value;
		}
	}

	/** Static Get with the ability to add headers and set the http timeout
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 * @static
	 * @param $url
	 * @param array $arHeader
	 * @param int $httpTimeout
	 * @return bool|string
	 */
	public static function sGetHeader($url, $arHeader = array(), $httpTimeout = 0)
	{
		$httpTimeout = intval($httpTimeout);
		$ob = new CHTTP();
		if(!empty($arHeader))
			$ob->SetAdditionalHeaders($arHeader);
		if($httpTimeout > 0)
			$ob->http_timeout = $httpTimeout;

		return $ob->Get($url);
	}

	/** Static Post with the ability to add headers and set the http timeout
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 * @static
	 * @param $url
	 * @param $arPostData
	 * @param array $arHeader
	 * @param int $http_timeout
	 * @return bool|string
	 */
	public static function sPostHeader($url, $arPostData, $arHeader = array(), $http_timeout = 0)
	{
		$http_timeout = intval($http_timeout);
		$ob = new CHTTP();
		if(!empty($arHeader))
			$ob->SetAdditionalHeaders($arHeader);
		if($http_timeout > 0)
			$ob->http_timeout = $http_timeout;
		return $ob->Post($url, $arPostData);
	}

	public static function SetStatus($status)
	{
		$bCgi = (stristr(php_sapi_name(), "cgi") !== false);
		if($bCgi && (!defined("BX_HTTP_STATUS") || BX_HTTP_STATUS == false))
			header("Status: ".$status);
		else
			header($_SERVER["SERVER_PROTOCOL"]." ".$status);
		self::$lastSetStatus = $status;
	}

	public static function GetLastStatus()
	{
		return self::$lastSetStatus;
	}

	public static function SetAuthHeader($bDigestEnabled=true)
	{
		self::SetStatus('401 Unauthorized');

		if(defined('BX_HTTP_AUTH_REALM'))
			$realm = BX_HTTP_AUTH_REALM;
		else
			$realm = "Bitrix Site Manager";

		header('WWW-Authenticate: Basic realm="'.$realm.'"');

		if($bDigestEnabled !== false && COption::GetOptionString("main", "use_digest_auth", "N") == "Y")
		{
			// On first try we found that we don't know user digest hash. Let ask only Basic auth first.
			if(\Bitrix\Main\Application::getInstance()->getKernelSession()->get("BX_HTTP_DIGEST_ABSENT") !== true)
				header('WWW-Authenticate: Digest realm="'.$realm.'", nonce="'.uniqid().'"');
		}
	}

	/*
	 * @deprecated Use \Bitrix\Main\Server::parseAuthRequest()
	 */
	public static function ParseAuthRequest()
	{
		return Main\Context::getCurrent()->getServer()->parseAuthRequest();
	}

	/**
	 * @deprecated Use \Bitrix\Main\Web\Uri::addParams().
	 */
	public static function urlAddParams($url, $add_params, $options = array())
	{
		if(!empty($add_params))
		{
			$params = array();
			foreach($add_params as $name => $value)
			{
				if(($options["skip_empty"] ?? false) && (string)$value == '')
					continue;
				if(($options["encode"] ?? false))
					$params[] = urlencode($name).'='.urlencode($value);
				else
					$params[] = $name.'='.$value;
			}

			if(!empty($params))
			{
				$p1 = mb_strpos($url, "?");
				if($p1 === false)
					$ch = "?";
				else
					$ch = "&";

				$p2 = mb_strpos($url, "#");
				if($p2===false)
				{
					$url = $url.$ch.implode("&", $params);
				}
				else
				{
					$url = mb_substr($url, 0, $p2).$ch.implode("&", $params).mb_substr($url, $p2);
				}
			}
		}
		return $url;
	}

	/**
	 * @deprecated Use \Bitrix\Main\Web\Uri::deleteParams().
	 */
	public static function urlDeleteParams($url, $delete_params, $options = array())
	{
		$url_parts = explode("?", $url, 2);
		if(count($url_parts) == 2 && $url_parts[1] <> '')
		{
			if(($options["delete_system_params"] ?? false))
				$delete_params = array_merge($delete_params, \Bitrix\Main\HttpRequest::getSystemParameters());

			$params_pairs = explode("&", $url_parts[1]);
			foreach($params_pairs as $i => $param_pair)
			{
				$name_value_pair = explode("=", $param_pair, 2);
				if(count($name_value_pair) == 2 && in_array($name_value_pair[0], $delete_params))
					unset($params_pairs[$i]);
			}

			if(empty($params_pairs))
				return $url_parts[0];
			else
				return $url_parts[0]."?".implode("&", $params_pairs);
		}

		return $url;
	}

	/**
	 * @deprecated Use \Bitrix\Main\Web\Uri::urnEncode().
	 */
	public static function urnEncode($str, $charset = false)
	{
		return Web\Uri::urnEncode($str, $charset);
	}

	/**
	 * @deprecated Use \Bitrix\Main\Web\Uri::urnDecode().
	 */
	public static function urnDecode($str, $charset = false)
	{
		return Web\Uri::urnDecode($str, $charset);
	}

	/**
	 * @deprecated Use \Bitrix\Main\Web\Uri::isPathTraversal().
	 */
	public static function isPathTraversalUri($url)
	{
		$uri = new Web\Uri($url);
		return $uri->isPathTraversal();
	}
}
