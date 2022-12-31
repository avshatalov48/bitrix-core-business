<?php

namespace Bitrix\MessageService\Providers\Edna\SMS;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\StringHelper;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\MessageService\DTO;
use Bitrix\MessageService\Sender\Result\HttpRequestResult;
use Bitrix\MessageService\Sender\Util;

class ExternalSender extends \Bitrix\MessageService\Providers\Edna\ExternalSender
{

	protected string $apiKey;
	protected string $apiEndpoint;

	public function __construct(?string $apiKey, string $apiEndpoint, int $socketTimeout = 10, int $streamTimeout = 30)
	{
		$this->apiKey = $apiKey ?? '';
		$this->apiEndpoint = $apiEndpoint;
		$this->socketTimeout = $socketTimeout;
		$this->streamTimeout = $streamTimeout;
	}

	public function callExternalMethod(string $method, ?array $requestParams = null, string $httpMethod = ''): HttpRequestResult
	{
		if ($this->apiKey === '')
		{
			$result = new HttpRequestResult();
			$result->addError(new Error('Missing API key when requesting a service.'));

			return $result;
		}
		$url = $this->apiEndpoint . $method;
		$queryMethod = HttpClient::HTTP_GET;

		$httpClient = new HttpClient([
			'socketTimeout' => $this->socketTimeout,
			'streamTimeout' => $this->streamTimeout,
			'waitResponse' => true,
			'version' => HttpClient::HTTP_1_1,
		]);
		$httpClient->setHeader('User-Agent', static::USER_AGENT);
		$httpClient->setHeader('Content-type', static::CONTENT_TYPE);
		$httpClient->setHeader('X-API-KEY', $this->apiKey);
		$httpClient->setCharset(static::CHARSET);

		if (is_array($requestParams))
		{
			$queryMethod = HttpClient::HTTP_POST;
			$requestParams = Json::encode($requestParams);
		}

		$result = new HttpRequestResult();
		$result->setHttpRequest(new DTO\Request([
			'method' => $queryMethod,
			'uri' => $url,
			'headers' => method_exists($httpClient, 'getRequestHeaders') ? $httpClient->getRequestHeaders()->toArray() : [],
			'body' => $requestParams
		]));

		$answer = [];
		$errorInfo = [];
		if ($httpClient->query($queryMethod, $url, $requestParams))
		{
			$answer = $this->parseExternalAnswer($httpClient->getResult());

			if ($httpClient->getStatus() !== 200)
			{
				$errorInfo = [
					'code' => $httpClient->getStatus(),
					'error' => $this->getMessageByErrorCode('error-' . $httpClient->getStatus()),
				];
			}
		}
		else
		{
			$error = $httpClient->getError();
			$errorInfo = [
				'code' => key($error),
				'error' => current($error),
			];
		}
		$result->setHttpResponse(new DTO\Response([
			'statusCode' => $httpClient->getStatus(),
			'headers' => $httpClient->getHeaders()->toArray(),
			'body' => $httpClient->getResult(),
			'error' => Util::getHttpClientErrorString($httpClient)
		]));

		$result->setData($answer);

		if (array_key_exists('code', $errorInfo) && $errorInfo['code'] !== 'ok')
		{
			$result->addError(new Error($errorInfo['error'], $errorInfo['code'], $errorInfo));
		}

		return $result;
	}

	protected function getMessageByErrorCode(string $code)
	{
		$locCode = 'MESSAGESERVICE_SENDER_SMS_SMSEDNARU_';
		$locCode .= StringHelper::str_replace('-', '_', mb_strtoupper($code));

		return Loc::getMessage($locCode) ?? $code;
	}

	protected function parseExternalAnswer(string $httpResult): array
	{
		try
		{
			return Json::decode($httpResult);
		}
		catch (ArgumentException $exception)
		{
			return ['error' => 'error-json-parsing'];
		}
	}
}