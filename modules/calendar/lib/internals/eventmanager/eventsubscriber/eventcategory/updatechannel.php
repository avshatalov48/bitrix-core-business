<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventCategory;

use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\EventCategory\Event\AfterEventCategoryUpdate;
use Bitrix\Calendar\Integration;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class UpdateChannel implements EventSubscriberInterface
{
	use EventSubscriberResponseTrait;

	/** need to update channel only if this fields updated */
	private const FIELDS_FOR_CHANNEL_UPDATE = ['name', 'description'];

	public function __invoke(Event $event): EventResult
	{
		$eventCategoryId = $event->getParameter('eventCategoryId');
		$fields = $event->getParameter('fields');

		if ($fields && !in_array($fields, self::FIELDS_FOR_CHANNEL_UPDATE, true))
		{
			return new EventResult(EventResult::UNDEFINED);
		}

		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$eventCategory = $mapperFactory->getEventCategory()->getById($eventCategoryId);

		ServiceLocator::getInstance()->get(Integration\Im\EventCategoryServiceInterface::class)
			->updateChannel($eventCategory);

		return $this->makeSuccessResponse();
	}

	public function getEventClasses(): array
	{
		return [
			AfterEventCategoryUpdate::class,
		];
	}
}