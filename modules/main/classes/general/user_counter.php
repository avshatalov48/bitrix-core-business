<?php

use Bitrix\Main\Config\Option;

class CAllUserCounter
{
	public const ALL_SITES = '**';
	public const LIVEFEED_CODE = '**';
	public const SYSTEM_USER_ID = 0;

	protected static $counters = [];
	private static $isLiveFeedJobOn = false;

	public static function GetValue($user_id, $code, $site_id = SITE_ID)
	{
		$user_id = (int)$user_id;

		if ($user_id < 0)
		{
			return false;
		}

		$arCodes = self::GetValues($user_id, $site_id);
		if (isset($arCodes[$code]))
		{
			return (int)$arCodes[$code];
		}

		return 0;
	}

	public static function GetValues($user_id, $site_id = SITE_ID, &$arLastDate = [])
	{
		static $diff;

		$user_id = (int)$user_id;
		if ($user_id < 0)
		{
			return [];
		}

		if (!is_array($arLastDate))
		{
			$arLastDate = [];
		}

		if ($diff === false)
		{
			$diff = CTimeZone::GetOffset();
		}

		if (!isset(self::$counters[$user_id][$site_id]))
		{
			$arAll = self::getValuesFromDB($user_id);

			if (is_array($arAll))
			{
				foreach($arAll as $arItem)
				{
					if (
						$arItem["SITE_ID"] == $site_id
						|| $arItem["SITE_ID"] === self::ALL_SITES
					)
					{
						if (!isset(self::$counters[$user_id][$site_id][$arItem["CODE"]]))
						{
							self::$counters[$user_id][$site_id][$arItem["CODE"]] = 0;
						}
						self::$counters[$user_id][$site_id][$arItem["CODE"]] += $arItem["CNT"];

						if (!isset($arLastDate[$user_id]))
						{
							$arLastDate[$user_id] = [];
						}
						if (!isset($arLastDate[$user_id][$site_id]))
						{
							$arLastDate[$user_id][$site_id] = [];
						}

						if (isset($arItem["LAST_DATE_TS"]))
						{
							$arLastDate[$user_id][$site_id][$arItem["CODE"]] = $arItem["LAST_DATE_TS"] - $diff;
						}
					}
				}
			}
		}

		return (self::$counters[$user_id][$site_id] ?? []);
	}

	public static function GetAllValues($user_id)
	{
		$arCounters = [];
		$user_id = (int)$user_id;
		if ($user_id < 0)
		{
			return $arCounters;
		}

		$arSites = Array();
		$by = '';
		$order = '';
		$res = CSite::GetList($by, $order, Array("ACTIVE" => "Y"));
		while ($row = $res->Fetch())
		{
			$arSites[] = $row['ID'];
		}

		$arAll = self::getValuesFromDB($user_id);

		foreach ($arAll as $arItem)
		{
			if ($arItem['SITE_ID'] === self::ALL_SITES)
			{
				foreach ($arSites as $siteId)
				{
					if (isset($arCounters[$siteId][$arItem['CODE']]))
					{
						$arCounters[$siteId][$arItem['CODE']] += (int)$arItem['CNT'];
					}
					else
					{
						$arCounters[$siteId][$arItem['CODE']] = (int)$arItem['CNT'];
					}
				}
			}
			elseif (isset($arCounters[$arItem['SITE_ID']][$arItem['CODE']]))
			{
				$arCounters[$arItem['SITE_ID']][$arItem['CODE']] += (int)$arItem['CNT'];
			}
			else
			{
				$arCounters[$arItem['SITE_ID']][$arItem['CODE']] = (int)$arItem['CNT'];
			}
		}

		return $arCounters;
	}

	private static function getValuesFromDB(int $userId = 0)
	{
		global $CACHE_MANAGER, $DB;

		if (
			CACHED_b_user_counter !== false
			&& $CACHE_MANAGER->read(CACHED_b_user_counter, 'user_counter' . $userId, 'user_counter')
		)
		{
			$result = $CACHE_MANAGER->get('user_counter' . $userId);
		}
		else
		{
			$strSQL = '
				SELECT CODE, SITE_ID, CNT, ' . $DB->datetimeToTimestampFunction('LAST_DATE') . ' LAST_DATE_TS
				FROM b_user_counter
				WHERE USER_ID = ' . $userId;

			$res = $DB->query($strSQL);
			$result = [];
			while ($rowFields = $res->fetch())
			{
				$rowFields['CODE'] = self::getGroupedCode($rowFields['CODE']);
				$result[] = $rowFields;
			}

			$result = self::getValuesForCache($result);

			if (CACHED_b_user_counter !== false)
			{
				$CACHE_MANAGER->set('user_counter' . $userId, $result);
			}
		}

		return $result;
	}

