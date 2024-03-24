<?php

namespace Bitrix\Calendar\Update;

use Bitrix\Calendar\Sync\GoogleApiPush;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;
use CAgent;

class InitLocalDataToGoogle extends Stepper
{
	protected static $moduleId = "calendar";

	public static function className()
	{
		return get_called_class();
	}

	public function execute(array &$result)
	{
		return self::FINISH_EXECUTION;
	}
}