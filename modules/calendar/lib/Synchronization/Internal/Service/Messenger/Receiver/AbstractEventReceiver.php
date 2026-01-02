<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventMessage;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Command\CommandInterface;
use Bitrix\Main\Command\Exception\CommandException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Messenger\Entity\MessageInterface;
use Bitrix\Main\Messenger\Internals\Exception\Receiver\UnprocessableMessageException;
use Bitrix\Main\Messenger\Receiver\AbstractReceiver;

abstract class AbstractEventReceiver extends AbstractReceiver
{
	use ExceptionProcessorTrait;

	private \Bitrix\Calendar\Core\Mappers\Event $eventRepository;

	public function __construct()
	{
		/** @var \Bitrix\Calendar\Core\Mappers\Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$this->eventRepository = $mapperFactory->getEvent();
	}

	/**
	 * @throws ArgumentException
	 */
	protected function getActiveEvent(int $eventId): ?Event
	{
		/** @var Event $event */
		$event = $this->eventRepository->getById($eventId);

		if (!$event || $event->isDeleted() || $event->getMeetingStatus() === 'N')
		{
			return null;
		}

		return $event;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function process(MessageInterface $message): void
	{
		if (!$message instanceof EventMessage)
		{
			throw new UnprocessableMessageException($message);
		}

		$command = $this->buildCommand($message);

		if (!$command)
		{
			return;
		}

		try
		{
			$command->run();
		}
		catch (CommandException $e)
		{
			$this->processException($e);
		}
	}

	abstract protected function buildCommand(EventMessage $message): ?CommandInterface;
}
