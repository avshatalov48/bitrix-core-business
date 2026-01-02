<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Event\ICloud;

use Bitrix\Calendar\Internals\Container;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\ICloudEventSynchronizer;
use Bitrix\Main\Repository\Exception\PersistenceException;

class DeleteEventCommandHandler
{
	private ICloudEventSynchronizer $synchronizer;

	public function __construct()
	{
		$this->synchronizer = Container::getICloudEventSynchronizer();
	}

	/**
	 * @throws SynchronizerException
	 * @throws PersistenceException
	 */
	public function __invoke(DeleteEventCommand $command): void
	{
		$this->synchronizer->deleteEvent($command->vendorEventId, $command->vendorSectionId, $command->userId);
	}
}
