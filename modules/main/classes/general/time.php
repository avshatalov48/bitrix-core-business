<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

use Bitrix\Main;

class CTimeZone
{
	protected static $enabled = 1;
	protected static $useTimeZones = false;

	public static function Possible()
	{
		//since PHP 5.2
		return true;
	}

	public static function Enabled()
	{
		return (self::$enabled > 0 && self::OptionEnabled());
	}

	public static function OptionEnabled()
	{
		if(self::$useTimeZones === false)
		{
			self::$useTimeZones = COption::GetOptionString("main", "use_time_zones", "N");
		}
		return (self::$useTimeZones == "Y");
	}

	public static function Disable()
	{
		self::$enabled --;
	}

	public static function Enable()
	{
		self::$enabled ++;
	}

	private static function __tzsort($a, $b)
	{
		if($a['offset'] == $b['offset'])
			return strcmp($a['timezone_id'], $b['timezone_id']);
		return ($a['offset'] < $b['offset']? -1 : 1);
	}

	public static function GetZones()
	{
		IncludeModuleLangFile(__FILE__);

		$aTZ = array();
		static $aExcept = array("Etc/", "GMT", "UTC", "UCT", "HST", "PST", "MST", "CST", "EST", "CET", "MET", "WET", "EET", "PRC", "ROC", "ROK", "W-SU");
		foreach(DateTimeZone::listIdentifiers() as $tz)
		{
			foreach($aExcept as $ex)
				if(strpos($tz, $ex) === 0)
					continue 2;
			try
			{
				$oTz = new DateTimeZone($tz);
				$aTZ[$tz] = array('timezone_id'=>$tz, 'offset'=>$oTz->getOffset(new DateTime("now", $oTz)));
			}
			catch(Exception $e){}
		}

		uasort($aTZ, array('CTimeZone', '__tzsort'));

		$aZones = array(""=>GetMessage("tz_local_time"));
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
		/** @global CMain $APPLICATION */
		global $APPLICATION, $USER;

		$cookiePrefix = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
		$autoTimeZone = $USER->GetParam("AUTO_TIME_ZONE") ?: '';
		if (self::IsAutoTimeZone(trim($autoTimeZone)))
		{
			$cookieDate = (new \Bitrix\Main\Type\DateTime())->add("12M");
			$cookieDate->setDate((int)$cookieDate->format('Y'), (int)$cookieDate->format('m'), 1);
			$cookieDate->setTime(0,	0);

			$APPLICATION->AddHeadString(
				'<script type="text/javascript">if (Intl && Intl.DateTimeFormat) document.cookie="'.$cookiePrefix.'_TZ="+Intl.DateTimeFormat().resolvedOptions().timeZone+"; path=/; expires='.$cookieDate->format("r").'";</script>', true
			);
		}
		elseif (isset($_COOKIE[$cookiePrefix."_TZ"]))
		{
			setcookie($cookiePrefix."_TZ", "", time()-3600, "/");
		}

		if (isset($_COOKIE[$cookiePrefix."_TIME_ZONE"]))
		{
			// delete deprecated cookie
			setcookie($cookiePrefix."_TIME_ZONE", "", time()-3600, "/");
		}
	}

	public static function getTzCookie()
	{
		$context = Main\Context::getCurrent();
		if ($context)
		{
			return $context->getRequest()->getCookie('TZ');
		}
		return null;
	}

	public static function IsAutoTimeZone($autoTimeZone)
	{
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
	 * @deprecated
	 * @return int|null
	 */
	public static function GetCookieValue()
	{
		static $cookie_prefix = null;
		if($cookie_prefix === null)
		{
			$cookie_prefix = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
		}

		if(isset($_COOKIE[$cookie_prefix."_TIME_ZONE"])	&& $_COOKIE[$cookie_prefix."_TIME_ZONE"] <> '')
		{
			return intval($_COOKIE[$cookie_prefix."_TIME_ZONE"]);
		}

		return null;
	}

	/**
	 * @deprecated
	 * Emulates timezone got from JS cookie setter like in SetAutoCookie.
	 *
	 * @param int $timezoneOffset Time zone offset
	 */
	public static function SetCookieValue($timezoneOffset)
	{
		static $cookie_prefix = null;
		if($cookie_prefix === null)
		{
			$cookie_prefix = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
		}

		$_COOKIE[$cookie_prefix."_TIME_ZONE"] = $timezoneOffset;
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

		try //possible DateTimeZone incorrect timezone
		{
			$timeZone = '';

			if ($USER_ID !== null)
			{
				$dbUser = CUser::GetList('id', 'asc', ['ID_EQUAL_EXACT' => $USER_ID], ['FIELDS' => ['AUTO_TIME_ZONE', 'TIME_ZONE', 'TIME_ZONE_OFFSET']]);
				if (($arUser = $dbUser->Fetch()))
				{
					if (self::IsAutoTimeZone(trim($arUser["AUTO_TIME_ZONE"])))
					{
						// can't detect auto timezone for a non-current user, return actual offset from the DB
						return intval($arUser["TIME_ZONE_OFFSET"]);
					}
					$timeZone = $arUser["TIME_ZONE"];
				}
			}
			elseif (is_object($USER))
			{
				// current user
				$autoTimeZone = $USER->GetParam("AUTO_TIME_ZONE") ?: '';
				if (self::IsAutoTimeZone(trim($autoTimeZone)))
				{
					if (($cookie = static::getTzCookie()) !== null)
					{
						// auto time zone from the cookie
						$timeZone = $cookie;
					}
					elseif (($cookie = static::GetCookieValue()) !== null)
					{
						//auto time offset from old cookie - deprecated
						$localOffset = (new DateTime())->getOffset();
						$userOffset = -($cookie) * 60;

						return $userOffset - $localOffset;
					}
				}
				else
				{
					// user set time zone manually
					$timeZone = $USER->GetParam("TIME_ZONE");
				}
			}

			if ($timeZone == '')
			{
				//default server time zone
				$timeZone = COption::GetOptionString("main", "default_time_zone", "");
			}

			if ($timeZone != '')
			{
				$localOffset = (new DateTime())->getOffset();

				$userTime = new DateTime('now', new DateTimeZone($timeZone));
				$userOffset = $userTime->getOffset();

				return $userOffset - $localOffset;
			}
		}
		catch (Throwable $e)
		{
		}

		return 0;
	}
}
