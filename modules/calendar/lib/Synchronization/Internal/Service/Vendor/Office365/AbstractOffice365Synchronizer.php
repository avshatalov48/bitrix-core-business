<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365;

use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\ConnectionManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;

class AbstractOffice365Synchronizer
{
	public const VENDOR_CODE = 'office365';

	public function __construct(
		private readonly ConnectionProvider $connectionProvider,
		protected readonly ConnectionManager $connectionManager,
		protected readonly SectionConnectionRepository $sectionConnectionRepository,
		protected readonly RequestLogger $logger
	)
	{
	}

	protected function handleUnauthorizedException(Connection $connection): void
	{
		$this->deactivateConnection($connection);
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

	protected function handleAuthorizationException(Connection $connection): void
	{
		if (!$connection->getId())
		{
			return;
		}

		$this->connectionManager->markFailedConnectionAsDeleted($connection);
	}

	protected function getUserConnection(int $userId): ?Connection
	{
		return $this->connectionProvider->getActiveUserConnection($userId, static::VENDOR_CODE);
	}

	protected function configureLoggerContext(int $userId, mixed $entityId = null): void
	{
		$this->logger
			->setUserId($userId)
			->setEntityId($entityId)
			->setType(static::VENDOR_CODE)
		;
	}

	protected function getSectionConnection(int $sectionId, int $connectionId): ?SectionConnection
	{
		return $this->sectionConnectionRepository->findOneBySectionAndConnectionId($sectionId, $connectionId);
	}
}
