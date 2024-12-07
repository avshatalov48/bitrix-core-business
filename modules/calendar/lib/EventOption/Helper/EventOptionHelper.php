<?php

namespace Bitrix\Calendar\EventOption\Helper;

use Bitrix\Calendar\EventOption\EventOptionRepository;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventOptionTable;
use Bitrix\Calendar\OpenEvents\Service\CategoryService;

final class EventOptionHelper
{
	private const SYSTEM_CATEGORY_ID = 0;

	public static function changeCategoryForEvents(
		int $prevCategory,
		int $newCategoryId = self::SYSTEM_CATEGORY_ID
	): bool
	{
		$eventOptionsIds = EventOptionRepository::getIdsByCategoryId($prevCategory);
		if (!$eventOptionsIds)
		{
			return true;
		}

		$updateResult = OpenEventOptionTable::updateMulti($eventOptionsIds, ['CATEGORY_ID' => $newCategoryId]);

		CategoryService::getInstance()->updateEventsCounter(
			eventCategoryId: $newCategoryId,
			value: count($eventOptionsIds)
		);

		return $updateResult->isSuccess();
	}
}
