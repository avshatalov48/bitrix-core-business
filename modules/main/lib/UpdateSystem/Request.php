<?php

namespace Bitrix\Main\UpdateSystem;

use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\HttpHeaders;
use Bitrix\Main\Web\Uri;

class Request
{
	private string $method = 'POST';
	private ?array $body = null;
	private Uri $url;
	private HttpClient $httpClient;

	public function __construct()
	{
		$this->httpClient = new HttpClient();
		$this->httpClient->setVersion('1.0');
	}

	public function setHeaders(HttpHeaders $headers): void
	{
		$this->httpClient->setHeaders($headers->toArray());
	}

	public function setUrl(Uri $url): void
	{
		$this->url = $url;
	}

	public function setMethod(string $method): void
	{
		$this->method = $method;
	}

	public function setProxy(array $proxyData): void
	{
		if (
			isset($proxyData['host'])
			&& !empty($proxyData['host'])
			&& isset($proxyData['port'])
			&& !empty($proxyData['port'])
		) {
			$this->httpClient->setProxy(
				$proxyData['host'],
				$proxyData['port'],
				$proxyData['user'] ?? null,
				$proxyData['password'] ?? null,
			);
		}
	}

	public function setBody(array $body): void
	{
		$this->body = $body;
	}

	/**
	 * @throws SystemException
	 * @throws \Exception
	 */
	public function send(): string
	{
		$this->url->addParams($this->body);
		$isSuccess = $this->httpClient->query(
			$this->method,
			$this->url->getLocator(),
			$this->body
		);

		if ($isSuccess)
		{
			return $this->httpClient->getResult();
		}
		else
		{
			if ($this->httpClient->getStatus() === 0 && empty($this->httpClient->getError()))
			{
				throw new \Exception('Invalid response from host: '.$this->url->getHost().' [RS01]', 400);
			}
			else
			{
				throw new SystemException(implode('\r\n', $this->httpClient->getError()).' [RS02]');
			}
		}
	}
}
