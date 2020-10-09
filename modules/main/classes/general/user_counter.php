<?
class CAllUserCounter
{
	const ALL_SITES = '**';
	const LIVEFEED_CODE = '**';
	const SYSTEM_USER_ID = 0;

	protected static $counters = false;

	public static function GetValue($user_id, $code, $site_id = SITE_ID)
	{
		$user_id = intval($user_id);

		if ($user_id < 0)
			return false;

		$arCodes = self::GetValues($user_id, $site_id);
		if (isset($arCodes[$code]))
			return intval($arCodes[$code]);
		else
			return 0;
	}

	public static function GetValues($user_id, $site_id = SITE_ID, &$arLastDate = array())
	{
		global $DB, $CACHE_MANAGER;
		static $diff;

		$user_id = intval($user_id);
		if ($user_id < 0)
		{
			return array();
		}

		if (!is_array($arLastDate))
		{
			$arLastDate = array();
		}

		if ($diff === false)
		{
			$diff = CTimeZone::GetOffset();
		}

		if(!isset(self::$counters[$user_id][$site_id]))
		{
			if (
				CACHED_b_user_counter !== false
				&& $CACHE_MANAGER->Read(CACHED_b_user_counter, "user_counter".$user_id, "user_counter")
			)
			{
				$arAll = $CACHE_MANAGER->Get("user_counter".$user_id);
			}
			else
			{
				$strSQL = "
					SELECT CODE, SITE_ID, CNT, ".$DB->DatetimeToTimestampFunction("LAST_DATE")." LAST_DATE_TS
					FROM b_user_counter
					WHERE USER_ID = ".$user_id;

				$dbRes = $DB->Query($strSQL, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$arAll = array();

				while ($arRes = $dbRes->Fetch())
				{
					$arRes["CODE"] = self::getGroupedCode($arRes["CODE"]);
					$arAll[] = $arRes;
				}

				if (CACHED_b_user_counter !== false)
				{
					$CACHE_MANAGER->Set("user_counter".$user_id, $arAll);
				}
			}

			if(is_array($arAll))
			{
				foreach($arAll as $arItem)
				{
					if (
						$arItem["SITE_ID"] == $site_id
						|| $arItem["SITE_ID"] == self::ALL_SITES
					)
					{
						if (!isset(self::$counters[$user_id][$site_id][$arItem["CODE"]]))
						{
							self::$counters[$user_id][$site_id][$arItem["CODE"]] = 0;
						}
						self::$counters[$user_id][$site_id][$arItem["CODE"]] += $arItem["CNT"];

						if (!isset($arLastDate[$user_id]))
						{
							$arLastDate[$user_id] = array();
						}
						if (!isset($arLastDate[$user_id][$site_id]))
						{
							$arLastDate[$user_id][$site_id] = array();
						}

						if (isset($arItem["LAST_DATE_TS"]))
						{
							$arLastDate[$user_id][$site_id][$arItem["CODE"]] = $arItem["LAST_DATE_TS"] - $diff;
						}
					}
				}
			}
		}

		return self::$counters[$user_id][$site_id];
	}

	public static function GetAllValues($user_id)
	{
		global $DB, $CACHE_MANAGER;

		$arCounters = Array();
		$user_id = intval($user_id);
		if ($user_id < 0)
			return $arCounters;

		$arSites = Array();
		$res = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
		while ($row = $res->Fetch())
			$arSites[] = $row['ID'];

		if (CACHED_b_user_counter !== false && $CACHE_MANAGER->Read(CACHED_b_user_counter, "user_counter".$user_id, "user_counter"))
		{
			$arAll = $CACHE_MANAGER->Get("user_counter".$user_id);
		}
		else
		{
			$strSQL = "
				SELECT CODE, SITE_ID, CNT
				FROM b_user_counter
				WHERE USER_ID = ".$user_id;

			$dbRes = $DB->Query($strSQL, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$arAll = array();
			while ($arRes = $dbRes->Fetch())
			{
				$arRes["CODE"] = self::getGroupedCode($arRes["CODE"]);
				$arAll[] = $arRes;
			}

			if (CACHED_b_user_counter !== false)
			{
				$CACHE_MANAGER->Set("user_counter".$user_id, $arAll);
			}
		}

		foreach($arAll as $arItem)
		{
			if ($arItem['SITE_ID'] == self::ALL_SITES)
			{
				foreach ($arSites as $siteId)
				{
					if (isset($arCounters[$siteId][$arItem['CODE']]))
						$arCounters[$siteId][$arItem['CODE']] += intval($arItem['CNT']);
					else
						$arCounters[$siteId][$arItem['CODE']] = intval($arItem['CNT']);
				}
			}
			else
			{
				if (isset($arCounters[$arItem['SITE_ID']][$arItem['CODE']]))
					$arCounters[$arItem['SITE_ID']][$arItem['CODE']] += intval($arItem['CNT']);
				else
					$arCounters[$arItem['SITE_ID']][$arItem['CODE']] = intval($arItem['CNT']);
			}
		}

		return $arCounters;
	}

	public static function GetLastDate($user_id, $code, $site_id = SITE_ID)
	{
		global $DB;

		$user_id = intval($user_id);
		if ($user_id < 0 || $code == '')
			return 0;

		$strSQL = "
			SELECT ".$DB->DateToCharFunction("LAST_DATE", "FULL")." LAST_DATE
			FROM b_user_counter
			WHERE USER_ID = ".$user_id."
			AND (SITE_ID = '".$site_id."' OR SITE_ID = '".self::ALL_SITES."')
			AND CODE = '".$DB->ForSql($code)."'
		";

		$result = 0;
		$dbRes = $DB->Query($strSQL, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
			$result = MakeTimeStamp($arRes["LAST_DATE"]);

		return $result;
	}

	public static function ClearAll($user_id, $site_id = SITE_ID, $sendPull = true)
	{
		global $DB, $CACHE_MANAGER;

		$user_id = intval($user_id);
		if ($user_id < 0)
			return false;

		$strSQL = "
			UPDATE b_user_counter SET
			CNT = 0
			WHERE USER_ID = ".$user_id."
			AND (SITE_ID = '".$site_id."' OR SITE_ID = '".self::ALL_SITES."')";
		$DB->Query($strSQL, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($site_id == self::ALL_SITES)
		{
			if (self::$counters)
				unset(self::$counters[$user_id]);
		}
		else
		{
			if (self::$counters)
				unset(self::$counters[$user_id][$site_id]);
		}

		$CACHE_MANAGER->Clean("user_counter".$user_id, "user_counter");

		if ($sendPull)
			self::SendPullEvent($user_id);

		return true;
	}

	public static function ClearByTag($tag, $code, $site_id = SITE_ID, $sendPull = true)
	{
		global $DB, $CACHE_MANAGER;

		if ($tag == '' || $code == '')
			return false;

		$strSQL = "
			UPDATE b_user_counter SET
			CNT = 0
			WHERE TAG = '".$DB->ForSQL($tag)."' AND CODE = '".$DB->ForSQL($code)."'
			AND (SITE_ID = '".$site_id."' OR SITE_ID = '".self::ALL_SITES."')";
		$DB->Query($strSQL, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		self::$counters = false;
		$CACHE_MANAGER->CleanDir("user_counter");

		if ($sendPull && self::CheckLiveMode())
		{
			global $DB;

			$arSites = Array();
			$res = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
			while ($row = $res->Fetch())
				$arSites[] = $row['ID'];

			$helper = \Bitrix\Main\Application::getConnection()->getSqlHelper();

			$strSQL = "
				SELECT pc.CHANNEL_ID, pc.CHANNEL_TYPE, uc.USER_ID, uc.SITE_ID, uc.CODE, uc.CNT
				FROM b_user_counter uc
				INNER JOIN b_pull_channel pc ON pc.USER_ID = uc.USER_ID
				INNER JOIN b_user u ON u.ID = uc.USER_ID AND (CASE WHEN u.EXTERNAL_AUTH_ID IN ('".join("', '", \Bitrix\Main\UserTable::getExternalUserTypes())."') THEN 'Y' ELSE 'N' END) = 'N' AND u.LAST_ACTIVITY_DATE > ".$helper->addSecondsToDateTime('(-3600)')."
				WHERE TAG = '".$DB->ForSQL($tag)."' AND CODE = '".$DB->ForSQL($code)."'
				AND (SITE_ID = '".$site_id."' OR SITE_ID = '".self::ALL_SITES."')";

			$res = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

			$pullMessage = Array();
			while ($row = $res->Fetch())
			{
				if (!($row['CHANNEL_TYPE'] == 'private' || $row['CHANNEL_TYPE'] == 'shared' && $row['USER_ID'] == 0))
				{
					continue;
				}
				if ($row['SITE_ID'] == self::ALL_SITES)
				{
					foreach ($arSites as $siteId)
					{
						if (isset($pullMessage[$row['CHANNEL_ID']][$siteId][$row['CODE']]))
							$pullMessage[$row['CHANNEL_ID']][$siteId][$row['CODE']] += intval($row['CNT']);
						else
							$pullMessage[$row['CHANNEL_ID']][$siteId][$row['CODE']] = intval($row['CNT']);
					}
				}
				else
				{
					if (isset($pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$row['CODE']]))
						$pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$row['CODE']] += intval($row['CNT']);
					else
						$pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$row['CODE']] = intval($row['CNT']);
				}
			}

			foreach ($pullMessage as $channelId => $arMessage)
			{
				\Bitrix\Pull\Event::add($channelId, Array(
					'module_id' => 'main',
					'command'   => 'user_counter',
					'expiry' 	=> 3600,
					'params'    => $arMessage,
				));
			}
		}

		return true;
	}

	public static function CheckLiveMode()
	{
		return (
			CModule::IncludeModule('pull')
			&& CPullOptions::GetNginxStatus()
		);
	}

	protected static function SendPullEvent($user_id, $code = "", $bMultiple = false)
	{
		$user_id = intval($user_id);
		if ($user_id < 0)
			return false;

		if (self::CheckLiveMode())
		{
			global $DB;

			$arSites = Array();
			$res = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
			while ($row = $res->Fetch())
				$arSites[] = $row['ID'];

			$helper = \Bitrix\Main\Application::getConnection()->getSqlHelper();
			$strSQL = "
				SELECT pc.CHANNEL_ID, pc.CHANNEL_TYPE, uc.USER_ID, uc.SITE_ID, uc.CODE, uc.CNT
				FROM b_user_counter uc
				INNER JOIN b_pull_channel pc ON pc.USER_ID = uc.USER_ID
				INNER JOIN b_user u ON u.ID = uc.USER_ID AND (CASE WHEN u.EXTERNAL_AUTH_ID IN ('".join("', '", \Bitrix\Main\UserTable::getExternalUserTypes())."') THEN 'Y' ELSE 'N' END) = 'N' AND u.LAST_ACTIVITY_DATE > ".$helper->addSecondsToDateTime('(-3600)')."
				WHERE uc.USER_ID = ".intval($user_id).(
					$code <> ''
					? (
						$bMultiple
						? " AND uc.CODE LIKE '".$DB->ForSQL($code)."%'"
						: " AND uc.CODE = '".$DB->ForSQL($code)."'"
					)
					: ""
				)."
			";
			$res = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

			$pullMessage = Array();
			while ($row = $res->Fetch())
			{
				$key = ($code <> '' ? $code : $row['CODE']);

				if (!($row['CHANNEL_TYPE'] == 'private' || $row['CHANNEL_TYPE'] == 'shared' && $row['USER_ID'] == 0))
				{
					continue;
				}
				if ($row['SITE_ID'] == self::ALL_SITES)
				{
					foreach ($arSites as $siteId)
					{
						if (isset($pullMessage[$row['CHANNEL_ID']][$siteId][$key]))
							$pullMessage[$row['CHANNEL_ID']][$siteId][$key] += intval($row['CNT']);
						else
							$pullMessage[$row['CHANNEL_ID']][$siteId][$key] = intval($row['CNT']);
					}
				}
				else
				{
					if (isset($pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$key]))
						$pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$key] += intval($row['CNT']);
					else
						$pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$key] = intval($row['CNT']);
				}
			}

