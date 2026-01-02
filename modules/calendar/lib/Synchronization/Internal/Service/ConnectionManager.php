<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Integration\Pull\PushCommand;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Google\Helper;
use Bitrix\Calendar\Util;
use Bitrix\Main\Application;

class ConnectionManager
{
	public function updateConnection(Connection $connection): void
	{
		$connection->setLastSyncTime(new Date());

		(new \Bitrix\Calendar\Core\Mappers\Connection())->update($connection);

		$accountType = $connection->getAccountType() === Helper::GOOGLE_ACCOUNT_TYPE_API
			? 'google'
			: $connection->getAccountType()
		;

		Util::addPullEvent(
			PushCommand::RefreshSyncStatus,
			(int)$connection->getOwner()?->getId(),
			[
				'syncInfo' => [
					$accountType => [
						'status' => $this->getSyncStatus($connection->getStatus()),
						'type' => $accountType,
						'connected' => true,
						'id' => $connection->getId(),
						'syncOffset' => 0,
					],
				],
				'requestUid' => Util::getRequestUid(),
			]
		);
	}

	private function getSyncStatus(?string $status): bool
	{
		return $status && preg_match("/^\[(2\d\d|0)\][a-z0-9 _]*/i", $status);
	}

	public function markFailedConnectionAsDeleted(Connection $connection): void
	{
		try
		{
			(new \Bitrix\Calendar\Core\Mappers\Connection())->patch($connection, ['IS_DELETED' => 'Y']);
		}
		catch (\Exception $e)
		{
			Application::getInstance()->getExceptionHandler()->writeToLog($e);
		}
	}
}
