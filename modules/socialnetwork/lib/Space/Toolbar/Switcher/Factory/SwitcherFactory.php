<?php

namespace Bitrix\Socialnetwork\Space\Toolbar\Switcher\Factory;

use Bitrix\Socialnetwork\Space\Toolbar\Switcher\Mode\SmartTracking;
use Bitrix\Socialnetwork\Space\Toolbar\Switcher\Option\Follow;
use Bitrix\Socialnetwork\Space\Toolbar\Switcher\Option\Pin;
use Bitrix\Socialnetwork\Space\Toolbar\Switcher\SwitcherInterface;

class SwitcherFactory
{
	public const SMART_TRACKING = 'smart_tracking';
	public const PIN = 'pinner';
	public const FOLLOW = 'follow';

	public static function get(string $type, int $userId, ?int $spaceId, string $code = ''): ?SwitcherInterface
	{
		/** @var SwitcherInterface $class */
		$class = static::getClass($type);
		if (is_null($class))
		{
			return null;
		}

		return $class::get($userId, $spaceId, empty($code) ? $class::getDefaultCode() : $code);
	}

	private static function getClass(string $type): ?string
	{
		return match ($type)
		{
			static::SMART_TRACKING => SmartTracking::class,
			static::PIN => Pin::class,
			static::FOLLOW => Follow::class,
			default => null,
		};
	}
}