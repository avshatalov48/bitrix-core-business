<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\Event;

use Bitrix\Calendar\Event\Event\AfterOpenEventCreated;
use Bitrix\Calendar\EventOption\Service\EventOptionService;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\DependentEventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\DependentEventSubscriberTrait;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\Event\Dto\CreateChannelThreadForEventDto;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class CreateEventOption implements EventSubscriberInterface, DependentEventSubscriberInterface
{
	use EventSubscriberResponseTrait;
	use DependentEventSubscriberTrait;

	public function handle(Event $event): EventResult
	{
		/** @var CreateChannelThreadForEventDto $threadCreatingResult */
		$threadCreatingResult = $this->getResultFromSubscriber(
			$event,
			CreateChannelThreadForEvent::class
		);

		EventOptionService::getInstance()->onEventCreated(
			(int)$event->getParameter('eventId'),
			$event->getParameter('command'),
			$threadCreatingResult->threadId,
		);

		return $this->makeSuccessResponse();
	}

	public function getEventClasses(): array
	{
		return [
			AfterOpenEventCreated::class,
		];
	}

	public static function getDependencies(): array
	{
		return [
			CreateChannelThreadForEvent::class,
		];
	}
}