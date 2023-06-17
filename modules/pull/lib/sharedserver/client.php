<?php

namespace Bitrix\Pull\SharedServer;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class Client
{
	const TYPE_CP = "CP";

	public static function register($preferredServer = "", array $options = []): Result
	{
		$result = new Result();
		if($preferredServer == "")
		{
			$serverAddressResult = static::selectServer();
			if(!$serverAddressResult->isSuccess())
			{
				Config::setRegistered(false);
				return $result->addErrors($serverAddressResult->getErrors());
			}
			$preferredServer = $serverAddressResult->getData()['hostname'];
		}
		Config::setServerAddress($preferredServer);
		$endpoint = $options['endpoint'] ?? "";
		$registerResult = static::performRegister($endpoint);
		if(!$registerResult->isSuccess())
		{
			Config::setRegistered(false);
			return $result->addErrors($registerResult->getErrors());
		}

		$registrationData = $registerResult->getData();

		Config::setSignatureKey($registrationData["securityKey"]);
		Config::setRegistered(true);

		return $result;
	}

	public static function selectServer(): Result
	{
		$result = new Result();
		$httpClient = new HttpClient();
		$response = $httpClient->get(Config::getDefaultCloudServer() . Config::HOSTNAME_URL);
		if(!$response)
		{
			$errors = $httpClient->getError();
			foreach ($errors as $code => $message)
			{
				$result->addError(new Error($message, $code));
			}
			return $result;
		}
		if ($httpClient->getStatus() != 200)
		{
			return $result->addError(new Error("Unexpected server response code " . $httpClient->getStatus(), "WRONG_RESPONSE_CODE"));
		}
		$result->setData([
			'hostname' => $response
		]);
		return $result;
	}

	public static function getServerList(): Result
	{
		$result = new Result();
		$httpClient = new HttpClient([
			"socketTimeout" => 5,
			"streamTimeout" => 5
		]);
		$response = $httpClient->get(Config::getDefaultCloudServer() . Config::SERVER_LIST_URL);
		if(!$response)
		{
			$errors = $httpClient->getError();
			foreach ($errors as $code => $message)
			{
				$result->addError(new Error($message, $code));
			}
			return $result;
		}
		if ($httpClient->getStatus() != 200)
		{
			return $result->addError(new Error("Unexpected server response code " . $httpClient->getStatus(), "WRONG_RESPONSE_CODE"));
		}
		$list = explode("\n", $response);
		$list = array_filter($list, function($a){return $a != "";});
		$list = array_map(
			function($a){
				list($url, $region) = explode(";", $a);
				return [
					'url' => $url,
					'region' => $region
				];
			},
			$list
		);

		$result->setData([
			'serverList' => $list
		]);
		return $result;
	}

	protected static function performRegister(string $licenseServerEndpoint = ""): Result
	{
		$result = new Result();
		$params = [
			"BX_LICENCE" => static::getPublicLicenseCode(),
			"BX_TYPE" => static::TYPE_CP,
		];
		$params["BX_HASH"] = static::signRequest($params);
		$params["BX_ALL"] = "y";

		$request = [
			"verificationQuery" => http_build_query($params)
		];

		$httpClient = new HttpClient([
			"disableSslVerification" => true
		]);

		$licenseServerEndpoint = $licenseServerEndpoint ?: Config::getRegisterUrl();
		$queryResult = $httpClient->query(HttpClient::HTTP_POST, $licenseServerEndpoint, $request);

		if(!$queryResult)
		{
			$errors = $httpClient->getError();
			foreach ($errors as $code => $message)
			{
				$result->addError(new Error($message, $code));
			}
			return $result;
		}
		$returnCode = $httpClient->getStatus();
		$response = $httpClient->getResult();
		if ($returnCode != 200)
		{
			try
			{
				$parsedResponse = Json::decode($response);
				$result->addError(new Error($parsedResponse["error"]));
			}
			catch (\Exception $e)
			{
				$result->addError(new Error("Server returned " . $returnCode . " code", "WRONG_SERVER_RESPONSE"));
			}

			return $result;
		}

		if($response == "")
		{
			$result->addError(new Error("Empty server response", "EMPTY_SERVER_RESPONSE"));
			return $result;
		}

		try
		{
			$parsedResponse = Json::decode($response);
		}
		catch (\Exception $e)
		{
			$result->addError(new Error("Could not parse server response. Raw response: " . $response));
			return $result;
		}

		if($parsedResponse["status"] === "error")
		{
			$result->addError(new Error($parsedResponse["error"]));
		}
		else
		{
			$result->setData($parsedResponse);
		}

		return $result;
	}

	public static function getPublicLicenseCode(): string
	{
		return Application::getInstance()->getLicense()->getPublicHashKey();
	}

	protected static function signRequest(array $request): string
	{
		$paramStr = md5(implode("|", $request));
		return md5($paramStr . Application::getInstance()->getLicense()->getHashLicenseKey());
	}
}
