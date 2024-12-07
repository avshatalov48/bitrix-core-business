<?php

namespace Bitrix\Socialnetwork\Permission\Trait;

use Bitrix\Main\Access\User\AccessibleUser;
use Bitrix\Socialnetwork\Permission\User\UserModel;

trait AccessUserTrait
{
	protected function loadUser(int $userId): AccessibleUser
	{
		$key = 'USER_'.$userId;
		if (!array_key_exists($key, static::$cache))
		{
			static::$cache[$key] = UserModel::createFromId($userId);
		}
		return static::$cache[$key];
	}
}