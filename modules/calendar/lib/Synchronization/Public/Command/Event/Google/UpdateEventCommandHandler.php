<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Event\Google;

use Bitrix\Calendar\Internals\Container;
use Bitrix\Calendar\Synchronization\Internal\Exception\LogicException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\GoogleEventSynchronizer;
use Bitrix\Main\Repository\Exception\PersistenceException;

class UpdateEventCommandHandler
{
	private GoogleEventSynchronizer $synchronizer;

	public function __construct()
	{
		$this->synchronizer = Container::getGoogleEventSynchronizer();
	}

	/**
	 * @param UpdateEventCommand $command
	 * @throws RepositoryReadException
	 * @throws SynchronizerException
	 * @throws PersistenceException
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
