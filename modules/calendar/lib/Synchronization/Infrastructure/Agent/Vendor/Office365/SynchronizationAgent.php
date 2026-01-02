<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Infrastructure\Agent\Vendor\Office365;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Office365\Factory;
use Bitrix\Calendar\Synchronization\Infrastructure\Agent\Vendor\AbstractSynchronizationAgent;
use Bitrix\Calendar\Synchronization\Public\Command\Common\Office365\SynchronizeConnectionCommand;
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
