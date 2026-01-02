<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Infrastructure\Agent\Vendor;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Main\Command\CommandInterface;
use Bitrix\Main\Command\Exception\CommandException;

abstract class AbstractSynchronizationAgent
{
	abstract protected static function getSynchronizationCommand(Connection $connection): ?CommandInterface;

	public static function synchronizeConnectionFully(Connection $connection): void
	{
		if ($command = static::getSynchronizationCommand($connection))
		{
			try
			{
				$result = $command->run();

				if (!$result->isSuccess())
				{
					$connection->setSyncStatus(Dictionary::SYNC_STATUS['failed']);
				}
			}
			catch (CommandException)
			{
				$connection->setSyncStatus(Dictionary::SYNC_STATUS['failed']);
			}
		}
	}
}
