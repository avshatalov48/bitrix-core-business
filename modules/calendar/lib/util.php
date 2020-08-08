<?php
namespace Bitrix\Calendar;
use Bitrix\Main;

class Util
{
	private static $userAccessCodes = array();

	public static function isManagerForUser($managerId, $userId)
	{
		if (!isset(self::$userAccessCodes[$managerId]))
		{
			$codes = array();
			$r = \CAccess::getUserCodes($managerId);
			while($code = $r->fetch())
			{
				$codes[] = $code['ACCESS_CODE'];
			}
			self::$userAccessCodes[$managerId] = $codes;
		}

		return in_array('IU'.$userId, self::$userAccessCodes[$managerId]);
	}


	public static function isSectionStructureConverted()
	{
		return \Bitrix\Main\Config\Option::get('calendar', 'sectionStructureConverted', 'N') === 'Y';
	}

	public static function getTimestamp($date, $round = true, $getTime = true)
	{
		$timestamp = MakeTimeStamp($date, \CSite::getDateFormat($getTime ? "FULL" : "SHORT"));
		// Get rid of seconds
		if ($round)
		{
			$timestamp = round($timestamp / 60) * 60;
		}
		return $timestamp;
	}
	
	public static function isTimezoneValid($timeZone)
	{
		if (in_array($timeZone, timezone_identifiers_list()))
		{
			return true;
		}

		return false;
	}
}