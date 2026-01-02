<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Common\Google;

use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Google\Factory;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Synchronization\Internal\Service\ConnectionManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\AbstractGoogleSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\GoogleEventSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\GoogleSectionSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Push\PushManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use CCalendar;

class SynchronizeConnectionCommandHandler
{
	public function __construct(
		private readonly ConnectionProvider $connectionProvider,
		private readonly GoogleSectionSynchronizer $sectionSynchronizer,
		private readonly GoogleEventSynchronizer $eventSynchronizer,
		private readonly ConnectionManager $connectionManager,
		private readonly PushManager $pushManager,
		private readonly RequestLogger $logger,
	)
	{
	}

	/**
	 * @throws Exception
	 */
	public function __invoke(SynchronizeConnectionCommand $command): void
	{
		$connection = $this->getConnection($command->userId);

		if (!$connection)
		{
			return;
		}

		try
		{
			$this->sectionSynchronizer->importSections($connection->getOwner()->getId(), $connection->getToken());
			$this->eventSynchronizer->importEvents($connection->getOwner()->getId());
			$this->connectionManager->updateConnection($connection);
		}
		catch (\Exception $e)
		{
			throw new Exception($e->getMessage(), $e->getCode(), $e);
		}

		CCalendar::ClearCache();

		try
		{
			$this->pushManager->resubscribeConnectionFully($connection);
		}
		catch (\Exception $e)
		{
			$this->logger->error(
				'Unable to resubscribe connection ' . $connection->getName() . ' to pushes',
				[
					'message' => $e->getMessage(),
					'code' => $e->getCode(),
					'entityId' => $connection->getId(),
					'type' => AbstractGoogleSynchronizer::VENDOR_CODE,
					'userId' => $connection->getOwner()->getId(),
				]
			);
		}
	}

	private function getConnection(int $userId): ?Connection
	{
		try
		{
			$connections = $this->connectionProvider->getActiveConnections(
				$userId,
				'user',
				[Factory::SERVICE_NAME]
			);
		}
		catch (\Exception $e)
		{
			$this->logger->error(
				'Unable to get connections of user ' . $userId,
				[
					'message' => $e->getMessage(),
					'code' => $e->getCode(),
					'trace' => $e->getTraceAsString(),
					'type' => AbstractGoogleSynchronizer::VENDOR_CODE,
					'userId' => $userId,
				]
			);
		}

		if (empty($connections))
		{
			return null;
		}

		if (count($connections) === 1)
		{
			return $connections[0];
		}

		$this->logger->notice(
			sprintf(
				'The user %d has %d connections with Google',
				$userId,
				count($connections)
			),
			[
				'userId' => $userId,
				'type' => AbstractGoogleSynchronizer::VENDOR_CODE,
			]
		);

		return null;
	}
}
