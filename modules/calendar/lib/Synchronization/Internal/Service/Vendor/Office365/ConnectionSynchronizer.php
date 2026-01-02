<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365;

use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Synchronization\Internal\Service\ConnectionManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common\AbstractConnectionSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Push\PushManager;

class ConnectionSynchronizer extends AbstractConnectionSynchronizer
{
	public function __construct(
		private readonly Office365SectionSynchronizer $sectionSynchronizer,
		private readonly Office365EventSynchronizer $eventSynchronizer,
		private readonly ConnectionManager $connectionManager,
		private readonly PushManager $pushManager,
		ConnectionProvider $connectionProvider,
		RequestLogger $logger,
	)
	{
		parent::__construct($connectionProvider, $logger);
	}

	/**
	 * @throws Exception
	 */
	public function synchronizeUserConnection(int $userId): void
	{
		$connection = $this->getConnection($userId, AbstractOffice365Synchronizer::VENDOR_CODE);

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

		\CCalendar::ClearCache();

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
					'type' => $connection->getVendor()->getCode(),
					'userId' => $connection->getOwner()->getId(),
				]
			);
		}
	}
}
