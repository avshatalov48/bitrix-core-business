<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Web\HttpClient;

class HttpQuery
{
	private const SERVICE_NAME = 'google';
	/**
	 * @var HttpClient
	 */
	private HttpClient $client;
	/**
	 * @var RequestLogger
	 */
	private RequestLogger $logger;

	public function __construct(HttpClient $client, private int $userId, string $serviceName = 'google')
	{
		$this->client = $client;

		$this->logger = ServiceLocator::getInstance()->get(RequestLogger::class);
	}

	/**
	 * @return HttpClient
	 */
	public function getClient(): HttpClient
	{
		return $this->client;
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param $body
	 *
	 * @return void
	 */
	public function query(string $method, string $url, $body = null): void
	{
		$this->client->query($method, $url, $body);

		$this->logger->debug(
			sprintf('Old Google sync. %s. %s "%s"', $this->client->getStatus(), $method, $url),
			[
				'requestParams' => $body,
				'url' => $url,
				'method' => $method,
				'statusCode' => $this->client->getStatus(),
				'response' => $this->prepareResponseForDebug($this->client->getResult()),
				'error' => $this->prepareErrorForDebug($this->client->getResult()),
				'type' => self::SERVICE_NAME,
				'userId' => $this->userId,
			]
		);
	}

	/**
	 * @return int
	 */
	public function getStatus(): int
	{
		return $this->client->getStatus();
	}

	/**
	 * @return string
	 */
	public function getResult(): string
	{
		return $this->client->getResult();
	}

	/**
	 * @param string $url
	 * @param $body
	 * @return void
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function post(string $url, $body = null): void
	{
		$this->query(HttpClient::HTTP_POST, $url, $body);
	}

	/**
	 * @param string $url
	 * @param $body
	 * @return void
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function get(string $url, $body = null): void
	{
		$this->query(HttpClient::HTTP_GET, $url, $body);
	}

	/**
	 * @param string $url
	 * @param $body
	 * @return void
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function delete(string $url, $body = null): void
	{
		$this->query(HttpClient::HTTP_DELETE, $url, $body);
	}

	/**
	 * @param string $url
	 * @param $body
	 * @return void
	 */
	public function put(string $url, $body = null): void
	{
		$this->query(HttpClient::HTTP_PUT, $url, $body);
	}

	/**
	 * @param $response
	 * @return string
	 */
	private function prepareResponseForDebug($response): string
	{
		if ($this->client->getStatus() >= 300)
		{
			return '';
		}

		try
		{
			$response = \Bitrix\Main\Web\Json::decode($response);
		}
		catch (\Exception $e){}

		if (!$response || !is_array($response))
		{
			return '';
		}

		$result = '';

		foreach ($response as $key => $value)
		{
			if (is_string($value))
			{
				$result .= "{$key}:{$value}; ";
			}
			elseif (is_array($value))
			{
				$result .= "{$key}:";
				foreach ($value as $valueKey => $valueValue)
				{
					$valueValue = (is_array($valueValue)) ? json_encode($valueValue) : $valueValue;
					$result .= "{$valueKey}:{$valueValue}, ";
				}
				$result .= "; ";
			}
		}

		return $result;
	}

	/**
	 * @return string
	 */
	private function prepareErrorForDebug($response): string
	{
		try
		{
			$response = \Bitrix\Main\Web\Json::decode($response);
		}
		catch (\Exception $e){}

		if (
			(!$response || !is_array($response))
			|| ($this->client->getStatus() < 400)
		)
		{
			return '';
		}

		return $response['error']['code'] . " " . $response['error']['message'] . "; ";
	}
}
