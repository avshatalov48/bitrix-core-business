<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Gateway;

use Bitrix\Calendar\Integration\Dav\WebDavMethodProvider;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AccessDeniedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\BadRequestException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Main\Web\HttpClient;

class AbstractICloudGateway
{
	public function __construct(
		protected readonly WebDavMethodProvider $davMethodProvider,
		protected readonly RequestDataBuilder $requestDataBuilder,
		protected readonly HttpClient $client,
		private readonly RequestLogger $logger,
		protected string $serverPath,
	)
	{
	}

	protected function isRequestSuccess(): bool
	{
		$acceptedCodes = [200, 201, 204];

		return in_array($this->client->getStatus(), $acceptedCodes, true);
	}

	protected function isDavRequestSuccess(): bool
	{
		$acceptedCodes = [200, 201, 207];

		return in_array($this->client->getStatus(), $acceptedCodes, true);
	}

	protected function isDeleteRequestSuccess(): bool
	{
		$acceptedCodes = [200, 201, 204, 404];

		return in_array($this->client->getStatus(), $acceptedCodes, true);
	}

	protected function request(string $method, string $uri, mixed $data = null): void
	{
		$this->client->query($method, $uri, $data);

		$this->logger->debug(
			sprintf('New iCloud sync. %s. %s "%s"', $this->client->getStatus(), $method, $uri),
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
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 */
	protected function processErrors(string $defaultErrorMessage): void
	{
		$error = $this->getResponseError();

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

				if ($error['code'] === 404)
				{
					throw new NotFoundException($error['message']);
				}

				if ($error['code'] === 403)
				{
					throw new AccessDeniedException($error['message']);
				}

				throw new UnexpectedException($error['message'], $error['code']);
			}

			throw new UnexpectedException(
				sprintf('Unknown iCloud API error: "%s"', $error['message'] ?? ''),
				(int)($error['code'] ?? 400)
			);
		}

		throw new UnexpectedException($defaultErrorMessage);
	}

	private function getResponseError(): array
	{
		$response = $this->client->getResponse();

		$code = $response?->getStatusCode();
		$message = $response?->getReasonPhrase();

		if (!$code && !$message)
		{
			return [];
		}

		return [
			'code' => $code,
			'message' => $message,
		];
	}
}
