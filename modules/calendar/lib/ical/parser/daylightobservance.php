<?php


namespace Bitrix\Calendar\ICal\Parser;


class DaylightObservance extends Observance
{
	public const TYPE = 'DAYLIGHT';
	/**
	 * @return DaylightObservance
	 */
	public static function createInstance(): DaylightObservance
	{
		return new self();
	}
}