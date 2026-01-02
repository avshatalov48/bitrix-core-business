<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common;

use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Repository\EventConnectionRepository;

trait EventSynchronizerTrait
{
	private readonly EventConnectionRepository $eventConnectionRepository;

	/**
	 * @throws SynchronizerException
	 */
	private function getEventConnection(int $eventId, int $connectionId): ?EventConnection
	{
		try
		{
			return $this->eventConnectionRepository->getOneByEventAndConnectionId($eventId, $connectionId);
		}
		catch (RepositoryReadException $e)
		{
			throw new SynchronizerException(
				sprintf('Unable to get an EventConnection: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}
	}
}
