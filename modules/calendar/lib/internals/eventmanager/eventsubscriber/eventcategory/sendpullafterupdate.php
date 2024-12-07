<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventCategory;

use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\EventCategory\Event\AfterEventCategoryUpdate;
use Bitrix\Calendar\EventCategory\Service\EventCategoryPullService;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class SendPullAfterUpdate implements EventSubscriberInterface
{
	use EventSubscriberResponseTrait;

	public function __invoke(Event $event): EventResult
	{
		$eventCategoryId = $event->getParameter('eventCategoryId');
		$fields = $event->getParameter('fields');
		$userId = $event->getParameter('userId');

		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		/** @var EventCategory $event */
		$eventCategory = $mapperFactory->getEventCategory()->getById($eventCategoryId);

		EventCategoryPullService::getInstance()->updateEvent($eventCategory, $fields, $userId);

		return $this->makeSuccessResponse();
	}

	public function getEventClasses(): array
	{
		return [
			AfterEventCategoryUpdate::class,
		];
	}
}