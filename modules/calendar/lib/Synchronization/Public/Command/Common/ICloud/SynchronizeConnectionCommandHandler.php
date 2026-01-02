<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Common\ICloud;

use Bitrix\Calendar\Integration\Pull\PushCommand;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Handlers\MasterPushHandler;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Sync\Icloud\Helper;
use Bitrix\Calendar\Sync\Managers\NotificationManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\ICloudEventSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\ICloudSectionSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use CCalendar;

class SynchronizeConnectionCommandHandler
{
	public function __construct(
		private readonly ICloudSectionSynchronizer $sectionSynchronizer,
		private readonly ICloudEventSynchronizer $eventSynchronizer,
		private readonly Factory $mapperFactory,
	)
	{
	}

	/**
	 * @throws ArgumentException
	 * @throws Exception
	 */
	public function __invoke(SynchronizeConnectionCommand $command): ?Connection
	{
		$connection = $this->mapperFactory->getConnection()->getById($command->connectionId);

		if (!$connection)
		{
			throw new Exception('Connection not found');
		}

		$userId = $command->userId;

		try
		{
			$this->sectionSynchronizer->importSections($userId);

			$this->eventSynchronizer->importEvents($userId);

			$this->addPullEvent($connection, MasterPushHandler::MASTER_STAGE[2]);

			$this->sectionSynchronizer->exportSections($userId);

			$this->eventSynchronizer->exportEvents($userId);

			$this->addPullEvent($connection, MasterPushHandler::MASTER_STAGE[3]);
		}
		catch (\Exception $e)
		{
			throw new Exception($e->getMessage(), $e->getCode(), $e);
		}

		CCalendar::ClearCache();

		NotificationManager::addFinishedSyncNotificationAgent(
			$userId,
			Helper::ACCOUNT_TYPE,
		);

		return $connection;
	}

	private function addPullEvent(Connection $connection, string $stage): void
	{
		Util::addPullEvent(
			PushCommand::ProcessSyncConnection,
			(int)$connection->getOwner()?->getId(),
			[
				'vendorName' => Helper::ACCOUNT_TYPE,
				'accountName' => $connection->getServer()->getUserName(),
				'stage' => $stage,
			],
		);
	}
}
