<?php

namespace Bitrix\Calendar\Access;

use Bitrix\Calendar\Access\Model\EventCategoryModel;
use Bitrix\Calendar\Access\Model\UserModel;
use Bitrix\Calendar\Core\Mappers\EventCategory;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\BaseAccessController;
use Bitrix\Main\Access\User\AccessibleUser;

final class EventCategoryAccessController extends BaseAccessController
{
	public static array $cache = [];

	private const ITEM_TYPE = 'EVENT_CATEGORY';
	private const USER_TYPE = 'USER';

	protected function loadItem(int $itemId = null): ?AccessibleItem
	{
		$key = self::ITEM_TYPE . '_' . $itemId;
		if (!array_key_exists($key, self::$cache))
		{
			$eventCategory = (new EventCategory())->getById($itemId);

			$eventCategoryModel = $eventCategory
				? EventCategoryModel::createFromObject($eventCategory)
				: EventCategoryModel::createNew();

			self::$cache[$key] = $eventCategoryModel;
		}

		return self::$cache[$key];
	}

	protected function loadUser(int $userId): AccessibleUser
	{
		$key = self::USER_TYPE . '_' . $userId;
		if (!array_key_exists($key, self::$cache))
		{
			self::$cache[$key] = UserModel::createFromId($userId);
		}

		return self::$cache[$key];
	}
}
