<?php

namespace Bitrix\Socialnetwork\Internals\EventService;

use Bitrix\Main\Type\DateTime;

class CleanAgent
{
	private const TTL = 2*24*3600;

	private static $processing = false;

	public static function execute()
	{
		if (self::$processing)
		{
			return self::getAgentName();
		}

		$filter = [
			'>=PROCESSED' => DateTime::createFromTimestamp(0),
			'<PROCESSED' => DateTime::createFromTimestamp(time() - self::TTL)
		];
		EventTable::deleteList($filter);


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