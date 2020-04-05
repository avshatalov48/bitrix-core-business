<?
class CAllPullWatch
{
	const bucket_size = 100;

	private static $arUpdate = Array();
	private static $arInsert = Array();

	public static function Add($userId, $tag, $immediate = false)
	{
		global $DB, $CACHE_MANAGER;

		$userId = intval($userId);
		if ($userId == 0 || strlen($tag) <= 0)
			return false;

		$arResult = $CACHE_MANAGER->Read(3600, $cache_id="b_pw_".$userId, "b_pull_watch");
		if ($arResult)
			$arResult = $CACHE_MANAGER->Get($cache_id);

		if(!$arResult)
		{
			CTimeZone::Disable();
			$strSql = "
					SELECT ID, USER_ID, TAG, ".$DB->DatetimeToTimestampFunction("DATE_CREATE")." DATE_CREATE
					FROM b_pull_watch
					WHERE USER_ID = ".intval($userId)."
			";
			CTimeZone::Enable();
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while ($arRes = $dbRes->Fetch())
				$arResult[$arRes["TAG"]] = $arRes;

			$CACHE_MANAGER->Set($cache_id, $arResult);
		}
		if ($arResult && $arResult[$tag])
		{
			if ($arResult[$tag]['DATE_CREATE']+1860 > time())
			{
				self::$arUpdate[intval($arResult[$tag]['ID'])] = intval($arResult[$tag]['ID']);
				return true;
			}
			else
			{
				self::Delete($userId, $tag);
				return self::Add($userId, $tag);
			}
		}
		$CACHE_MANAGER->Clean("b_pw_".$userId, "b_pull_watch");

		self::$arInsert[trim($tag)] = trim($tag);

		if ($immediate || defined('BX_CHECK_AGENT_START') && !defined('BX_WITH_ON_AFTER_EPILOG'))
		{
			self::DeferredSql($userId);
		}

		return true;
	}

	public static function DeferredSql($userId = false)
	{
		global $DB, $USER;
		if (empty(self::$arUpdate) && empty(self::$arInsert))
			return false;

		$userId = intval($userId);
		if (!$userId)
		{
			if (defined('PULL_USER_ID'))
			{
				$userId = PULL_USER_ID;
			}
			else if (is_object($GLOBALS['USER']) && $GLOBALS['USER']->GetID() > 0)
			{
				$userId = $GLOBALS['USER']->GetId();
			}
			else if (intval($_SESSION["SESS_SEARCHER_ID"]) <= 0 && intval($_SESSION["SESS_GUEST_ID"]) > 0 && \CPullOptions::GetGuestStatus())
			{
				$userId = intval($_SESSION["SESS_GUEST_ID"])*-1;
			}
		}
		if ($userId === 0)
		{
			return false;
		}

		$arChannel = CPullChannel::Get($userId);
		if (!empty(self::$arUpdate))
		{
			$DB->Query("
				UPDATE b_pull_watch
				SET DATE_CREATE = ".$DB->CurrentTimeFunction().", CHANNEL_ID = '".$DB->ForSQL($arChannel['CHANNEL_ID'])."'
				WHERE ID IN (".(implode(',', self::$arUpdate)).")
			");
		}

		$dbType = strtolower($DB->type);
		if ($dbType == "mysql")
		{
			if (!empty(self::$arInsert))
			{
				$strSqlPrefix = "INSERT INTO b_pull_watch (USER_ID, CHANNEL_ID, TAG, DATE_CREATE) VALUES ";
				$maxValuesLen = 2048;
				$strSqlValues = "";

				foreach(self::$arInsert as $tag)
				{
					$strSqlValues .= ",\n(".intval($userId).", '".$DB->ForSql($arChannel['CHANNEL_ID'])."', '".$DB->ForSql($tag)."', ".$DB->CurrentTimeFunction().")";
					if(strlen($strSqlValues) > $maxValuesLen)
					{
						$DB->Query($strSqlPrefix.substr($strSqlValues, 2));
						$strSqlValues = "";
					}
				}
				if(strlen($strSqlValues) > 0)
				{
					$DB->Query($strSqlPrefix.substr($strSqlValues, 2));
				}
			}
		}
		else if (!empty(self::$arInsert))
		{
			foreach(self::$arInsert as $tag)
			{
				$DB->Query("INSERT INTO b_pull_watch (USER_ID, CHANNEL_ID, TAG, DATE_CREATE) VALUES (".intval($userId).", '".$DB->ForSql($arChannel['CHANNEL_ID'])."', '".$DB->ForSql($tag)."', ".$DB->CurrentTimeFunction().")");
			}
		}

		self::$arInsert = Array();
		self::$arUpdate = Array();

		return true;
	}

	public static function Delete($userId, $tag = null)
	{
		global $DB, $CACHE_MANAGER;

		$strSql = "DELETE FROM b_pull_watch WHERE USER_ID = ".intval($userId).(!is_null($tag)? " AND TAG = '".$DB->ForSQL($tag)."'": "");
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$CACHE_MANAGER->Clean("b_pw_".$userId, "b_pull_watch");

		return true;
	}

	public static function Extend($userId, $tags)
	{
		global $DB, $CACHE_MANAGER;

		if (intval($userId) == 0)
		{
			return false;
		}

		if (is_array($tags))
		{
			$isMulti = true;
			$searchTag = '';
			if (empty($tags))
			{
				return false;
			}
		}
		else
		{
			$isMulti = false;
			$searchTag = trim($tags);
			if (strlen($searchTag) <= 0)
			{
				return false;
			}
			else
			{
				$tags = Array($searchTag);
			}
		}

		$result = Array();
		foreach ($tags as $id => $tag)
		{
			$result[$tag] = false;
			$tags[$id] = $DB->ForSQL($tag);
		}

		$updateIds = Array();
		$strSql = "SELECT ID, TAG FROM b_pull_watch WHERE USER_ID = ".intval($userId)." AND TAG IN ('".implode("', '", $tags)."')";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while ($arRes = $dbRes->Fetch())
		{
			$updateIds[] = $arRes['ID'];
			$result[$arRes['TAG']] = true;
		}

		if ($updateIds)
		{
			$DB->Query("UPDATE b_pull_watch SET DATE_CREATE = ".$DB->CurrentTimeFunction()." WHERE ID IN (".implode(', ', $updateIds).")");
			$CACHE_MANAGER->Clean("b_pw_".$userId, "b_pull_watch");
		}

		return $isMulti? $result: $result[$searchTag];
	}

	public static function AddToStack($tag, $parameters, $channelType = \CPullChannel::TYPE_PRIVATE)
	{
		global $DB;

		$users = Array();

		$result = $DB->Query("SELECT USER_ID FROM b_pull_watch WHERE TAG = '".$DB->ForSQL($tag)."'");
		while ($row = $result->Fetch())
		{
			if (isset($parameters['skip_users']) && in_array($row['USER_ID'], $parameters['skip_users']))
				continue;

			$users[] = $row['USER_ID'];
		}

		\Bitrix\Pull\Event::add($users, $parameters, $channelType);

		return true;
	}

	public static function GetUserList($tag)
	{
		global $DB;

		$arUsers = Array();
		$strSql = "SELECT USER_ID FROM b_pull_watch WHERE TAG = '".$DB->ForSQL($tag)."'";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while ($arRes = $dbRes->Fetch())
			$arUsers[$arRes['USER_ID']] = $arRes['USER_ID'];

		return $arUsers;
	}
}
?>