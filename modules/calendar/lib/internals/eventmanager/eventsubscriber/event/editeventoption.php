<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\Event;

use Bitrix\Calendar\Event\Event\AfterOpenEventEdited;
use Bitrix\Calendar\EventOption\Service\EventOptionService;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class EditEventOption implements EventSubscriberInterface
{
	use EventSubscriberResponseTrait;

	public function __invoke(Event $event): EventResult
	{
		EventOptionService::getInstance()->onEventEdited(
			(int)$event->getParameter('eventId'),
			$event->getParameter('command')
		);

		return $this->makeSuccessResponse();
	}

	public function getEventClasses(): array
	{
		return [
			AfterOpenEventEdited::class,
		];
	}
}