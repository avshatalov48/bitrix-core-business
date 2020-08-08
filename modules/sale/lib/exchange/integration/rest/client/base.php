<?php
namespace Bitrix\Sale\Exchange\Integration\Rest\Client;

use Bitrix\Main\Application;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Exchange\Integration\OAuth;

class Base
{
	const LOG_DIR = '/bitrix/modules/sale/lib/exchange/integration/log';
	const LOG_PATH = 'rest_client.log';

	protected $accessToken;
	protected $refreshToken;
	protected $endPoint;
	protected $expiresIn;

	public function __construct(array $settings)
	{
		if (isset($settings["accessToken"]))
		{
			$this->setAccessToken($settings["accessToken"]);
		}

		if (isset($settings["refreshToken"]))
		{
			$this->setRefreshToken($settings["refreshToken"]);
		}

		if (isset($settings["endPoint"]))
		{
			$this->setEndPoint($settings["endPoint"]);
		}
	}

	public function getAccessToken()
	{
		return $this->accessToken;
	}

	public function setAccessToken($accessToken)
	{
		if (is_string($accessToken) && !empty($accessToken))
		{
			$this->accessToken = $accessToken;
		}
	}

	public function getRefreshToken()
	{
		return $this->refreshToken;
	}

	public function setRefreshToken($refreshToken)
	{
		if (is_string($refreshToken) && !empty($refreshToken))
		{
			$this->refreshToken = $refreshToken;
		}
	}

	public function getEndPoint()
	{
		return $this->endPoint;
	}

	public function setEndPoint($endPoint)
	{
		if (is_string($endPoint) && !empty($endPoint))
		{
			$this->endPoint = $endPoint;
		}
	}

	public function getExpiresIn()
	{
		return $this->expiresIn;
	}

	public function setExpiresIn($expiresIn)
	{
		$this->expiresIn = $expiresIn;
	}

	public function call($method, $params = [])
	{
		$response = $this->makeRequest($method, $params);

		if (isset($response["error"]) && $response["error"] === "expired_token")
		{
			if ($this->refreshAccessToken())
			{
				$response = $this->makeRequest($method, $params);
			}
		}

		return $response;
	}

	protected function makeRequest($method, $params = [])
	{
		$accessToken = $this->getAccessToken();
		if ($accessToken === null)
		{
			throw new ObjectPropertyException("Access Token must be set.");
		}

		$endPoint = $this->getEndPoint();
		if ($endPoint === null)
		{
			throw new ObjectPropertyException("End Point URL must be set.");
		}

		$httpClient = new HttpClient();
		$httpClient->setHeader("User-Agent", "Bitrix Integration B24");
		$httpClient->setCharset("UTF-8");

		$params["auth"] = $accessToken;
		if (!Application::getInstance()->isUtfMode())
		{
			$params = Encoding::convertEncoding($params, SITE_CHARSET, "UTF-8");
		}

		$this->log("\\----------\n");
		$this->log(['endpoint'=>$endPoint.$method,'params'=>$params]);
		$this->log([$endPoint.$method.'?'.http_build_query($params)]);

		$success = $httpClient->post($endPoint.$method, $params);
		if (!$success)
		{
			throw new SystemException("Wrong Rest Response. ".$endPoint.$method);
		}

		$result = $httpClient->getResult();

		$this->log(['result'=>$result]);
		$this->log("\n ----------//\n");

		try
		{
			$response = Json::decode($result);
		}
		catch (\Exception $exception)
		{
			throw new SystemException(
				"Wrong Rest Response. ".$endPoint.$method."\n\n".mb_substr($result, 0, 1024)
			);
		}

		return $response;
	}

	protected function refreshAccessToken()
	{
		$refreshToken = $this->getRefreshToken();
		if ($refreshToken !== null)
		{
			$oauthClient = new OAuth\Bitrix24();
			$response = $oauthClient->getAccessToken(
				"refresh_token",
				["refresh_token" => $this->refreshToken]
			);

			if (!isset($response["error"]) && is_array($response))
			{
				$this->setAccessToken($response["access_token"]);
				$this->setRefreshToken($response["refresh_token"]);

				return true;
			}
		}

		return false;
	}

	/**
	 * @param $params
	 * @return void
	 */
	protected function log($params)
	{
		if($this->isOnLog() == false)
		{
			return;
		}

		$dir = $_SERVER['DOCUMENT_ROOT'].static::LOG_DIR;
		if(is_dir($dir) || @mkdir($dir, BX_DIR_PERMISSIONS))
		{
			$f = fopen($dir.'/'.static::LOG_PATH, "a+");
			fwrite($f, print_r($params, true));
			fclose($f);
		}
	}

	protected function isOnLog()
	{
		return \Bitrix\Main\Config\Option::get("sale", "log_integration_b24_rest_client", 'N') == 'Y';
	}
}