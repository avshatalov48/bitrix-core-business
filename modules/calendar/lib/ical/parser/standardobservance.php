<?php


namespace Bitrix\Calendar\ICal\Parser;


class StandardObservance extends Observance
{
	public const TYPE = 'STANDARD';

	/**
	 * @return StandardObservance
	 */
	public static function createInstance(): StandardObservance
	{
		return new self();
	}
}