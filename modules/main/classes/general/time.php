<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

use Bitrix\Main;

class CTimeZone
{
	protected static $enabled = 1;
	protected static $useTimeZones = false;

	public static function Enabled()
	{
		return (self::$enabled > 0 && self::OptionEnabled());
	}

	public static function OptionEnabled()
	{
		if (self::$useTimeZones === false)
		{
			self::$useTimeZones = COption::GetOptionString("main", "use_time_zones", "N");
		}
		return (self::$useTimeZones == "Y");
	}

	public static function Disable()
	{
		self::$enabled--;
	}

	public static function Enable()
	{
		self::$enabled++;
	}

	public static function GetZones()
	{
		IncludeModuleLangFile(__FILE__);

		$aTZ = [];
		static $aExcept = ["Etc/", "GMT", "UTC", "UCT", "HST", "PST", "MST", "CST", "EST", "CET", "MET", "WET", "EET", "PRC", "ROC", "ROK", "W-SU"];
		foreach (DateTimeZone::listIdentifiers() as $tz)
		{
			foreach ($aExcept as $ex)
			{
				if (str_starts_with($tz, $ex))
				{
					continue 2;
				}
			}
			try
			{
				$oTz = new DateTimeZone($tz);
				$aTZ[$tz] = ['timezone_id' => $tz, 'offset' => $oTz->getOffset(new DateTime("now", $oTz))];
			}
			catch (Throwable)
			{
			}
		}

		uasort($aTZ, function ($a, $b) {
			if ($a['offset'] == $b['offset'])
			{
				return strcmp($a['timezone_id'], $b['timezone_id']);
			}
			return ($a['offset'] < $b['offset'] ? -1 : 1);
		});

		$aZones = ["" => GetMessage("tz_local_time")];
		foreach ($aTZ as $z)
		{
			$offset = '';
			if ($z['offset'] != 0)
			{
				$offset = ' ' . Main\Type\DateTime::secondsToOffset($z['offset'], ':');
			}
			$aZones[$z['timezone_id']] = '(UTC' . $offset . ') ' . $z['timezone_id'];
		}

		return $aZones;
	}

	public static function SetAutoCookie()
	{
		global $APPLICATION, $USER;

		$cookiePrefix = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');

		if (self::IsAutoTimeZone($USER->GetParam("AUTO_TIME_ZONE")))
		{
			$cookieDate = (new \Bitrix\Main\Type\DateTime())->add("12M");
			$cookieDate->setDate((int)$cookieDate->format('Y'), (int)$cookieDate->format('m'), 1);
			$cookieDate->setTime(0, 0);

			$tzFunc = '';
			if ($USER->IsAuthorized())
			{
				CJSCore::Init(['ajax']);

				$tzFunc = '
						if (timezone !== "' . CUtil::JSEscape($USER->GetParam("TIME_ZONE")) . '")
						{
							BX.ready(function () {
								BX.ajax.runAction(
									"main.timezone.set",
									{
										data: {
											timezone: timezone
										}
									}
								);
							});
						}
				';
			}

			$APPLICATION->AddHeadString(
				'<script>
					if (Intl && Intl.DateTimeFormat)
					{
						const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
						document.cookie = "' . $cookiePrefix . '_TZ=" + timezone + "; path=/; expires=' . $cookieDate->format("r") . '";
						' . $tzFunc . '
					}
				</script>',
				true
			);
		}
		elseif (isset($_COOKIE[$cookiePrefix . "_TZ"]))
		{
			// delete the cookie
			setcookie($cookiePrefix . "_TZ", "", time() - 3600, "/");
		}
	}

	public static function getTzCookie(): ?string
	{
		$context = Main\Context::getCurrent();
		$cookie = $context?->getRequest()->getCookie('TZ');

		return is_string($cookie)? $cookie : null;
	}

	public static function IsAutoTimeZone($autoTimeZone)
	{
		$autoTimeZone = trim((string)$autoTimeZone);

		if ($autoTimeZone === "Y")
		{
			return true;
		}

		if (empty($autoTimeZone))
		{
			static $defAutoZone = null;
			if ($defAutoZone === null)
			{
				$defAutoZone = (COption::GetOptionString("main", "auto_time_zone", "N") == "Y");
			}

			return $defAutoZone;
		}

		return false;
	}

	/**
	 * @return null
	 * @deprecated Does nothing.
	 */
	public static function GetCookieValue()
	{
		return null;
	}

	/**
	 * @deprecated Does nothing.
	 */
	public static function SetCookieValue($timezoneOffset)
	{
	}

	/**
	 * @param int|null $USER_ID If USER_ID is set offset is taken from DB
	 * @param bool $forced If set, offset is calculated regardless enabling/disabling by functions Enable()/Disable().
	 * @return int
	 */
	public static function GetOffset($USER_ID = null, $forced = false)
	{
		global $USER;

		if ($forced)
		{
			if (!self::OptionEnabled())
			{
				return 0;
			}
		}
		else
		{
			if (!self::Enabled())
			{
				return 0;
			}
		}

		$timeZone = '';

		if ($USER_ID !== null && $USER?->GetID() != $USER_ID)
		{
			$dbUser = CUser::GetList('id', 'asc', ['ID_EQUAL_EXACT' => $USER_ID], ['FIELDS' => ['TIME_ZONE', 'TIME_ZONE_OFFSET']]);
			if (($arUser = $dbUser->Fetch()))
			{
				if (empty($arUser["TIME_ZONE"]))
				{
					// deprecated, return last known offset from the DB
					return intval($arUser["TIME_ZONE_OFFSET"]);
				}
				$timeZone = $arUser["TIME_ZONE"];
			}
		}
		elseif (is_object($USER))
		{
			// current user
			$timeZone = (string)$USER->GetParam("TIME_ZONE");
			if (empty($timeZone))
			{
				if (self::IsAutoTimeZone($USER->GetParam("AUTO_TIME_ZONE")))
				{
					if (($cookie = static::getTzCookie()) !== null)
					{
						// auto time zone from the cookie
						$timeZone = $cookie;
					}
				}
			}
		}

		return static::getTimezoneOffset($timeZone);
	}

	/**
	 * Returns timezone offset according to global settings.
	 * @param string $timeZone
	 * @return int
	 */
	public static function getTimezoneOffset(string $timeZone): int
	{
		if (!self::OptionEnabled())
		{
			return 0;
		}
		if ($timeZone == '')
		{
			//default server time zone
			$timeZone = COption::GetOptionString("main", "default_time_zone", "");
		}

		return static::calculateOffset($timeZone);
	}

	/**
	 * Calculates the difference between local server time and specified timezone in the present moment of time in seconds.
	 * @param string $timeZone
	 * @return int
	 */
	public static function calculateOffset(string $timeZone): int
	{
		if ($timeZone != '')
		{
			try //possible DateTimeZone incorrect timezone
			{
				$localOffset = (new DateTime())->getOffset();

				$userTime = new DateTime('now', new DateTimeZone($timeZone));
				$userOffset = $userTime->getOffset();

				return $userOffset - $localOffset;
			}
			catch (Throwable)
			{
			}
		}

		return 0;
	}
}
