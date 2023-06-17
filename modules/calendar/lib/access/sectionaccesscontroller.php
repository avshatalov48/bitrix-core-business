<?php

namespace Bitrix\Calendar\Access;

use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\Model\UserModel;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\BaseAccessController;
use Bitrix\Main\Access\User\AccessibleUser;
use Bitrix\Calendar\Core\Mappers\Section;

class SectionAccessController extends BaseAccessController
{
	public static array $cache = [];

	private const ITEM_TYPE = 'SECTION';
	private const USER_TYPE = 'USER';

	protected function loadItem(int $itemId = null): ?AccessibleItem
	{
		$key = self::ITEM_TYPE . '_' . $itemId;
		if (!array_key_exists($key, static::$cache))
		{
			/**@var  \Bitrix\Calendar\Core\Section\Section $section */
			$section = (new Section())->getById($itemId);

			$sectionModel = SectionModel::createFromId($itemId);
			if ($section instanceof \Bitrix\Calendar\Core\Section\Section)
			{
				$owner = $section->getOwner();
				$ownerId = $owner ? $owner->getId() : 0;
				$sectionModel
					->setType($section->getType())
					->setOwnerId($ownerId)
				;
			}

			static::$cache[$key] = $sectionModel;
		}

		return static::$cache[$key];
	}

	protected function loadUser(int $userId): AccessibleUser
	{
		$key = self::USER_TYPE . '_' . $userId;
		if (!array_key_exists($key, static::$cache))
		{
			static::$cache[$key] = UserModel::createFromId($userId);
		}

		return static::$cache[$key];
	}
}