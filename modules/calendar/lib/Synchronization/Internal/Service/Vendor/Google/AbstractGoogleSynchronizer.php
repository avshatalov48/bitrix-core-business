<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google;

use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Integration\Socialservices\Auth\GoogleAuthHelper;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Service\ConnectionManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;

class AbstractGoogleSynchronizer
{
	public const VENDOR_CODE = 'google';

	public function __construct(
		private readonly ConnectionProvider $connectionProvider,
		protected readonly ConnectionManager $connectionManager,
		protected readonly RequestLogger $logger
	)
	{
	}

	/**
	 * @throws RepositoryReadException
	 */
	protected function handleUnauthorizedException(Connection $connection): void
	{
		$userId = $connection->getOwner()->getId();
		$authEntity = GoogleAuthHelper::getUserAuthEntity($userId);
		$userTokenInfo = GoogleAuthHelper::getStoredTokens($userId);
		$refreshResult = false;

		if (!empty($userTokenInfo['REFRESH_TOKEN']))
		{
			$refreshResult = $authEntity->getNewAccessToken($userTokenInfo['REFRESH_TOKEN'], $userId, true);
		}

		if (!$refreshResult)
		{
			$this->deactivateConnection($connection);
		}
	}

	protected function deactivateConnection(Connection $connection): void
	{
		if (!$connection->getId())
		{
			return;
		}

		$connection->setStatus('[401] Unauthorized');

		$this->connectionManager->updateConnection($connection);
	}

	protected function getUserConnection(int $userId): ?Connection
	{
		return $this->connectionProvider->getActiveUserConnection(
			$userId,
			\Bitrix\Calendar\Sync\Google\Factory::SERVICE_NAME
		);
	}

	protected function configureLoggerContext(int $userId, mixed $entityId = null): void
	{
		$this->logger
			->setUserId($userId)
			->setEntityId($entityId)
			->setType(self::VENDOR_CODE)
		;
	}
}
