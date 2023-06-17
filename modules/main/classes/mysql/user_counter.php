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
		global $DB, $CACHE_MANAGER;

		$user_id = (int)$user_id;
		if ($user_id < 0 || $code == '')
		{
			return false;
		}

		$increment = (int)$increment;

		$strSQL = "
			INSERT INTO b_user_counter (USER_ID, CNT, SITE_ID, CODE)
			VALUES (".$user_id.", ".$increment.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."')
			ON DUPLICATE KEY UPDATE CNT = CNT + ".$increment;
		$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (self::$counters && self::$counters[$user_id])
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
		global $DB, $CACHE_MANAGER;

		$user_id = (int)$user_id;
		if ($user_id < 0 || $code == '')
		{
			return false;
		}

		$decrement = (int)$decrement;

		$strSQL = "
			INSERT INTO b_user_counter (USER_ID, CNT, SITE_ID, CODE)
			VALUES (".$user_id.", -".$decrement.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."')
			ON DUPLICATE KEY UPDATE CNT = CNT - ".$decrement;
		$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

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
		global $DB, $CACHE_MANAGER;

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
				$strSQL = "
					INSERT INTO b_user_counter (USER_ID, CNT, SITE_ID, CODE, SENT, TAG".(isset($arParams["SET_TIMESTAMP"]) ? ", TIMESTAMP_X" : "").") (".$sub_select.")
					ON DUPLICATE KEY UPDATE CNT = CNT + VALUES(CNT), SENT = VALUES(SENT), TAG = '".$DB->ForSQL($arParams["TAG_SET"])."'
				";
			}
			elseif (
				is_array($arParams)
				&& isset($arParams["TAG_CHECK"])
			)
			{
				$strSQL = "
					INSERT INTO b_user_counter (USER_ID, CNT, SITE_ID, CODE, SENT".(isset($arParams["SET_TIMESTAMP"]) ? ", TIMESTAMP_X" : "").") (".$sub_select.")
					ON DUPLICATE KEY UPDATE CNT = CASE
						WHEN
							TAG = '".$DB->ForSQL($arParams["TAG_CHECK"])."'
						THEN
							CNT

						ELSE
							CNT + VALUES(CNT)
						END,
						SENT = CASE
						WHEN
							TAG = '".$DB->ForSQL($arParams["TAG_CHECK"])."'
						THEN
							SENT
						ELSE
							SENT = VALUES(SENT)
						END
				";
			}
			else
			{
				$strSQL = "
					INSERT INTO b_user_counter (USER_ID, CNT, SITE_ID, CODE, SENT".(is_array($arParams) && isset($arParams["SET_TIMESTAMP"]) ? ", TIMESTAMP_X" : "").") (".$sub_select.")
					ON DUPLICATE KEY UPDATE CNT = CNT + VALUES(CNT), SENT = VALUES(SENT)
				";
			}

			$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

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
					$connection = \Bitrix\Main\Application::getConnection();
					if($connection->lock('pull'))
					{
						$helper = $connection->getSqlHelper();

						$strSQL = "
							SELECT uc.USER_ID as CHANNEL_ID, uc.USER_ID, uc.SITE_ID, uc.CODE, uc.CNT
							FROM b_user_counter uc
							INNER JOIN b_user u ON u.ID = uc.USER_ID AND (CASE WHEN u.EXTERNAL_AUTH_ID IN ('" . implode("', '", \Bitrix\Main\UserTable::getExternalUserTypes())."') THEN 'Y' ELSE 'N' END) = 'N' AND u.LAST_ACTIVITY_DATE > " . $helper->addSecondsToDateTime('(-3600)')."
							WHERE uc.SENT = '0' AND uc.USER_ID IN (" . implode(", ", $arParams["USERS_TO_PUSH"]) . ")
						";

						$res = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

						$pullMessage = Array();
						while($row = $res->Fetch())
						{
							self::addValueToPullMessage($row, $arSites, $pullMessage);
						}

						$DB->Query("UPDATE b_user_counter SET SENT = '1' WHERE SENT = '0' AND CODE NOT LIKE '". self::LIVEFEED_CODE . "L%'");

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
		global $DB, $CACHE_MANAGER;

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
			$connection = \Bitrix\Main\Application::getConnection();
			if($connection->lock('counter_delete'))
			{
				$siteToDelete = "";
				$strUpsertSQL = "
					INSERT INTO b_user_counter (USER_ID, SITE_ID, CODE, CNT, LAST_DATE) VALUES ";

				foreach ($site_id as $i => $site_id_tmp)
				{
					if ($i > 0)
					{
						$strUpsertSQL .= ",";
						$siteToDelete .= ",";
					}

					$siteToDelete .= "'".$DB->ForSQL($site_id_tmp)."'";
					$strUpsertSQL .= " (" . $user_id . ", '" . $DB->ForSQL($site_id_tmp) . "', '" . $DB->ForSQL($code) . "', 0, " . CDatabase::CurrentTimeFunction() . ") ";
				}
				$strUpsertSQL .= " ON DUPLICATE KEY UPDATE CNT = 0, LAST_DATE = " . CDatabase::CurrentTimeFunction();

				$strDeleteSQL = "
					DELETE FROM b_user_counter
					WHERE
						USER_ID = ".$user_id."
						".(
					count($site_id) == 1
						? " AND SITE_ID = '".$site_id[0]."' "
						: " AND SITE_ID IN (".$siteToDelete.") "
					)."
						AND CODE LIKE '".$DB->ForSQL($code)."L%'
					";

				$DB->Query($strDeleteSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
				$DB->Query($strUpsertSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

				$connection->unlock('counter_delete');
			}
		}
		else
		{
			$strSQL = "
				INSERT INTO b_user_counter (USER_ID, SITE_ID, CODE, CNT, LAST_DATE) VALUES ";

			foreach ($site_id as $i => $site_id_tmp)
			{
				if ($i > 0)
				{
					$strSQL .= ",";
				}
				$strSQL .= " (" . $user_id . ", '" . $DB->ForSQL($site_id_tmp) . "', '" . $DB->ForSQL($code) . "', 0, " . CDatabase::CurrentTimeFunction() . ") ";
			}

			$strSQL .= " ON DUPLICATE KEY UPDATE CNT = 0, LAST_DATE = " . CDatabase::CurrentTimeFunction();

			$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
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
				"DELETE FROM b_user_counter WHERE CODE = '".$code."'",
				false,
				"FILE: " . __FILE__ . "<br> LINE: " . __LINE__
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

			$res = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

			while($row = $res->Fetch())
			{
				self::addValueToPullMessage($row, $arSites, $pullMessage);
			}
		}

		$DB->Query("DELETE FROM b_user_counter WHERE CODE = '".$code."'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

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
		return "if(".$condition.", ".$yes.", ".$no.")";
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

		$queryObject = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
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
		$res = $DB->Query($userSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

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

			$res = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
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

			$res = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
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
