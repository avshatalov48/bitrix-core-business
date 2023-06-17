<?php

namespace Bitrix\Main\Service\MicroService;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

/**
 * Class BaseSender
 */
abstract class BaseSender
{
	public function __construct()
	{
	}

	/**
	 * @param string $action
	 * @param array $parameters
	 * @return Result
	 * @throws ArgumentException
	 */
	public function performRequest($action, array $parameters = []): Result
	{
		$httpClient = $this->buildHttpClient();

		$url = $this->getServiceUrl() . "/api/?action=".$action;

		$request = [
			"action" => $action,
			"serializedParameters" => base64_encode(gzencode(Json::encode($parameters))),
		];

		$request["BX_TYPE"] = Client::getPortalType();
		$request["BX_LICENCE"] = Client::getLicenseCode();
		$request["SERVER_NAME"] = Client::getServerName();
		$request["BX_HASH"] = Client::signRequest($request);

		$result = $httpClient->query(HttpClient::HTTP_POST, $url, $request);

		return $this->buildResult($httpClient, $result);
	}

	protected function buildHttpClient(): HttpClient
	{
		return new HttpClient($this->getHttpClientParameters());
	}

	protected function buildResult(HttpClient $httpClient, bool $requestResult): Result
	{
		return $this->createAnswerForJsonResponse(
			$requestResult,
			$httpClient->getResult(),
			$httpClient->getError(),
			$httpClient->getStatus()
		);
	}

	protected function createAnswerForJsonResponse($queryResult, $response, $errors, $status): Result
	{
		$result = new Result();

		if(!$queryResult)
		{
			foreach ($errors as $code => $message)
			{
				$result->addError(new Error($message, $code));
			}

			return $result;
		}

		if($status != 200)
		{
			$result->addError(new Error("Server returned " . $status . " code", "WRONG_SERVER_RESPONSE"));
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
			foreach ($parsedResponse["errors"] as $error)
			{
				$result->addError(new Error($error["message"], $error["code"], $error["customData"]));
			}
		}
		else if(is_array($parsedResponse["data"]))
		{
			$result->setData($parsedResponse["data"]);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getHttpClientParameters(): array
	{
		return [
			"socketTimeout" => 10,
			"streamTimeout" => 30,
			"disableSslVerification" => true
		];
	}

	protected abstract function getServiceUrl(): string;
}