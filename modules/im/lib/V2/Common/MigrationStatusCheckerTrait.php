<?php

namespace Bitrix\Im\V2\Common;

use Bitrix\Main\Config\Option;

trait MigrationStatusCheckerTrait
{
	protected static string $moduleId = 'im';
	protected static ?bool $isMigrationFinished = null;

	protected static function isMigrationFinished(): bool
	{
		if (isset(static::$isMigrationFinished))
		{
			return static::$isMigrationFinished;
		}

		$isFinished = Option::get(static::$moduleId, static::$migrationOptionName, 'N');
		static::$isMigrationFinished = ($isFinished === 'Y');

		return static::$isMigrationFinished;
	}
}