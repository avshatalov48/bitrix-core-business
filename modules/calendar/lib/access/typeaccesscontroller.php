<?php

namespace Bitrix\Calendar\Access;

use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\Model\UserModel;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\BaseAccessController;
use Bitrix\Main\Access\User\AccessibleUser;

class TypeAccessController extends BaseAccessController
{
	public static array $cache = [];

	private const USER_TYPE = 'USER';

	protected function loadItem(int $itemId = null): ?AccessibleItem
	{
		return null;
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