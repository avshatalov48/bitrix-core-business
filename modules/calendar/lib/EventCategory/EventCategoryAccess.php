<?php

namespace Bitrix\Calendar\EventCategory;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventCategoryAccessController;
use Bitrix\Calendar\Access\Model\EventCategoryModel;
use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\EventCategory\Dto\EventCategoryPermissions;
use Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory;

final class EventCategoryAccess
{
	public static function getPermissionsForObject(EventCategory $category, int $userId): EventCategoryPermissions
	{
		return self::getPermissions(EventCategoryModel::createFromObject($category), $userId);
	}

	public static function getPermissionsForEntity(OpenEventCategory $category, int $userId): EventCategoryPermissions
	{
		return self::getPermissions(EventCategoryModel::createFromEntity($category), $userId);
	}

	private static function getPermissions(EventCategoryModel $model, int $userId): EventCategoryPermissions
	{
		$eventCategoryAccessController = new EventCategoryAccessController($userId);
		$accessResult = $eventCategoryAccessController->batchCheck(
			[
				ActionDictionary::ACTION_EVENT_CATEGORY_EDIT => [],
//				ActionDictionary::ACTION_EVENT_CATEGORY_DELETE => [],
			],
			$model,
		);

		return new EventCategoryPermissions(
			edit: $accessResult[ActionDictionary::ACTION_EVENT_CATEGORY_EDIT],
			delete: false,
		);
	}
}
