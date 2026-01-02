<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google;

use Bitrix\Calendar\Integration\Socialservices\Auth\GoogleAuthHelper;
use Bitrix\Calendar\Sync\Util\Helper;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Gateway\GoogleEventGateway;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Gateway\GooglePushGateway;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Gateway\GoogleSectionGateway;
use Bitrix\Main\Web\HttpClient;

class GoogleGatewayProvider
{
	/**
	 * @var GoogleSectionGateway[]
	 */
	private array $sectionGateways = [];

	/**
	 * @var GoogleEventGateway[]
	 */
	private array $eventGateways = [];

	/**
	 * @var GooglePushGateway[]
	 */
	private array $pushGateways = [];

	/**
	 * @throws NotAuthorizedException
	 */
	public function getSectionGateway(int $userId): ?GoogleSectionGateway
	{
		if (isset($this->sectionGateways[$userId]))
		{
			return $this->sectionGateways[$userId];
		}

		$httpClient = $this->buildHttpClient($userId);

		if ($httpClient)
		{
			$this->sectionGateways[$userId] = new GoogleSectionGateway($httpClient);

			return $this->sectionGateways[$userId];
		}

		return null;
	}

	/**
	 * @throws NotAuthorizedException
	 */
	public function getEventGateway(int $userId): ?GoogleEventGateway
	{
		if (isset($this->eventGateways[$userId]))
		{
			return $this->eventGateways[$userId];
		}

		$httpClient = $this->buildHttpClient($userId);

		if ($httpClient)
		{
			$this->eventGateways[$userId] = new GoogleEventGateway($httpClient);

			return $this->eventGateways[$userId];
		}

		return null;
	}

	/**
	 * @throws NotAuthorizedException
	 */
	public function getPushGateway(int $userId): ?GooglePushGateway
	{
		if (isset($this->pushGateways[$userId]))
		{
			return $this->pushGateways[$userId];
		}

		$httpClient = $this->buildHttpClient($userId);

		if ($httpClient)
		{
			$this->pushGateways[$userId] = new GooglePushGateway($httpClient);

			return $this->pushGateways[$userId];
		}

		return null;
	}

	/**
	 * @throws NotAuthorizedException
	 */
	private function buildHttpClient(int $userId): ?HttpClient
	{
		$httpClient = new HttpClient();
		try
		{
			$authEntity = GoogleAuthHelper::getUserAuthEntity($userId);
		}
		catch (RepositoryReadException $e)
		{
			throw new NotAuthorizedException(
				sprintf('Unable to get user auth entity: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}

		if ($authEntity->getToken())
		{
			$httpClient->setHeader('Authorization', 'Bearer ' . $authEntity->getToken());
			$httpClient->setHeader('Content-Type', 'application/json');
			$httpClient->setHeader('Referer', Helper::getDomain());

			unset($authEntity);

			return $httpClient;
		}

		return null;
	}
}
