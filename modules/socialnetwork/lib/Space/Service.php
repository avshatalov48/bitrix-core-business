<?php

namespace Bitrix\Socialnetwork\Space;

use Bitrix\Main\Config\Option;

final class Service
{
	public static function isAvailable(bool $isPublic = false): bool
	{
		if ($isPublic)
		{
			return \CUserOptions::getOption('socialnetwork.space', 'space_enabled', 'N') === 'Y';
		}

		$isSpaceEnabled = Option::get('socialnetwork', 'space_enabled', 'N') === 'Y';
		if (!$isSpaceEnabled)
		{
			return \CUserOptions::getOption('socialnetwork.space', 'space_enabled', 'N') === 'Y';
		}

		return true;
	}
}