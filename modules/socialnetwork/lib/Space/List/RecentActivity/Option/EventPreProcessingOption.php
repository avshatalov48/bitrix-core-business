<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Option;

use Bitrix\Main\Config\Option;

final class EventPreProcessingOption
{
	private const MODULE_NAME = 'socialnetwork';
	private const OPTION_NAME = 'spaces_activity_event_preprocessing';

	private const VALUES = [
		'Enabled' => 'Y',
		'Disabled' => 'N',
	];

	public static function isEnabled(): bool
	{
		return self::getOption() === self::VALUES['Enabled'];
	}

	private static function getOption(): string
	{
		return Option::get(
			self::MODULE_NAME,
			self::OPTION_NAME,
			self::VALUES['Disabled'],
		);
	}

	public static function setOption(string $value): void
	{
		if (in_array($value, self::VALUES))
		{
			Option::set(
				self::MODULE_NAME,
				self::OPTION_NAME,
				$value,
			);
		}
	}
}