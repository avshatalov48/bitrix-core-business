<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\MessageService\DTO\Request;
use Bitrix\MessageService\DTO\Response;
use Bitrix\MessageService\Sender\Result\HttpRequestResult;
use Bitrix\MessageService\Sender\Util;

class ExternalSender
{
	protected const USER_AGENT = 'Bitrix24';
	protected const CONTENT_TYPE = 'application/json';
	protected const CHARSET = 'UTF-8';

	protected const SOCKET_TIMEOUT = 10;
	protected const STREAM_TIMEOUT = 30;

	protected const WAIT_RESPONSE = true;

	protected string $apiKey;
	protected string $apiEndpoint;

	public function __construct(?string $apiKey, string $apiEndpoint)
	{
		$this->apiKey = $apiKey ?? '';
		$this->apiEndpoint = $apiEndpoint;
	}

	/**
	 * @param string $method
	 * @param array|null $requestParams
	 * @param string $httpMethod see class constants \Bitrix\Main\Web\HttpClient
	 * @return HttpRequestResult
	 */
	public function callExternalMethod(string $method, ?array $requestParams = null, string $httpMethod = ''): HttpRequestResult
	{
		if ($this->apiKey === '')
		{
			$result = new HttpRequestResult();
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_EDNARU_ERROR_SYSTEM')));

			return $result;
		}

		$url = $this->apiEndpoint . $method;
		$queryMethod = HttpClient::HTTP_GET;

		$httpClient = new HttpClient([
			'socketTimeout' => static::SOCKET_TIMEOUT,
			'streamTimeout' => static::STREAM_TIMEOUT,
			'waitResponse' => static::WAIT_RESPONSE,
			'version' => HttpClient::HTTP_1_1,
		]);
		$httpClient->setHeader('User-Agent', static::USER_AGENT);
		$httpClient->setHeader('Content-type', static::CONTENT_TYPE);
		$httpClient->setHeader('X-API-KEY', $this->apiKey);
		$httpClient->setCharset(static::CHARSET);


		if (isset($requestParams) && $httpMethod !== HttpClient::HTTP_GET)
		{
			$queryMethod = HttpClient::HTTP_POST;
		}
		$queryMethod = $httpMethod ?: $queryMethod;

		if (isset($requestParams) && $queryMethod === HttpClient::HTTP_POST)
		{
			$requestParams = Json::encode($this->convertRequestParams($requestParams));
		}

		if (isset($requestParams) && $queryMethod === HttpClient::HTTP_GET)
		{
			$url .= '?' . $this->getParamsForGetMethod($requestParams);
		}

		$result = new HttpRequestResult();
		$result->setHttpRequest(new Request([
			'method' => $queryMethod,
			'uri' => $url,
			'headers' => method_exists($httpClient, 'getRequestHeaders') ? $httpClient->getRequestHeaders()->toArray() : [],
			'body' => $requestParams,
		]));

		if ($httpClient->query($queryMethod, $url, $requestParams))
		{
			$response = $this->parseExternalResponse($httpClient->getResult());
		}
		else
		{
			$result->setHttpResponse(new Response([
				'error' => Util::getHttpClientErrorString($httpClient)
			]));
			$error = $httpClient->getError();
			$response = ['code' => current($error)];
		}

		$result->setHttpResponse(new Response([
			'statusCode' => $httpClient->getStatus(),
			'headers' => $httpClient->getHeaders()->toArray(),
			'body' => $httpClient->getResult(),
		]));

		if (!$this->checkResponse($response))
		{
			$errorMessage = '';

			if (isset($response['code']))
			{
				$errorMessage = $this->getErrorMessageByCode($response['code']);
			}

			if (isset($response['detail']))
			{
				$errorMessage = $response['detail'];
			}

			$result->addError(new Error($errorMessage));

			return $result;
		}
		$result->setData($response);

		return $result;
	}

	protected function parseExternalResponse(string $httpResult): array
	{
		try
		{
			return Json::decode($httpResult);
		}
		catch (ArgumentException $exception)
		{
			return ['code' => 'error-json-parsing'];
		}
	}

	protected function checkResponse(array $response): bool
	{
		// Success response without "code" parameter https://edna.docs.apiary.io/#reference/api/by-apikey
		if ($this->apiEndpoint === Old\Constants::API_ENDPOINT)
		{
			return (isset($response['code']) && $response['code'] === 'ok')	|| !isset($response['code']);
		}

		return (isset($response['status']) && (int)$response['status'] === 200) || !isset($response['status']);
	}

	protected function convertRequestParams(array $requestParams): array
	{
		if (!Application::isUtfMode())
		{
			$requestParams = Encoding::convertEncoding($requestParams, SITE_CHARSET, 'UTF-8');
		}

		return $requestParams;
	}

	/**
	 * Mapping from the docs https://edna.docs.apiary.io/#reference/0
	 *
	 * @param string $errorCode
	 *
	 * @return string
	 */
	protected function getErrorMessageByCode(?string $errorCode): string
	{
		$errorCode = mb_strtoupper($errorCode);
		$errorCode = str_replace("-", "_", $errorCode);

		$errorMessage = Loc::getMessage('MESSAGESERVICE_SENDER_SMS_EDNARU_'.$errorCode);

		return $errorMessage ? : Loc::getMessage('MESSAGESERVICE_SENDER_SMS_EDNARU_UNKNOWN_ERROR');
	}

	protected function getParamsForGetMethod(array $params): string
	{
		$result = '';
		foreach ($params as $name => $value)
		{
			$result .= $name . '=' . $value . '&';
		}

		return rtrim($result,'&');
	}
}