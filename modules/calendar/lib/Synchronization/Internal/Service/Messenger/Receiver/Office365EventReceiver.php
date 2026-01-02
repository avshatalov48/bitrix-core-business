<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver;

use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventMessage;
use Bitrix\Calendar\Synchronization\Public\Command\Event\Office365\CreateEventCommand;
use Bitrix\Calendar\Synchronization\Public\Command\Event\Office365\ExcludeEventDateCommand;
use Bitrix\Calendar\Synchronization\Public\Command\Event\Office365\DeleteEventCommand;
use Bitrix\Calendar\Synchronization\Public\Command\Event\Office365\UpdateEventCommand;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventCreatedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventDateExcludedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventDeletedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventUpdatedMessage;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Command\CommandInterface;

class Office365EventReceiver extends AbstractEventReceiver
{
	/**
	 * @throws ArgumentException
	 */
	protected function buildCommand(EventMessage $message): ?CommandInterface
	{
		if ($message instanceof EventDeletedMessage)
		{
			return new DeleteEventCommand($message->vendorEventId, $message->userId);
		}

		$event = $this->getActiveEvent($message->eventId);

		if (!$event)
		{
			return null;
		}

		if ($message instanceof EventCreatedMessage)
		{
			return new CreateEventCommand($event);
		}

		if ($message instanceof EventUpdatedMessage)
		{
			return new UpdateEventCommand($event);
		}

		if ($message instanceof EventDateExcludedMessage)
		{
			return new ExcludeEventDateCommand($event, $message->getExcludedDate());
		}

		return null;
	}
}
