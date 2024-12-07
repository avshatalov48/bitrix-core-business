<?php

namespace Bitrix\Sale\TradingPlatform\Ebay\Api;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\HttpClient;

class Caller
{
	protected $http;
	protected $apiUrl;

	public function __construct($params)
	{
		$this->http = new HttpClient(array(
			"version" => "1.1",
			"socketTimeout" => 60,
			"streamTimeout" => 60,
			"redirect" => true,
			"redirectMax" => 5,
		));

		if(!isset($params["COMPATIBILITY-LEVEL"]))
			$params["COMPATIBILITY-LEVEL"] = 945;

		if(!isset($params["EBAY_SITE_ID"]))
			$params["EBAY_SITE_ID"] = 215;  //RU

		if(!isset($params["URL"]))
			throw new ArgumentNullException("params[\"URL\"]");

		$this->apiUrl = $params["URL"];
		$this->http->setHeader("X-EBAY-API-COMPATIBILITY-LEVEL", $params["COMPATIBILITY-LEVEL"]);
		$this->http->setHeader("X-EBAY-API-SITEID", $params["EBAY_SITE_ID"]);
		$this->http->setHeader("Content-Type", "text/xml");
	}

	public function sendRequest($callName, $data, $devId = "", $apiAppId = "", $certId = "")
	{
		if($callName == '')
			throw new ArgumentNullException("callName");

		$this->http->setHeader("X-EBAY-API-CALL-NAME", $callName);

		if($devId <> '')
			$this->http->setHeader("X-EBAY-API-DEV-NAME", $devId);

		if($apiAppId <> '')
			$this->http->setHeader("X-EBAY-API-APP-NAME", $apiAppId);

		if($certId <> '')
			$this->http->setHeader("X-EBAY-API-CERT-NAME", $certId);

		$result = @$this->http->post($this->apiUrl, $data);
		$errors = $this->http->getError();

		if (!$result && !empty($errors))
		{
			$strError = "";

			foreach($errors as $errorCode => $errMes)
				$strError .= $errorCode.": ".$errMes;

			throw new SystemException($strError);
		}
		else
		{
			$status = $this->http->getStatus();

			if ($status != 200)
				throw new SystemException(sprintf('HTTP error code: %d', $status));
		}

		return $result;
	}
} 