<?php

namespace Bitrix\Calendar\Integration\Intranet;

use Bitrix\Intranet\Util;
use Bitrix\Main\Loader;

class UserService
{
	public function isNotIntranetUser(int $userId): bool
	{
		return Loader::includeModule('intranet') && !Util::isIntranetUser($userId);
	}
}
