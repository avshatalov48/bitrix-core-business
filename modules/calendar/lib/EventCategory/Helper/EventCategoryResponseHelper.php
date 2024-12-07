<?php

namespace Bitrix\Calendar\EventCategory\Helper;

use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\EventCategory\Dto\EventCategoryPermissions;
use Bitrix\Calendar\EventCategory\EventCategoryAccess;
use Bitrix\Calendar\Internals\Counter;
use Bitrix\Calendar\OpenEvents\Item;
use Bitrix\Calendar\OpenEvents\Provider;

final class EventCategoryResponseHelper
{
	public static function prepareEventCategoryForUserResponse(
		EventCategory $eventCategory,
		int $userId = 0,
		?bool $isMuted = null,
	): Item\Category
	{
		$categoryId = $eventCategory->getId();
		$categoryNewCounters = $userId === 0
			? null
			: Counter::getInstance($userId)
				->get(Counter\CounterDictionary::COUNTER_OPEN_EVENTS, $categoryId);

		$categoryProvider = new Provider\CategoryProvider();

		return new Item\Category(
			id: $categoryId,
			closed: $eventCategory->getClosed(),
			name: $categoryProvider->prepareCategoryName($eventCategory->getName()),
			description: $categoryProvider->prepareCategoryDescription($eventCategory->getDescription()),
			creatorId: $eventCategory->getCreatorId(),
			eventsCount: $eventCategory->getEventsCount(),
			permissions: $userId === 0
				? new EventCategoryPermissions(false, false)
				: EventCategoryAccess::getPermissionsForObject($eventCategory, $userId),
			channelId: $eventCategory->getChannelId(),
			isMuted: $isMuted,
			newCount: $categoryNewCounters,
		);
	}
}