	private static function getValuesForCache(array $counters = []): array
	{
		$cachedCounters = [];

		foreach ($counters as $counter)
		{
			$key = $counter['CODE'] . '_' . $counter['SITE_ID'];

			if (!isset($cachedCounters[$key]))
			{
				$cachedCounters[$key] = $counter;
				$cachedCounters[$key]['CNT'] = (int)$counter['CNT'];
			}
			else
			{
				$cachedCounters[$key]['CNT'] += (int)$counter['CNT'];
				if (isset($counter['LAST_DATE_TS']))
				{
					$cachedCounters[$key]['LAST_DATE_TS'] = $counter['LAST_DATE_TS'];
				}
			}
		}

		return array_values($cachedCounters);
	}

	public static function GetLastDate($user_id, $code, $site_id = SITE_ID)
	{
		global $DB;

		$user_id = (int)$user_id;
		if ($user_id < 0 || $code == '')
		{
			return 0;
		}

		$strSQL = "
			SELECT " . $DB->DateToCharFunction("LAST_DATE") . " LAST_DATE
			FROM b_user_counter
			WHERE USER_ID = ".$user_id."
			AND (SITE_ID = '".$site_id."' OR SITE_ID = '" . self::ALL_SITES . "')
			AND CODE = '" . $DB->ForSql($code) . "'
		";

		$result = 0;
		$dbRes = $DB->Query($strSQL);
		if ($arRes = $dbRes->Fetch())
		{
			$result = MakeTimeStamp($arRes["LAST_DATE"]);
		}

		return $result;
	}

	public static function ClearAll($user_id, $site_id = SITE_ID, $sendPull = true)
	{
		global $DB, $CACHE_MANAGER;

		$user_id = (int)$user_id;
		if ($user_id < 0)
		{
			return false;
		}

		$strSQL = "
			UPDATE b_user_counter SET
			CNT = 0
			WHERE USER_ID = ".$user_id."
			AND (SITE_ID = '".$site_id."' OR SITE_ID = '".self::ALL_SITES."')";
		$DB->Query($strSQL);

		if ($site_id === self::ALL_SITES)
		{
			if (self::$counters)
			{
				unset(self::$counters[$user_id]);
			}
		}
		elseif (self::$counters)
		{
			unset(self::$counters[$user_id][$site_id]);
		}

		$CACHE_MANAGER->Clean("user_counter" . $user_id, "user_counter");

		if ($sendPull)
		{
			self::SendPullEvent($user_id);
		}

		return true;
	}

