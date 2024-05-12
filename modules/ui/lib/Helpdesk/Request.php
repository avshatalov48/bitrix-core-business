<?php

namespace Bitrix\UI\Helpdesk;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;

class Request
{
	private Url $url;
	private RequestParametersBuilder $requestParametersBuilder;

	public function __construct(private string $path, private ?array $additionalParameters = null)
	{
		$this->url = new Url();
		$this->requestParametersBuilder = new RequestParametersBuilder();
	}

	public function send(): Result
	{
		$httpClient = new HttpClient();
		$result = new Result();
		$response = $httpClient->get($this->getPreparedUrl());

		try
		{
			$result->setData((array)Json::decode($response));
		}
		catch (\Exception $e)
		{
			$result->addError(new Error($e->getMessage()));
		}

		return $result;
	}

	public function getUrl(): Url
	{
		return $this->url;
	}

	public function getPreparedUrl(): Uri
	{
		$parameters = $this->getParameters();

		if ($this->additionalParameters)
		{
			$parameters = $this->additionalParameters + $parameters;
		}

		return $this->url->getByPath($this->path)->addParams($parameters);
	}

	public function getParameters(): array
	{
		return $this->requestParametersBuilder->build();
	}
}