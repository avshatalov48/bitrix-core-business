<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Gateway;

use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\ConflictException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\GoneException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\PreconditionFailedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class AbstractOffice365Gateway
{
	protected const BASE_PATH = 'https://graph.microsoft.com/v1.0/';

	private RequestLogger $logger;

	public function __construct(protected readonly HttpClient $client)
	{
		$this->logger = ServiceLocator::getInstance()->get(RequestLogger::class);
	}

	/**
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	protected function get(string $uri, array $params = []): array
	{
		if ($params)
		{
			$uri .= (strpos($uri, '?') ? '&' : '?') . http_build_query($params);
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		return $this->request(HttpClient::HTTP_GET, $uri, $params);
	}

	/**
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	protected function post(string $uri, array $params = []): array
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return $this->request(HttpClient::HTTP_POST, $uri, $params);
	}

	/**
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	protected function delete(string $uri, array $params = []): array
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return $this->request(HttpClient::HTTP_DELETE, $uri, $params);
	}

	/**
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	protected function put(string $uri, array $params = []): array
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return $this->request(HttpClient::HTTP_PUT, $uri, $params);
	}

	/**
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws PreconditionFailedException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	protected function patch(string $uri, array $params = []): array
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return $this->request(HttpClient::HTTP_PATCH, $uri, $params);
	}

	/**
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws PreconditionFailedException
	 * @throws UnexpectedException
	 */
	protected function request(string $method, string $uri, array $params = []): array
	{
		$paramString = $this->prepareParams($params);

		$uri = static::BASE_PATH . $uri;

		$this->client->query($method, $uri, $paramString);

		$this->logger->debug(
			sprintf('New Office sync. %s. %s "%s"', $this->client->getStatus(), $method, $uri),
			[
				'method' => $method,
				'url' => $uri,
				'request' => $paramString,
				'statusCode' => $this->client->getStatus(),
				'response' => $this->client->getResult(),
			]
		);

		if ($this->client->getStatus() < 300)
		{
			return $this->prepareResponse();
		}

		$this->processErrors();
	}

	/**
	 * @param array $params
	 *
	 * @return string|null
	 */
	protected function prepareParams(array $params): ?string
	{
		try
		{
			return $params ? Json::encode($params, JSON_UNESCAPED_SLASHES) : null;
		}
		catch (ArgumentException)
		{
			return null;
		}
	}

	/**
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws PreconditionFailedException
	 * @throws UnexpectedException
	 */
	protected function processErrors(): void
	{
		$error = $this->getResponseError();

		if (!empty($error))
		{
			if (!empty($error['code']) && !empty($error['message']))
			{
				$message = $error['code'] . ' ' . $error['message'];

				$this->throwException($message);
			}

			throw new UnexpectedException('Unknown Office 365 error', (int)($error['code'] ?? 400));
		}

		throw new UnexpectedException('Unknown Office 365 error', 400);
	}

	/**
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws PreconditionFailedException
	 * @throws UnexpectedException
	 */
	private function throwException(string $message): void
	{
		throw match ($this->client->getStatus())
		{
			401 => new NotAuthorizedException($message),
			404 => new NotFoundException($message),
			409 => new ConflictException($message),
			410 => new GoneException($message),
			412 => new PreconditionFailedException($message),
			default => new UnexpectedException($message, $this->client->getStatus()),
		};
	}

	/**
	 * @return array|null
	 */
	private function getResponseError(): ?array
	{
		try
		{
			$response = Json::decode($this->client->getResult());
		}
		catch (ArgumentException)
		{
			return null;
		}

		if (is_array($response) && !empty($response['error']) && is_array($response['error']))
		{
			return $response['error'];
		}

		return null;
	}


	/**
	 * @return array
	 *
	 * @throws UnexpectedException
	 */
	protected function prepareResponse(): array
	{
		$contentType = $this->client->getHeaders()->getContentType();

		if ($contentType === 'multipart/mixed')
		{
			$response = $this->multipartDecode(
				$this->client->getResult(),
				$this->client->getHeaders()->getBoundary()
			);
		}
		else
		{
			try
			{
				$response = $this->client->getResult()
					? Json::decode($this->client->getResult())
					: [];
			}
			catch (ArgumentException $e)
			{
				throw new UnexpectedException('Wrong JSON data from Office 365', 0, $e);
			}
		}

		return $response;
	}

	/**
	 * @param string $response
	 * @param string $boundary
	 *
	 * @return array
	 */
	protected function multipartDecode(string $response, string $boundary): array
	{
		$events = [];

		$response = str_replace("--$boundary--", "--$boundary", $response);
		$parts = explode("--$boundary\r\n", $response);

		foreach ($parts as $part)
		{
			$part = trim($part);
			if (!empty($part))
			{
				$partEvent = explode("\r\n\r\n", $part);
				$data = $this->getMetaInfo($partEvent[1]);
				$id = $this->getId($partEvent[0]);

				if ($data['status'] === 200)
				{
					if ($id === null)
					{
						continue;
					}

					try
					{
						$event = Json::decode($partEvent[2]);
					}
					catch (ArgumentException)
					{
						continue;
					}

					$event['etag'] = $data['etag'];
					$events[$id] = $event;
				}
			}
		}

		return $events;
	}

	private function getMetaInfo($headers): array
	{
		$data = [];

		foreach (explode("\n", $headers) as $k => $header)
		{
			if ($k === 0)
			{
				if (preg_match('#HTTP\S+ (\d+)#', $header, $find))
				{
					$data['status'] = (int)$find[1];
				}
			}
			elseif (mb_strpos($header, ':') !== false)
			{
				[$headerName, $headerValue] = explode(':', $header, 2);
				if (mb_strtolower($headerName) === 'etag')
				{
					$data['etag'] = trim($headerValue);
				}
			}
		}

		return $data;
	}

	/**
	 * @param string $headers
	 *
	 * @return int|null
	 */
	private function getId(string $headers): ?int
	{
		$id = null;

		foreach (explode("\n", $headers) as $header)
		{
			if (mb_strpos($header, ':') !== false)
			{
				[$headerName, $headerValue] = explode(':', $header, 2);
				if (mb_strtolower($headerName) === 'content-id')
				{
					$part = explode(':', $headerValue);
					$id = (int)rtrim($part[1], '>');

					break;
				}
			}
		}

		return $id;
	}
}