	public static function ClearByTag($tag, $code, $site_id = SITE_ID, $sendPull = true)
	{
		global $DB, $CACHE_MANAGER;

		if ($tag == '' || $code == '')
		{
			return false;
		}

		$strSQL = "
			UPDATE b_user_counter SET
			CNT = 0
			WHERE TAG = '" . $DB->ForSQL($tag) . "' AND CODE = '" . $DB->ForSQL($code) . "'
			AND (SITE_ID = '" . $site_id . "' OR SITE_ID = '" . self::ALL_SITES . "')";
		$DB->Query($strSQL);

		self::$counters = [];
		$CACHE_MANAGER->CleanDir("user_counter");

		if ($sendPull && self::CheckLiveMode())
		{
			global $DB;

			$arSites = Array();
			$by = '';
			$order = '';
			$res = CSite::GetList($by, $order, Array("ACTIVE" => "Y"));
			while ($row = $res->Fetch())
			{
				$arSites[] = $row['ID'];
			}

			$helper = \Bitrix\Main\Application::getConnection()->getSqlHelper();

			$strSQL = "
				SELECT uc.USER_ID as CHANNEL_ID, 'private' as CHANNEL_TYPE, uc.USER_ID, uc.SITE_ID, uc.CODE, uc.CNT
				FROM b_user_counter uc
				INNER JOIN b_user u ON u.ID = uc.USER_ID AND (CASE WHEN u.EXTERNAL_AUTH_ID IN ('" . implode("', '", \Bitrix\Main\UserTable::getExternalUserTypes())."') THEN 'Y' ELSE 'N' END) = 'N' AND u.LAST_ACTIVITY_DATE > " . $helper->addSecondsToDateTime('(-3600)')."
				WHERE TAG = '".$DB->ForSQL($tag)."' AND CODE = '".$DB->ForSQL($code)."'
				AND (SITE_ID = '".$site_id."' OR SITE_ID = '" . self::ALL_SITES . "')";

			$res = $DB->Query($strSQL);

			$pullMessage = [];
			while ($row = $res->Fetch())
			{
				if (!(
					$row['CHANNEL_TYPE'] === 'private'
					|| (
						$row['CHANNEL_TYPE'] === 'shared'
						&& (int)$row['USER_ID'] === 0
					)
				))
				{
					continue;
				}

				if ($row['SITE_ID'] === self::ALL_SITES)
				{
					foreach ($arSites as $siteId)
					{
						if (isset($pullMessage[$row['CHANNEL_ID']][$siteId][$row['CODE']]))
						{
							$pullMessage[$row['CHANNEL_ID']][$siteId][$row['CODE']] += (int)$row['CNT'];
						}
						else
						{
							$pullMessage[$row['CHANNEL_ID']][$siteId][$row['CODE']] = (int)$row['CNT'];
						}
					}
				}
				elseif (isset($pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$row['CODE']]))
				{
					$pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$row['CODE']] += (int)$row['CNT'];
				}
				else
				{
					$pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$row['CODE']] = (int)$row['CNT'];
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
		$user_id = (int)$user_id;
		if ($user_id < 0)
		{
			return false;
		}

		if (self::CheckLiveMode())
		{
			global $DB;

			$arSites = Array();
			$by = '';
			$order = '';
			$res = CSite::GetList($by, $order, Array("ACTIVE" => "Y"));
			while ($row = $res->Fetch())
			{
				$arSites[] = $row['ID'];
			}

			$helper = \Bitrix\Main\Application::getConnection()->getSqlHelper();
			$strSQL = "
				SELECT uc.USER_ID as CHANNEL_ID, 'private' as CHANNEL_TYPE, uc.USER_ID, uc.SITE_ID, uc.CODE, uc.CNT
				FROM b_user_counter uc
				INNER JOIN b_user u ON u.ID = uc.USER_ID AND (CASE WHEN u.EXTERNAL_AUTH_ID IN ('" . implode("', '", \Bitrix\Main\UserTable::getExternalUserTypes())."') THEN 'Y' ELSE 'N' END) = 'N' AND u.LAST_ACTIVITY_DATE > ".$helper->addSecondsToDateTime('(-3600)')."
				WHERE uc.USER_ID = " . (int)$user_id
				.(
				$code <> ''
					? (
				$bMultiple
					? " AND uc.CODE LIKE '".$DB->ForSQL($code)."%'"
					: " AND uc.CODE = '".$DB->ForSQL($code)."'"
				)
					: ""
				)."
			";
			$res = $DB->Query($strSQL);

			$pullMessage = Array();
			while ($row = $res->Fetch())
			{
				$key = ($code <> '' ? $code : $row['CODE']);

				if (!(
					$row['CHANNEL_TYPE'] === 'private'
					|| (
						$row['CHANNEL_TYPE'] === 'shared'
						&& (int)$row['USER_ID'] === 0
					)
				))
				{
					continue;
				}
				if ($row['SITE_ID'] === self::ALL_SITES)
				{
					foreach ($arSites as $siteId)
					{
						if (isset($pullMessage[$row['CHANNEL_ID']][$siteId][$key]))
						{
							$pullMessage[$row['CHANNEL_ID']][$siteId][$key] += (int)$row['CNT'];
						}
						else
						{
							$pullMessage[$row['CHANNEL_ID']][$siteId][$key] = (int)$row['CNT'];
						}
					}
				}
				elseif (isset($pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$key]))
				{
					$pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$key] += (int)$row['CNT'];
				}
				else
				{
					$pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$key] = (int)$row['CNT'];
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

	public static function addValueToPullMessage($row, $arSites = array(), &$pullMessage = [])
	{
		$code = self::getGroupedCode($row["CODE"]);

		if ($row['SITE_ID'] === self::ALL_SITES)
		{
			foreach($arSites as $siteId)
			{
				if (isset($pullMessage[$row['CHANNEL_ID']][$siteId][$code]))
				{
					$pullMessage[$row['CHANNEL_ID']][$siteId][$code] += (int)$row['CNT'];
				}
				else
				{
					$pullMessage[$row['CHANNEL_ID']][$siteId][$code] = (int)$row['CNT'];
				}
			}
		}
		elseif (isset($pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$code]))
		{
			$pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$code] += (int)$row['CNT'];
		}
		else
		{
			$pullMessage[$row['CHANNEL_ID']][$row['SITE_ID']][$code] = (int)$row['CNT'];
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
		CUserCounter::DeleteByCode(self::LIVEFEED_CODE . 'LC' . (int)$commentId);
	}

