<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Event\Office365;

use Bitrix\Calendar\Internals\Container;
use Bitrix\Calendar\Synchronization\Internal\Exception\LogicException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Office365EventSynchronizer;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Repository\Exception\PersistenceException;

class UpdateEventCommandHandler
{
	private Office365EventSynchronizer $synchronizer;

	public function __construct()
	{
		$this->synchronizer = Container::getOffice365EventSynchronizer();
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws RepositoryReadException
	 * @throws SynchronizerException
	 * @throws LogicException
	 */
	public function __invoke(UpdateEventCommand $command): void
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
