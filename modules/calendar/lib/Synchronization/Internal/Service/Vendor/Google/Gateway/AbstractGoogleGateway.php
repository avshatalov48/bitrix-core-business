<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Gateway;

use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AccessDeniedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\BadRequestException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\ConflictException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\Google\RateLimitExceededException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\Google\SyncTokenNotValidException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

abstract class AbstractGoogleGateway
{
	protected const BASE_PATH = 'https://www.googleapis.com:443/calendar/v3';

	private RequestLogger $logger;

	public function __construct(protected readonly HttpClient $client)
	{
		$this->logger = ServiceLocator::getInstance()->get(RequestLogger::class);
	}

	/**
	 * @throws UnexpectedException
	 */
	protected function encode(array $data): ?string
	{
		try
		{
			$result = Json::encode($data, JSON_UNESCAPED_SLASHES);
		}
		catch (ArgumentException $e)
		{
			throw new UnexpectedException(
				sprintf('Unable to encode data for Google: "%s"', $e->getMessage()),
				previous: $e
			);
		}

		return is_string($result) ? $result : null;
	}

	protected function isRequestSuccess(): bool
	{
		return $this->client->getStatus() === 200;
	}

	protected function isDeleteRequestSuccess(): bool
	{
		$acceptedCodes = [200, 201, 204, 404, 410];

		return in_array($this->client->getStatus(), $acceptedCodes);
	}

	protected function request(string $method, string $uri, mixed $data = null): void
	{
		$this->client->query($method, $uri, $data);

		$this->logger->debug(
			sprintf('New Google sync. %s. %s "%s"', $this->client->getStatus(), $method, $uri),
			[
				'method' => $method,
				'url' => $uri,
				'request' => $data,
				'statusCode' => $this->client->getStatus(),
				'response' => $this->client->getResult(),
			]
		);
	}

	/**
	 * @param string $defaultErrorMessage
	 *
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 * @throws ConflictException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws RateLimitExceededException
	 * @throws SyncTokenNotValidException
	 * @throws UnexpectedException
	 */
	protected function processErrors(string $defaultErrorMessage): void
	{
		try
		{
			$error = $this->getResponseError();
		}
		catch (ArgumentException $e)
		{
			throw new UnexpectedException(
				sprintf('Unable to decode a Google error response: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}

		if (!empty($error))
		{
			if (!empty($error['code']) && !empty($error['message']))
			{
				if ($error['code'] === 400)
				{
					throw new BadRequestException($error['message']);
				}

				if ($error['code'] === 401)
				{
					throw new NotAuthorizedException($error['message']);
				}

				if ($error['code'] === 403)
				{
					$this->process403Response($error);
				}

				if ($error['code'] === 404)
				{
					throw new NotFoundException($error['message']);
				}

				if ($error['code'] === 409)
				{
					throw new ConflictException($error['message']);
				}

				if ($this->isSyncTokenNotValidError($error['code'], $error['message']))
				{
					throw new SyncTokenNotValidException($error['message'], $error['code']);
				}

				throw new UnexpectedException($error['message'], $error['code']);
			}

			throw new UnexpectedException('Unknown Google API error', (int)($error['code'] ?? 400));
		}

		throw new UnexpectedException($defaultErrorMessage);
	}

	/**
	 * @throws AccessDeniedException
	 * @throws RateLimitExceededException
	 */
	private function process403Response(array $error): void
	{
		if (!empty($error['details']))
		{
			foreach ($error['details'] as $detail)
			{
				if ($detail['@type'] === 'type.googleapis.com/google.rpc.ErrorInfo')
				{
					if (!empty($detail['reason']) && $detail['reason'] === 'RATE_LIMIT_EXCEEDED')
					{
						throw new RateLimitExceededException($error['message']);
					}
				}
			}
		}

		throw new AccessDeniedException($error['message']);
	}

	/**
	 * @return array|null
	 *
	 * @throws ArgumentException
	 */
	private function getResponseError(): ?array
	{
		$response = Json::decode($this->client->getResult());

		if (is_array($response) && !empty($response['error']) && is_array($response['error']))
		{
			return $response['error'];
		}

		return null;
	}

	private function isSyncTokenNotValidError(int $errorCode, string $errorMessage): bool
	{
		$messages = [
			'Sync token is no longer valid, a full sync is required.',
			'The requested minimum modification time lies too far in the past.'
		];

		return $errorCode === 410 && in_array($errorMessage, $messages, true);
	}
}
