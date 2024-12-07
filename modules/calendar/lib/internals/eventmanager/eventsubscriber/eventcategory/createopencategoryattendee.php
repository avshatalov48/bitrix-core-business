<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventCategory;

use Bitrix\Calendar\EventCategory\Event\AfterEventCategoryCreate;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Calendar\OpenEvents\Service\CategoryAttendeeService;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class CreateOpenCategoryAttendee implements EventSubscriberInterface
{
	use EventSubscriberResponseTrait;

	public function __invoke(Event $event): EventResult
	{
		$categoryId = (int)$event->getParameter('eventCategoryId');

		CategoryAttendeeService::getInstance()->createSystem($categoryId);

		return $this->makeSuccessResponse();
	}

	public function getEventClasses(): array
	{
		return [
			AfterEventCategoryCreate::class,
		];
	}
}
