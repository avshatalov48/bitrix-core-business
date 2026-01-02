<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Common\Office365;

use Bitrix\Calendar\Core\Role\Helper;
use Bitrix\Calendar\Core\Role\User;
use Bitrix\Calendar\Integration\Pull\PushCommand;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Managers\NotificationManager;
use Bitrix\Calendar\Sync\Office365\StartSyncController;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Util;
use Bitrix\Main\SystemException;
use Throwable;

class CreateConnectionCommandHandler
{
	/**
	 * @throws Exception
	 */
	public function __invoke(CreateConnectionCommand $command): ?Connection
	{
		try
		{
			$owner = Helper::getRole($command->userId, User::TYPE);
		}
		catch (SystemException $e)
		{
			throw new Exception('Unable to create owner of connection for Office 365', $e->getCode(), $e);
		}

		$service = new StartSyncController($owner);

		$pusher = static function ($result) use ($owner)
		{
			Util::addPullEvent(
				PushCommand::ProcessSyncConnection,
				$owner->getId(),
				(array) $result
			);

			if ($result['stage'] === StartSyncController::STATUSES['export_finished'])
			{
				NotificationManager::addFinishedSyncNotificationAgent($owner->getId(), $result['vendorName']);
			}
		};

		try
		{
			return $service->addStatusHandler($pusher)->start();
		}
		catch (Throwable $e)
		{
			throw new Exception('Unable to create connection for Office 365', $e->getCode(), $e);
		}
	}
}
