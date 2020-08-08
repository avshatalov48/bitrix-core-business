<?php
/**
* Bitrix Framework
* @package bitrix
* @subpackage socialnetwork
* @copyright 2001-2019 Bitrix
*/
namespace Bitrix\Socialnetwork\Integration\Calendar;

class ApiVersion
{
	public static function isEventEditFormAvailable()
	{
		return \Bitrix\Main\UI\Extension::getConfig('calendar.eventeditform') !== null;
	}

	public static function getAddEventInLivefeedJs()
	{
		if (self::isEventEditFormAvailable())
		{
			return "if (BX.Calendar && BX.Calendar.SliderLoader){new BX.Calendar.SliderLoader('NEW').show();}";
		}
		else
		{
			return "BX.onCustomEvent('onCalendarLiveFeedShown');";
		}
	}
}
?>