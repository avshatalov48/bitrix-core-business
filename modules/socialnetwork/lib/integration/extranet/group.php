<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Integration\Extranet;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CExtranet;

class Group
{
	/**
	 * @throws LoaderException
	 */
	public static function isExtranetGroup(int $groupId): bool
	{
		if (!Loader::includeModule('extranet'))
		{
			return false;
		}

		return CExtranet::IsExtranetSocNetGroup($groupId);
	}
}