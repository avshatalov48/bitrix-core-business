<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Common\Office365;

use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\ConnectionSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Main\DI\ServiceLocator;

class SynchronizeConnectionCommandHandler
{
	private ConnectionSynchronizer $connectionSynchronizer;

	public function __construct()
	{
		$this->connectionSynchronizer = ServiceLocator::getInstance()->get(ConnectionSynchronizer::class);
	}

	/**
	 * @throws Exception
	 */
	public function __invoke(SynchronizeConnectionCommand $command): void
	{
		$this->connectionSynchronizer->synchronizeUserConnection($command->userId);
	}
}
