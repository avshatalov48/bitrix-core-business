<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Integration\Intranet;

use Bitrix\Main\Loader;

class User
{
	public static function isIntranet(int $userId): bool
	{
		if (!Loader::includeModule('intranet'))
		{
			return false;
		}

		if ($userId <= 0)
		{
			return false;
		}

		return (new \Bitrix\Intranet\User($userId))->isIntranet();
	}
}
