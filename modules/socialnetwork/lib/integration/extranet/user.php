<?php

declare (strict_types=1);

namespace Bitrix\Socialnetwork\Integration\Extranet;

use Bitrix\Extranet\Service\ServiceContainer;
use Bitrix\Main\Loader;

class User
{
	public static function isCollaber(int $userId): bool
	{
		if (!Loader::includeModule('extranet'))
		{
			return false;
		}

		return
			ServiceContainer::getInstance()
				->getCollaberService()
				->isCollaberById($userId)
		;
	}
}