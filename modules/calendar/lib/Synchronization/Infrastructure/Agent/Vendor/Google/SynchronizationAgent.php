<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Infrastructure\Agent\Vendor\Google;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Google\Factory;
use Bitrix\Calendar\Synchronization\Infrastructure\Agent\Vendor\AbstractSynchronizationAgent;
use Bitrix\Calendar\Synchronization\Public\Command\Common\Google\SynchronizeConnectionCommand;
use Bitrix\Main\Command\CommandInterface;

class SynchronizationAgent extends AbstractSynchronizationAgent
{
	protected static function getSynchronizationCommand(Connection $connection): ?CommandInterface
	{
		if ($connection->getAccountType() === Factory::SERVICE_NAME)
		{
			if ($connection->getOwner())
			{
				return new SynchronizeConnectionCommand($connection->getOwner()->getId());
			}
		}

		return null;
	}
}
