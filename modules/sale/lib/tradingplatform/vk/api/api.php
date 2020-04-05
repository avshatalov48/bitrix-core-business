<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Api;

use Bitrix\Main\ArgumentNullException;

use Bitrix\Sale\TradingPlatform\Vk;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

/**
 * Class Api
 * Work with VK API through http requsts
 * @package Bitrix\Sale\TradingPlatform\Vk\Api
 */
class Api
{
	private $accessToken = NULL;
	public static $apiUrl = 'https://api.vk.com/method/';
	public static $apiVersion = "5.52";
	private $exportId;
	private $response;
	
	const TOO_MANY_REQUESTS_ERROR_CODE = 6;
	
	/**
	 * Api constructor.
	 * @param $accessToken - string of accesstoken from VK
	 * @param $exportId - int
	 * @throws ArgumentNullException
	 */
	public function __construct($accessToken, $exportId)
	{
		$this->exportId = $exportId;
		$this->response = array();
		
		if ($accessToken)
		{
			$this->accessToken = $accessToken;
		}
		else
		{
			throw new ArgumentNullException('accessToken');
		}
	}
	
	/**
	 * Send a request to single VK API method with params.
	 *
	 * @param string $method - Name of VK-API method (see VK manual).
	 * @param array $params
	 * @return mixed|null
	 */
	public function run($method, $params = array())
	{
		$params['access_token'] = $this->accessToken;
		$params['v'] = self::$apiVersion;
		$url = self::$apiUrl . $method;
		
		$http = new HttpClient();
		$responseStr = $http->post($url, $params);
		
		if (!is_string($responseStr))
		{
			return NULL;
		}
		
		$this->response = Json::decode($responseStr);
		$this->checkError($method, $params);
		
		return $this->response['response'];
	}
	
	/**
	 * Parse response string from VK and find errors.
	 * If find errors - add them to vk-log
	 *
	 * @param $response
	 * @param $params - array of request params
	 * @return null
	 */
	private function checkError($method, $params)
	{
//		check limit of requests count. If limit catched - run again
		if ($this->checkRequestsLimit())
		{
			return $this->run($method, $params);
		}
//		FATAL errors - stop running
		if (isset($this->response["error"]))
		{
			$logger = new Vk\Logger($this->exportId);
			$logger->addLog(
				'Catch error in method ' . $method,
				array('ERROR' => $this->response["error"] . ' - ' . $this->response["error_msg"], "PARAMS" => $params)
			);
			$logger->addError($this->response["error"]["error_code"], $method);
			
			throw new Vk\ExecuteException("VK_critical_execution_error " . $this->response["error"]["error_code"] . " in method " . $method);
		}

//		EXECUTE errors can be fatal or not critical
		if (isset($this->response["execute_errors"]))
		{
			$logger = new Vk\Logger($this->exportId);
			foreach ($this->response["execute_errors"] as $er)
			{
				$logger->addLog(
						'Execute error in method ' . $method,
						array('ERROR' => $er["error_code"] . ' (' . $er["method"] . ') - ' . $er["error_msg"], "PARAMS" => $params,
							"RESPONSE" => $this->response)
				);
				$logger->addError($er["error_code"]);
			}
		}
		
		return NULL;
	}
	
	
	private function checkRequestsLimit()
	{
//		we can do only LIMIT count of requests per second. If catched error - wait one second, clear error and do next
		if (isset($this->response["error"]) && $this->response["error"]["error_code"] == self::TOO_MANY_REQUESTS_ERROR_CODE)
		{
			sleep(1);
			$this->response = array();
			
			return true;
		}
		
		return false;
	}
	
}