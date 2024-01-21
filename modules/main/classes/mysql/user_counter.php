<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/user_counter.php");

class CUserCounter extends CAllUserCounter
{
	private static $isLiveFeedJobOn = false;

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

						$res = $connection->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

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
			mb_strpos($code, self::LIVEFEED_CODE) === 0
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

class CUserCounterPage extends CAllUserCounterPage
{
	public static function checkSendCounter()
	{
		global $DB, $USER;

		$connection = \Bitrix\Main\Application::getConnection();

		if(!$connection->lock('counterpull'))
		{
			return;
		}

		$counterPageSize = (int)CAllUserCounterPage::getPageSizeOption(100);

		$userSQL = "SELECT USER_ID FROM b_user_counter WHERE SENT='0' GROUP BY USER_ID LIMIT ".$counterPageSize;
		$res = $DB->Query($userSQL);

		$pullMessage = [];
		$userIdList = [];

		while ($row = $res->fetch())
		{
			$userIdList[] = (int)$row["USER_ID"];
		}

		if (
			is_object($USER)
			&& $USER->isAuthorized()
			&& count($userIdList) >= $counterPageSize
			&& !in_array((int)$USER->getId(), $userIdList, true)
		)
		{
			$userIdList[] = (int)$USER->getId();
		}

		$userString = '';
		foreach($userIdList as $userId)
		{
			$userString .= ($userString <> ''? ', ' : '').$userId;
		}

		if ($userString <> '')
		{
			$arSites = array();
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
				INNER JOIN b_user u ON u.ID = uc.USER_ID AND (CASE WHEN u.EXTERNAL_AUTH_ID IN ('" . implode("', '", \Bitrix\Main\UserTable::getExternalUserTypes()) . "') THEN 'Y' ELSE 'N' END) = 'N' AND u.LAST_ACTIVITY_DATE > " . $helper->addSecondsToDateTime('(-3600)')."
				WHERE uc.USER_ID IN (".$userString.") AND uc.CODE NOT LIKE '" . CUserCounter::LIVEFEED_CODE . "L%' AND uc.SENT = '0'
			";

			$res = $DB->Query($strSQL);
			while($row = $res->Fetch())
			{
				CUserCounter::addValueToPullMessage($row, $arSites, $pullMessage);
			}

			$strSQL = "
				SELECT uc.USER_ID as CHANNEL_ID, uc.USER_ID, uc.SITE_ID, uc.CODE, uc.CNT
				FROM b_user_counter uc
				INNER JOIN b_user u ON u.ID = uc.USER_ID AND (CASE WHEN u.EXTERNAL_AUTH_ID IN ('" . implode("', '", \Bitrix\Main\UserTable::getExternalUserTypes()) . "') THEN 'Y' ELSE 'N' END) = 'N' AND u.LAST_ACTIVITY_DATE > " . $helper->addSecondsToDateTime('(-3600)')."
				WHERE uc.USER_ID IN (" . $userString . ") AND uc.CODE LIKE '" . CUserCounter::LIVEFEED_CODE . "L%'
			";

			$res = $DB->Query($strSQL);
			while($row = $res->Fetch())
			{
				CUserCounter::addValueToPullMessage($row, $arSites, $pullMessage);
			}

			$DB->Query("UPDATE b_user_counter SET SENT = '1' WHERE SENT = '0' AND USER_ID IN (".$userString.")");
		}

		$connection->unlock('counterpull');

		if (\CUserCounter::CheckLiveMode())
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
