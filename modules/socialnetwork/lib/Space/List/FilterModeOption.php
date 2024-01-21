<?php
namespace Bitrix\Socialnetwork\Space\List;

final class FilterModeOption
{
	private const MODULE_NAME = 'socialnetwork';
	private const OPTION_NAME = 'spaces_list_filter_mode';

	public static function getOption(int $userId): string
	{
		return \CUserOptions::GetOption(
			self::MODULE_NAME,
			self::OPTION_NAME,
			Dictionary::FILTER_MODES['my'],
			$userId,
		);
	}

	public static function setOption(int $userId, string $mode): void
	{
		if (in_array($mode, Dictionary::FILTER_MODES))
		{
			\CUserOptions::SetOption(
				self::MODULE_NAME,
				self::OPTION_NAME,
				$mode,
				false,
				$userId,
			);
		}
	}
}