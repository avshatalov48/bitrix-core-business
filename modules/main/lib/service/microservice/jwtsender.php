<?php

namespace Bitrix\Main\Service\MicroService;

use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;

abstract class JWTSender extends BaseSender
{
	abstract protected function obtainJWToken();

	protected function createHttpClient()
	{
		$httpClient = new HttpClient(
			$this->getHttpClientParameters()
		);

		$token = $this->obtainJWToken();

		if(!$token)
		{
			throw new SystemException('JWT Token must not be empty');
		}

		$httpClient->setHeader('Authorization', 'Bearer ' . $token);
		return $httpClient;
	}

	public function performRequest($action, array $parameters = []): Result
	{
		$url = $this->getServiceUrl() . "/api/?action=" . $action;
		$httpClient = $this->createHttpClient();
		$result = $httpClient->query(HttpClient::HTTP_POST, $url, $parameters);

		return $this->createAnswer(
			$result,
			$httpClient->getResult(),
			$httpClient->getError(),
			$httpClient->getStatus()
		);
	}
}