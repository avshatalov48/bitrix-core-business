<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Event\Google;

use Bitrix\Calendar\Internals\Container;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\GoogleEventSynchronizer;
use Bitrix\Main\Repository\Exception\PersistenceException;

class ExcludeEventDateCommandHandler
{
	private GoogleEventSynchronizer $synchronizer;

	public function __construct()
	{
		$this->synchronizer = Container::getGoogleEventSynchronizer();
	}

	/**
	 * @throws RepositoryReadException
	 * @throws SynchronizerException
	 * @throws PersistenceException
	 */
	public function __invoke(ExcludeEventDateCommand $command): void
	{
		$this->synchronizer->deleteInstance(
			$command->event,
			$command->excludedDate,
			$command->event->getOriginalDateFrom()
		);
	}
}
