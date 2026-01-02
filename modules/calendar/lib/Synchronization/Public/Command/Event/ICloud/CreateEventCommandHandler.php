<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Event\ICloud;

use Bitrix\Calendar\Internals\Container;
use Bitrix\Calendar\Synchronization\Internal\Exception\LogicException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\ICloudEventSynchronizer;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Repository\Exception\PersistenceException;

class CreateEventCommandHandler
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
	 * @throws LogicException
	 */
	public function __invoke(CreateEventCommand $command): void
	{
		if ($command->event->isInstance())
		{
			$this->synchronizer->sendInstance($command->event);
		}
		else
		{
			$this->synchronizer->sendEvent($command->event);
		}
	}
}
