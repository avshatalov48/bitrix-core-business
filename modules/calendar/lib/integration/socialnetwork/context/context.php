<?php

namespace Bitrix\Calendar\Integration\SocialNetwork\Context;

use Bitrix\Calendar\Integration\SocialNetwork\SpaceService;

abstract class Context
{
	private static ?string $spaces = null;
	private static string $default = 'default';

	public static function getSpaces(): ?string
	{
		if (!is_null(self::$spaces))
		{
			return self::$spaces;
		}

		if (SpaceService::isAvailable())
		{
			self::$spaces = \Bitrix\Socialnetwork\Livefeed\Context\Context::SPACES;
		}

		return self::$spaces;
	}

	public static function getDefault(): string
	{
		return self::$default;
	}
}