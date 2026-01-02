<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver;

use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventMessage;
use Bitrix\Calendar\Synchronization\Public\Command\Event\Google\CreateEventCommand;
use Bitrix\Calendar\Synchronization\Public\Command\Event\Google\DeleteEventCommand;
use Bitrix\Calendar\Synchronization\Public\Command\Event\Google\ExcludeEventDateCommand;
use Bitrix\Calendar\Synchronization\Public\Command\Event\Google\ReCreateRecurrenceCommand;
use Bitrix\Calendar\Synchronization\Public\Command\Event\Google\UpdateEventCommand;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventCreatedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventDateExcludedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventDeletedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventUpdatedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\RecreateRecurrenceEventMessage;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Command\CommandInterface;

class GoogleEventReceiver extends AbstractEventReceiver
{
	/**
	 * @throws ArgumentException
	 */
	protected function buildCommand(EventMessage $message): ?CommandInterface
	{
		if ($message instanceof EventDeletedMessage)
		{
			return new DeleteEventCommand(
				$message->vendorEventId,
				$message->vendorSectionId,
				$message->userId
			);
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

		if ($message instanceof RecreateRecurrenceEventMessage)
		{
			return new ReCreateRecurrenceCommand($event);
		}

		return null;
	}
}
