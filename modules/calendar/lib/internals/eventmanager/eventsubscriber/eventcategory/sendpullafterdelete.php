<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventCategory;

use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\EventCategory\Event\AfterEventCategoryDelete;
use Bitrix\Calendar\EventCategory\Service\EventCategoryPullService;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class SendPullAfterDelete implements EventSubscriberInterface
{
	use EventSubscriberResponseTrait;

	public function __invoke(Event $event): EventResult
	{
		$eventCategoryId = $event->getParameter('eventCategoryId');

		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		/** @var EventCategory $event */
		$eventCategory = $mapperFactory->getEventCategory()->getById($eventCategoryId);

		EventCategoryPullService::getInstance()->deleteEvent($eventCategory);

		return $this->makeSuccessResponse();
	}

	public function getEventClasses(): array
	{
		return [
			AfterEventCategoryDelete::class,
		];
	}
}