<?php

namespace Bitrix\Calendar\Update;

use Bitrix\Calendar\Sync\Google\Dictionary;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Type;

class CorrectEventInGoogle extends Stepper
{
	protected static $moduleId = "calendar";

	public static function className()
	{
		return get_called_class();
	}

	/**
	 * @param array $result
	 * @return bool
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function execute(array &$result): bool
	{
		return self::FINISH_EXECUTION;
	}
}