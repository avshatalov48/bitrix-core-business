<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Event\ICloud;

use Bitrix\Calendar\Internals\Container;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\ICloudEventSynchronizer;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Repository\Exception\PersistenceException;

class ExcludeEventDateCommandHandler
{
	private ICloudEventSynchronizer $synchronizer;

	public function __construct()
	{
		$this->synchronizer = Container::getICloudEventSynchronizer();
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	public function __invoke(ExcludeEventDateCommand $command): void
	{
		$this->synchronizer->deleteInstance($command->event);
	}
}