			foreach ($pullMessage as $channelId => $arMessage)
			{
				\Bitrix\Pull\Event::add($channelId, Array(
					'module_id' => 'main',
					'command'   => 'user_counter',
					'expiry' 	=> 3600,
					'params'    => $arMessage,
				));
			}
		}
	}

	public static function addValueToPullMessage($row, $arSites = array(), &$pullMessage = array())
	{
		$code = self::getGroupedCode($row["CODE"]);

		if ($row['SITE_ID'] == self::ALL_SITES)
		{
			foreach($arSites as $siteId)
			{
				if (isset($pullMessage[$row['CHANNEL_ID']][$siteId][$code]))
				{
					$pullMessage[$row['CHANNEL_ID']][$siteId][$code] += intval($row['CNT']);
				}
				else
				{
					$pullMessage[$row['CHANNEL_ID']][$siteId][$code] = intval($row['CNT']);
				}
			}
		}
		else
		{
			if (isset($pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$code]))
			{
				$pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$code] += intval($row['CNT']);
			}
			else
			{
				$pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$code] = intval($row['CNT']);
			}
		}
	}

	public static function getGroupedCounters($counters)
	{
		$result = array();

		foreach ($counters as $siteId => $data)
		{
			$result[$siteId] = self::getGroupedCounterRecords($data);
		}

		return $result;
	}

	public static function getGroupedCounterRecords($records)
	{
		$result = array();

		foreach ($records as $code => $value)
		{
			$code = self::getGroupedCode($code);

			if (isset($result[$code]))
			{
				$result[$code] += $value;
			}
			else
			{
				$result[$code] = $value;
			}
		}

		return $result;
	}

	private static function getGroupedCode($code)
	{
		$result = $code;

		if (mb_strpos($code, self::LIVEFEED_CODE) === 0)
		{
			$result = self::LIVEFEED_CODE;
		}

		return $result;
	}

	public static function OnSocNetLogCommentDelete($commentId)
	{
		CUserCounter::DeleteByCode(self::LIVEFEED_CODE."LC".intval($commentId));
	}

	public static function OnSocNetLogDelete($logId)
	{
		CUserCounter::DeleteByCode(self::LIVEFEED_CODE."L".intval($logId));
	}

	// legacy function
	public static function GetValueByUserID($user_id, $site_id = SITE_ID, $code = self::ALL_SITES)
	{
		return self::GetValue($user_id, $code, $site_id);
	}
	public static function GetCodeValuesByUserID($user_id, $site_id = SITE_ID)
	{
		return self::GetValues($user_id, $site_id);
	}
	public static function GetLastDateByUserAndCode($user_id, $site_id = SITE_ID, $code = self::ALL_SITES)
	{
		return self::GetLastDate($user_id, $code, $site_id);
	}

	public static function DeleteOld()
	{
		global $DB;

		$days = intval(\Bitrix\Main\Config\Option::get('main', 'user_counter_days', 20));

		$time = $DB->CharToDateFunction(GetTime(time()- $days*60*60*24, "FULL"));
		$DB->Query("DELETE FROM b_user_counter WHERE TIMESTAMP_X <= ".$time);

		return "CUserCounter::DeleteOld();";
	}
}

