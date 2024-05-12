<?php

namespace Bitrix\Calendar\Integration\Tasks;

use Bitrix\Main\Loader;
use Bitrix\Tasks\Util\User;

class TaskUser
{
	public static function getId(): int
	{
		if (!Loader::includeModule('tasks'))
		{
			return 0;
		}

		return User::getId();
	}
}