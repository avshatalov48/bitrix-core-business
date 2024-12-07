<?php

namespace Bitrix\Socialnetwork\Internals\Space\LiveWatch;

class CleanAgent
{
	private static $processing = false;

	public static function execute()
	{
		if (self::$processing)
		{
			return self::getAgentName();
		}

		LiveWatchService::getInstance()->removeStaleRecords();

		return self::getAgentName();
	}

	/**
	 * @return string
	 */
	private static function getAgentName(): string
	{
		return static::class . "::execute();";
	}
}