class CAllUserCounterPage
{
	protected static function setUserIdOption($value = false)
	{
//		\Bitrix\Main\Config\Option::set('main', 'user_counter_pull_page_start', $value);
	}

	protected static function getUserIdOption()
	{
//		return \Bitrix\Main\Config\Option::get('main', 'user_counter_pull_page_start', false);
	}

	public static function getPageSizeOption($defaultValue = 100)
	{
		$value = intval(\Bitrix\Main\Config\Option::get('main', 'user_counter_pull_page_size', $defaultValue));
		if ($value <= 0)
		{
			$value = $defaultValue;
		}

		return $value;
	}

	public static function setNewEvent()
	{
		self::setUserIdOption(0);
	}

	protected static function getMinMax($prevMax = 0)
	{
		global $DB;

		$pageSize = self::getPageSizeOption(100);

		$strSQL = "
				SELECT USER_ID
				FROM b_user_counter uc
				WHERE SENT = '0' AND USER_ID > ".intval($prevMax)."
				GROUP BY USER_ID
				ORDER BY USER_ID ASC
				LIMIT ".$pageSize."
			";

		$res = $DB->query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$i = 0;
		while($row = $res->fetch())
		{
			if (!$i)
			{
				$minValue = $row["USER_ID"];
			}
			else
			{
				$maxValue = $row["USER_ID"];
			}
			$i++;
		}

		if ($i)
		{
			return array(
				"MIN" => intval($minValue),
				"MAX" => intval($maxValue)
			);
		}
		else
		{
			return false;
		}
	}
}
?>