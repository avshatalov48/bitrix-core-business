<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Event\Office365;

use Bitrix\Calendar\Internals\Container;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Office365EventSynchronizer;
use Bitrix\Main\Repository\Exception\PersistenceException;

class DeleteEventCommandHandler
{
	private Office365EventSynchronizer $synchronizer;

	public function __construct()
	{
		$this->synchronizer = Container::getOffice365EventSynchronizer();
	}

	/**
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	public function __invoke(DeleteEventCommand $command): void
	{
		$this->synchronizer->deleteEvent($command->vendorEventId, $command->userId);
	}
}
