<?php
namespace Bitrix\Socialnetwork\Space\List;

final class SpaceListMode
{
	private const MODULE_NAME = 'socialnetwork';
	private const OPTION_NAME = 'spacesListState';
	private const DEFAULT_VALUE = Dictionary::SPACE_LIST_STATES['default'];

	public static function getOption(): string
	{
		$spacesListMode = \CUserOptions::GetOption(
			self::MODULE_NAME,
			self::OPTION_NAME,
			self::DEFAULT_VALUE,
		);

		if (!in_array($spacesListMode, Dictionary::SPACE_LIST_STATES, true))
		{
			$spacesListMode = self::DEFAULT_VALUE;
		}

		return $spacesListMode;
	}

	public static function setOption(int $userId, string $mode): void
	{
		if (in_array($mode, Dictionary::SPACE_LIST_STATES))
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