	public static function OnSocNetLogDelete($logId)
	{
		CUserCounter::DeleteByCode(self::LIVEFEED_CODE . 'L' . (int)$logId);
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

		$days = (int)Option::get('main', 'user_counter_days', 20);

		$time = $DB->CharToDateFunction(GetTime(time()- $days*60*60*24, "FULL"));
		$DB->Query("DELETE FROM b_user_counter WHERE TIMESTAMP_X <= ".$time);

		return "CUserCounter::DeleteOld();";
	}

	public static function Set($user_id, $code, $value, $site_id = SITE_ID, $tag = '', $sendPull = true)
	{
		global $DB, $CACHE_MANAGER;

		$value = (int)$value;
		$user_id = (int)$user_id;
		if ($user_id < 0 || $code == '')
		{
			return false;
		}

		$rs = $DB->Query("
			SELECT CNT FROM b_user_counter
			WHERE USER_ID = ".$user_id."
			AND SITE_ID = '".$DB->ForSQL($site_id)."'
			AND CODE = '".$DB->ForSQL($code)."'
		");

		if ($cntVal = $rs->Fetch())
		{
			$ssql = "";
			if ($tag != "")
			{
				$ssql = ", TAG = '".$DB->ForSQL($tag)."'";
			}

			if($cntVal['CNT'] != $value)
			{
				$DB->Query("
					UPDATE b_user_counter SET
					CNT = " . $value . " " . $ssql . ",
					SENT = 0
					WHERE USER_ID = " . $user_id . "
					AND SITE_ID = '" . $DB->ForSQL($site_id) . "'
					AND CODE = '" . $DB->ForSQL($code) . "'
				");
			}
			else
			{
				$sendPull = false;
			}
		}
		else
		{
			$DB->Query("
				INSERT INTO b_user_counter
				(CNT, USER_ID, SITE_ID, CODE, TAG)
				VALUES
				(".$value.", ".$user_id.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."', '".$DB->ForSQL($tag)."')
			", true);
		}

		if (self::$counters && isset(self::$counters[$user_id]))
		{
			if ($site_id === self::ALL_SITES)
			{
				foreach(self::$counters[$user_id] as $key => $tmp)
				{
					self::$counters[$user_id][$key][$code] = $value;
				}
			}
			else
			{
				if (!isset(self::$counters[$user_id][$site_id]))
				{
					self::$counters[$user_id][$site_id] = [];
				}

				self::$counters[$user_id][$site_id][$code] = $value;
			}
		}

		$CACHE_MANAGER->Clean("user_counter" . $user_id, "user_counter");

		if ($sendPull)
		{
			self::SendPullEvent($user_id, $code);
		}

		return true;
	}

	public static function Increment($user_id, $code, $site_id = SITE_ID, $sendPull = true, $increment = 1)
	{
		global $CACHE_MANAGER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$user_id = (int)$user_id;
		if ($user_id < 0 || $code == '')
		{
			return false;
		}

		$increment = (int)$increment;

		$merge = $helper->prepareMerge('b_user_counter', ['USER_ID', 'SITE_ID', 'CODE'], [
			'USER_ID' => $user_id,
			'SITE_ID' => $site_id,
			'CODE' => $code,
			'CNT' => $increment,
		], [
			'CNT' => new \Bitrix\Main\DB\SqlExpression('b_user_counter.CNT + ' . $increment),
		]);
		if ($merge[0])
		{
			$connection->query($merge[0]);
		}

		if (isset(self::$counters[$user_id]) && is_array(self::$counters[$user_id]))
		{
			if ($site_id === self::ALL_SITES)
			{
				foreach(self::$counters[$user_id] as $key => $tmp)
				{
					if (isset(self::$counters[$user_id][$key][$code]))
					{
						self::$counters[$user_id][$key][$code] += $increment;
					}
					else
					{
						self::$counters[$user_id][$key][$code] = $increment;
					}
				}
			}
			else
			{
				if (!isset(self::$counters[$user_id][$site_id]))
				{
					self::$counters[$user_id][$site_id] = [];
				}

				if (isset(self::$counters[$user_id][$site_id][$code]))
				{
					self::$counters[$user_id][$site_id][$code] += $increment;
				}
				else
				{
					self::$counters[$user_id][$site_id][$code] = $increment;
				}
			}
		}
		$CACHE_MANAGER->Clean("user_counter".$user_id, "user_counter");

		if ($sendPull)
		{
			self::SendPullEvent($user_id, $code);
		}

		return true;
	}

	/**
	 * @deprecated
	 * @param $user_id
	 * @param $code
	 * @param string $site_id
	 * @param bool $sendPull
	 * @param int $decrement
	 * @return bool
	 */
	public static function Decrement($user_id, $code, $site_id = SITE_ID, $sendPull = true, $decrement = 1)
	{
		global $CACHE_MANAGER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$user_id = (int)$user_id;
		if ($user_id < 0 || $code == '')
		{
			return false;
		}

		$decrement = (int)$decrement;

		$merge = $helper->prepareMerge('b_user_counter', ['USER_ID', 'SITE_ID', 'CODE'], [
			'USER_ID' => $user_id,
			'SITE_ID' => $site_id,
			'CODE' => $code,
			'CNT' => -$decrement,
		], [
			'CNT' => new \Bitrix\Main\DB\SqlExpression('b_user_counter.CNT - ' . $decrement),
		]);
		if ($merge[0])
		{
			$connection->query($merge[0]);
		}

		if (self::$counters && self::$counters[$user_id])
		{
			if ($site_id === self::ALL_SITES)
			{
				foreach (self::$counters[$user_id] as $key => $tmp)
				{
					if (isset(self::$counters[$user_id][$key][$code]))
					{
						self::$counters[$user_id][$key][$code] -= $decrement;
					}
					else
					{
						self::$counters[$user_id][$key][$code] = -$decrement;
					}
				}
			}
			else
			{
				if (!isset(self::$counters[$user_id][$site_id]))
				{
					self::$counters[$user_id][$site_id] = [];
				}

				if (isset(self::$counters[$user_id][$site_id][$code]))
				{
					self::$counters[$user_id][$site_id][$code] -= $decrement;
				}
				else
				{
					self::$counters[$user_id][$site_id][$code] = -$decrement;
				}
			}
		}

		$CACHE_MANAGER->Clean("user_counter".$user_id, "user_counter");

		if ($sendPull)
		{
			self::SendPullEvent($user_id, $code);
		}

		return true;
	}

	public static function IncrementWithSelect($sub_select, $sendPull = true, $arParams = array())
	{
		global $CACHE_MANAGER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		if ($sub_select <> '')
		{
			$pullInclude = (
				$sendPull
				&& self::CheckLiveMode()
			);

			if (
				is_array($arParams)
				&& isset($arParams["TAG_SET"])
			)
			{
				$insertFields = ['USER_ID', 'CNT', 'SITE_ID', 'CODE', 'SENT', 'TAG'];
				if (is_array($arParams) && isset($arParams["SET_TIMESTAMP"]))
				{
					$insertFields[] = 'TIMESTAMP_X';
				}
				$strSQL = $helper->prepareMergeSelect(
					'b_user_counter',
					['USER_ID', 'SITE_ID', 'CODE'],
					$insertFields,
					'(' . $sub_select . ')',
					[
						'CNT' => new \Bitrix\Main\DB\SqlExpression('b_user_counter.CNT + ?v', 'CNT'),
						'SENT' => new \Bitrix\Main\DB\SqlExpression('?v', 'SENT'),
						'TAG' => $arParams["TAG_SET"],
					]
				);
			}
			elseif (
				is_array($arParams)
				&& isset($arParams["TAG_CHECK"])
			)
			{
				$insertFields = ['USER_ID', 'CNT', 'SITE_ID', 'CODE', 'SENT'];
				if (is_array($arParams) && isset($arParams["SET_TIMESTAMP"]))
				{
					$insertFields[] = 'TIMESTAMP_X';
				}
				$strSQL = $helper->prepareMergeSelect(
					'b_user_counter',
					['USER_ID', 'SITE_ID', 'CODE'],
					$insertFields,
					'(' . $sub_select . ')',
					[
						'CNT' => new \Bitrix\Main\DB\SqlExpression("CASE WHEN b_user_counter.TAG = '" . $helper->forSQL($arParams["TAG_CHECK"]) . "' THEN b_user_counter.CNT ELSE b_user_counter.CNT + ?v END", 'CNT'),
						'SENT' => new \Bitrix\Main\DB\SqlExpression("CASE WHEN b_user_counter.TAG = '" . $helper->forSQL($arParams["TAG_CHECK"]) . "' THEN b_user_counter.SENT ELSE ?v END", 'SENT'),
					]
				);
			}
			else
			{
				$insertFields = ['USER_ID', 'CNT', 'SITE_ID', 'CODE', 'SENT'];
				if (is_array($arParams) && isset($arParams["SET_TIMESTAMP"]))
				{
					$insertFields[] = 'TIMESTAMP_X';
				}
				$strSQL = $helper->prepareMergeSelect(
					'b_user_counter',
					['USER_ID', 'SITE_ID', 'CODE'],
					$insertFields,
					'(' . $sub_select . ')',
					[
						'CNT' => new \Bitrix\Main\DB\SqlExpression("b_user_counter.CNT + ?v", 'CNT'),
						'SENT' => new \Bitrix\Main\DB\SqlExpression("?v", 'SENT'),
					]
				);
			}

			$connection->query($strSQL);

			if (
				!is_array($arParams)
				|| (
					!isset($arParams["TAG_SET"])
					&& (
						!isset($arParams["CLEAN_CACHE"])
						|| $arParams["CLEAN_CACHE"] != "N"
					)
				)
			)
			{
				self::$counters = [];
				$CACHE_MANAGER->CleanDir("user_counter");
			}

			if ($pullInclude)
			{
				$arSites = Array();
				$by = '';
				$order = '';
				$res = CSite::GetList($by, $order, Array("ACTIVE" => "Y"));
				while($row = $res->Fetch())
				{
					$arSites[] = $row['ID'];
				}

				if (
					!empty($arParams["USERS_TO_PUSH"])
					&& is_array($arParams["USERS_TO_PUSH"])
				)
				{
					if($connection->lock('pull'))
					{
						$helper = $connection->getSqlHelper();

						$strSQL = "
							SELECT uc.USER_ID as CHANNEL_ID, uc.USER_ID, uc.SITE_ID, uc.CODE, uc.CNT
							FROM b_user_counter uc
							INNER JOIN b_user u ON u.ID = uc.USER_ID AND (CASE WHEN u.EXTERNAL_AUTH_ID IN ('" . implode("', '", \Bitrix\Main\UserTable::getExternalUserTypes())."') THEN 'Y' ELSE 'N' END) = 'N' AND u.LAST_ACTIVITY_DATE > " . $helper->addSecondsToDateTime('(-3600)')."
							WHERE uc.SENT = '0' AND uc.USER_ID IN (" . implode(", ", $arParams["USERS_TO_PUSH"]) . ")
						";

						$res = $connection->Query($strSQL);

						$pullMessage = Array();
						while($row = $res->fetch())
						{
							self::addValueToPullMessage($row, $arSites, $pullMessage);
						}

						$connection->Query("UPDATE b_user_counter SET SENT = '1' WHERE SENT = '0' AND CODE NOT LIKE '". self::LIVEFEED_CODE . "L%'");

						$connection->unlock('pull');

						if (self::CheckLiveMode())
						{
							foreach ($pullMessage as $channelId => $arMessage)
							{
								\Bitrix\Pull\Event::add($channelId, Array(
									'module_id' => 'main',
									'command' => 'user_counter',
									'expiry' => 3600,
									'params' => $arMessage,
								));
							}
						}
					}
				}
				else
				{
					CUserCounterPage::setNewEvent();
				}
			}
		}
	}

	public static function Clear($user_id, $code, $site_id = SITE_ID, $sendPull = true, $bMultiple = false, $cleanCache = true)
	{
		global $CACHE_MANAGER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$user_id = (int)$user_id;
		if (
			$user_id < 0
			|| $code == ''
		)
		{
			return false;
		}

		if (!is_array($site_id))
		{
			$site_id = [ $site_id ];
		}

		if ($bMultiple)
		{
			if($connection->lock('counter_delete'))
			{
				$siteToDelete = "";
				foreach ($site_id as $i => $site_id_tmp)
				{
					if ($i > 0)
					{
						$siteToDelete .= ",";
					}
					$siteToDelete .= "'".$helper->forSQL($site_id_tmp)."'";
				}

				$strDeleteSQL = "
					DELETE FROM b_user_counter
					WHERE
						USER_ID = ".$user_id."
						AND SITE_ID IN (".$siteToDelete.")
						AND CODE LIKE '".$helper->forSQL($code)."L%'
					";

				$connection->query($strDeleteSQL);

				foreach ($site_id as $i => $site_id_tmp)
				{
					$merge = $helper->prepareMerge('b_user_counter', ['USER_ID', 'SITE_ID', 'CODE'], [
						'USER_ID' => $user_id,
						'SITE_ID' => $site_id_tmp,
						'CODE' => $code,
						'CNT' => 0,
						'LAST_DATE' => new \Bitrix\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
					], [
						'CNT' => 0,
						'LAST_DATE' => new \Bitrix\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
					]);
					if ($merge[0])
					{
						$connection->query($merge[0]);
					}
				}

				$connection->unlock('counter_delete');
			}
		}
		else
		{
			foreach ($site_id as $i => $site_id_tmp)
			{
				$merge = $helper->prepareMerge('b_user_counter', ['USER_ID', 'SITE_ID', 'CODE'], [
					'USER_ID' => $user_id,
					'SITE_ID' => $site_id_tmp,
					'CODE' => $code,
					'CNT' => 0,
					'LAST_DATE' => new \Bitrix\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
				], [
					'CNT' => 0,
					'LAST_DATE' => new \Bitrix\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
				]);
				if ($merge)
				{
					$connection->Query($merge[0]);
				}
			}
		}

		if (self::$counters && self::$counters[$user_id])
		{
			foreach ($site_id as $site_id_tmp)
			{
				if ($site_id_tmp === self::ALL_SITES)
				{
					foreach (self::$counters[$user_id] as $key => $tmp)
					{
						self::$counters[$user_id][$key][$code] = 0;
					}
					break;
				}

				if (!isset(self::$counters[$user_id][$site_id_tmp]))
				{
					self::$counters[$user_id][$site_id_tmp] = array();
				}

				self::$counters[$user_id][$site_id_tmp][$code] = 0;
			}
		}

		if ($cleanCache)
		{
			$CACHE_MANAGER->Clean('user_counter' . $user_id, 'user_counter');
		}

		if ($sendPull)
		{
			self::SendPullEvent($user_id, $code);
		}

		return true;
	}

	public static function DeleteByCode($code)
	{
		global $DB, $CACHE_MANAGER;

		if ($code == '')
		{
			return false;
		}

		$pullMessage = Array();
		$bPullEnabled = false;

		$connection = \Bitrix\Main\Application::getConnection();

		$isLiveFeed = (
			str_starts_with($code, self::LIVEFEED_CODE)
			&& $code !== self::LIVEFEED_CODE
		);

		if ($isLiveFeed)
		{
			$DB->Query(
				"DELETE FROM b_user_counter WHERE CODE = '".$code."'"
			);

			self::$counters = [];
			$CACHE_MANAGER->CleanDir("user_counter");

			if (self::$isLiveFeedJobOn === false && self::CheckLiveMode())
			{
				$application = \Bitrix\Main\Application::getInstance();
				$application && $application->addBackgroundJob([__CLASS__, 'sendLiveFeedPull']);

				self::$isLiveFeedJobOn = true;
			}

			return true;
		}

		if (
			self::CheckLiveMode()
			&& $connection->lock('pull')
		)
		{
			$bPullEnabled = true;

			$arSites = [];
			$by = '';
			$order = '';
			$res = CSite::GetList($by, $order, array("ACTIVE" => "Y"));
			while($row = $res->Fetch())
			{
				$arSites[] = $row['ID'];
			}

			$helper = $connection->getSqlHelper();
			$strSQL = "
				SELECT uc.USER_ID as CHANNEL_ID, uc.USER_ID, uc.SITE_ID, uc.CODE, uc.CNT
				FROM b_user_counter uc
				INNER JOIN b_user u ON u.ID = uc.USER_ID AND (CASE WHEN u.EXTERNAL_AUTH_ID IN ('" . implode("', '", \Bitrix\Main\UserTable::getExternalUserTypes()) . "') THEN 'Y' ELSE 'N' END) = 'N' AND u.LAST_ACTIVITY_DATE > ".$helper->addSecondsToDateTime('(-3600)')."
				WHERE uc.CODE = '" . $code . "'";

			$res = $DB->Query($strSQL);

			while($row = $res->Fetch())
			{
				self::addValueToPullMessage($row, $arSites, $pullMessage);
			}
		}

		$DB->Query("DELETE FROM b_user_counter WHERE CODE = '".$code."'");

		self::$counters = [];
		$CACHE_MANAGER->CleanDir("user_counter");

		if ($bPullEnabled)
		{
			$connection->unlock('pull');
		}

		if (self::CheckLiveMode())
		{
			foreach ($pullMessage as $channelId => $arMessage)
			{
				\Bitrix\Pull\Event::add($channelId, Array(
					'module_id' => 'main',
					'command' => 'user_counter',
					'expiry' 	=> 3600,
					'params' => $arMessage,
				));
			}
		}

		return null;
	}

	protected static function dbIF($condition, $yes, $no)
	{
		return "CASE WHEN ".$condition." THEN ".$yes." ELSE ".$no."END ";
	}

	// legacy function
	public static function ClearByUser($user_id, $site_id = SITE_ID, $code = self::ALL_SITES, $bMultiple = false, $sendPull = true)
	{
		return self::Clear($user_id, $code, $site_id, $sendPull, $bMultiple);
	}

	public static function sendLiveFeedPull()
	{
		global $DB;

		$pullMessage = [];

		$connection = \Bitrix\Main\Application::getConnection();

		$connection->lock('pull');

		$sites = [];
		$by = '';
		$order = '';
		$queryObject = CSite::getList($by, $order, ['ACTIVE' => 'Y']);
		while ($row = $queryObject->fetch())
		{
			$sites[] = $row['ID'];
		}

		$helper = $connection->getSqlHelper();

		$strSQL = "
			SELECT uc.USER_ID as CHANNEL_ID, uc.USER_ID, uc.SITE_ID, uc.CODE, uc.CNT
			FROM b_user_counter uc
			INNER JOIN b_user u ON u.ID = uc.USER_ID AND (CASE WHEN u.EXTERNAL_AUTH_ID IN ('"
			. implode("', '", \Bitrix\Main\UserTable::getExternalUserTypes())
			. "') THEN 'Y' ELSE 'N' END) = 'N' AND u.LAST_ACTIVITY_DATE > "
			.$helper->addSecondsToDateTime('(-3600)')."
			WHERE uc.CODE LIKE '" . self::LIVEFEED_CODE . "%'
		";

		$queryObject = $DB->Query($strSQL);
		while($row = $queryObject->fetch())
		{
			self::addValueToPullMessage($row, $sites, $pullMessage);
		}

		$connection->unlock('pull');

		foreach ($pullMessage as $channelId => $arMessage)
		{
			\Bitrix\Pull\Event::add($channelId, [
				'module_id' => 'main',
				'command' => 'user_counter',
				'expiry' => 3600,
				'params' => $arMessage,
			]);
		}
	}
}

class CUserCounter extends CAllUserCounter
{
}
