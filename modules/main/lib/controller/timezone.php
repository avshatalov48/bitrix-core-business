<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */
namespace Bitrix\Main\Controller;

use Bitrix\Main;

class Timezone extends Main\Engine\Controller
{
	public function setAction(string $timezone)
	{
		global $USER;

		try
		{
			new \DateTimeZone($timezone);
		}
		catch (\Throwable)
		{
			$this->addError(new Main\Error("Incorrect timezone.", "ERR_PARAMS"));
			return null;
		}

		$USER->Update(
			$USER->GetID(),
			[
				"TIME_ZONE" => $timezone,
				"TIME_ZONE_OFFSET" => \CTimeZone::calculateOffset($timezone),
			]
		);

		// remember in the current session
		$USER->SetParam("TIME_ZONE", $timezone);

		return true;
	}
}
