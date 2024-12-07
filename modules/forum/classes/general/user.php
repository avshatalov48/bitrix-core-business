<?php

use Bitrix\Forum\UserTable;

IncludeModuleLangFile(__FILE__);
/**********************************************************************/
/************** FORUM USER ********************************************/
/**********************************************************************/
class CAllForumUser
{
	public static function IsAdmin($userId = false, $arGroups = false)
	{
		global $USER;
		if (is_array($userId) && !empty($userId) && $arGroups === false)
		{
			$arGroups = $userId;
			$userId = false;
		}
		if (is_array($arGroups) && !empty($arGroups))
		{
			//
		}
		elseif (!is_object($USER))
		{
			$arGroups = array(2);
		}
		elseif ($userId === false || $userId == $USER->GetID())
		{
			$arGroups = $USER->GetUserGroupArray();
		}
		else
		{
			$arGroups = $USER->GetUserGroup($userId);
		}
		$result = (
			in_array(1, $arGroups) ||
			$GLOBALS["APPLICATION"]->GetGroupRight("forum", $arGroups) >= "W"
		);
		return $result;
	}

	public static function GetUserTopicVisits($forumID, $arTopic, $userID=null)
	{
		global $DB, $USER;

		$arResult = array();

		$forumID = intval($forumID);
		if ($userID == null)
			$userID = $USER->GetID();
		else
			$userID = intval($userID);
		if (($forumID <= 0) || ($userID <= 0))
		{
			return $arResult;
		}
		$arSelectTopic = array();
		foreach ($arTopic as $topicID)
			$arSelectTopic[] = intval($topicID);
		$arSelectTopic = array_unique(array_filter($arSelectTopic));
		if (sizeof($arSelectTopic) < 1)
		{
			return $arResult;
		}
		$sTopicIDs = implode(",", $arSelectTopic);

		$strSql = "SELECT FUT.TOPIC_ID,
			".$DB->DateToCharFunction("FUT.LAST_VISIT", "FULL")." as LAST_VISIT
			FROM b_forum_user_topic FUT
			WHERE (FORUM_ID=".$forumID." AND USER_ID=".$userID." AND TOPIC_ID IN (".$sTopicIDs."))";
		$rVisit = $DB->Query($strSql);
		if ($rVisit)
		{
			while ($arVisit = $rVisit->Fetch())
			{
				$arResult[$arVisit['TOPIC_ID']] = $arVisit['LAST_VISIT'];
			}
		}
		return $arResult;
	}

	public static function IsLocked($userID)
	{
		$userID = (int) $userID;
		if ($userID <= 0)
		{
			return false;
		}

		$cacheID = 'b_forum_user_locked_' . $userID;
		$cache = Bitrix\Main\Application::getInstance()->getManagedCache();

		if (CACHED_b_forum_user !== false && $cache->read(CACHED_b_forum_user, $cacheID, 'b_forum_user'))
		{
			$result = $cache->get($cacheID);
		}
		else
		{
			$allow = Bitrix\Forum\UserTable::query()
				->addSelect('ALLOW_POST')
				->where('USER_ID', $userID)
				->fetch();

			if ($allow)
			{
				$result = $allow['ALLOW_POST'];
			}
			else
			{
				$result = 'Y';
			}

			$cache->set($cacheID, $result);
		}

		return $result != 'Y';
	}

	public static function CanUserAddUser($arUserGroups)
	{
		return True;
	}

	public static function CanUserUpdateUser($ID, $arUserGroups, $CurrentUserID = 0)
	{
		$ID = intval($ID);
		$CurrentUserID = intval($CurrentUserID);
		if (in_array(1, $arUserGroups)) return True;
		$arUser = CForumUser::GetByID($ID);
		if ($arUser && intval($arUser["USER_ID"]) == $CurrentUserID) return True;
		return False;
	}

	public static function CanUserDeleteUser($ID, $arUserGroups)
	{
		$ID = intval($ID);
		if (in_array(1, $arUserGroups)) return True;
		return False;
	}

	public static function CheckFields($ACTION, &$arFields, $ID=false)
	{
		if (is_set($arFields, "AVATAR") && $arFields["AVATAR"]["name"] == '' && $arFields["AVATAR"]["del"] == '')
		{
			unset($arFields["AVATAR"]);
		}

		$aMsg = array();
		// Checking user for updating or adding
		// USER_ID as value
		if ((is_set($arFields, "USER_ID") || $ACTION=="ADD") && intval($arFields["USER_ID"]) <= 0)
		{
			$aMsg[] = array(
				"id" => 'EMPTY_USER_ID',
				"text" => GetMessage("F_GL_ERR_EMPTY_USER_ID"));
		}
		elseif (is_set($arFields, "USER_ID"))
		{
			$user = CUser::GetByID($arFields["USER_ID"])->fetch();
			if (empty($user))
			{
				$aMsg[] = array(
					"id" => 'USER_DOES_NOT_EXIST',
					"text" => GetMessage("F_GL_ERR_USER_NOT_EXIST", array("#UID#" => htmlspecialcharsbx($arFields["USER_ID"]))));
			}

			$res = CForumUser::GetByUSER_ID($arFields["USER_ID"]);

			if ($ACTION == "UPDATE")
			{
				unset($arFields["USER_ID"]);
			}
			else if (!empty($res))
			{
				$aMsg[] = array(
					"id" => 'USER_EXISTS',
					"text" => GetMessage("F_GL_ERR_USER_IS_EXIST", array("#UID#" => htmlspecialcharsbx($arFields["USER_ID"]))));
			}
			else if (!array_key_exists("AVATAR", $arFields) && $user["PERSONAL_PHOTO"] > 0)
			{
				$arFields["AVATAR"] = CFile::MakeFileArray($user["PERSONAL_PHOTO"]);
				if (!$arFields["AVATAR"] || CFile::ResizeImage($arFields["AVATAR"], array(
					"width" => COption::GetOptionInt("forum", "avatar_max_width", 100),
					"height" => COption::GetOptionInt("forum", "avatar_max_height", 100)))
				)
				{
					unset($arFields["AVATAR"]);
				}
			}
		}
		// last visit
		if (is_set($arFields, "LAST_VISIT"))
		{
			$arFields["LAST_VISIT"] = trim($arFields["LAST_VISIT"]);
			if ($arFields["LAST_VISIT"] <> '')
			{
				if ($arFields["LAST_VISIT"] != $GLOBALS["DB"]->GetNowFunction() && !$GLOBALS["DB"]->IsDate($arFields["LAST_VISIT"], false, SITE_ID, "FULL"))
					$aMsg[] = array(
						"id" => 'LAST_VISIT',
						"text" => GetMessage("F_GL_ERR_LAST_VISIT"));
			}
			else
			{
				unset($arFields["LAST_VISIT"]);
			}
		}
		// date registration
		if (is_set($arFields, "DATE_REG"))
		{
			$arFields["DATE_REG"] = trim($arFields["DATE_REG"]);
			if ($arFields["DATE_REG"] <> '')
			{
				if ($arFields["DATE_REG"] != $GLOBALS["DB"]->GetNowFunction() && !$GLOBALS["DB"]->IsDate($arFields["DATE_REG"], false, SITE_ID, "SHORT"))
				{
					$aMsg[] = array(
						"id" => 'DATE_REG',
						"text" => GetMessage("F_GL_ERR_DATE_REG"));
				}
			}
			else
			{
				unset($arFields["DATE_REG"]);
			}
		}
		// avatar
		if (is_set($arFields, "AVATAR"))
		{
			$max_size = COption::GetOptionInt("forum", "file_max_size", 5242880);
			$size = array(
				"width" => COption::GetOptionInt("forum", "avatar_max_width", 100),
				"height" => COption::GetOptionInt("forum", "avatar_max_height", 100));
			$res = CFile::CheckImageFile($arFields["AVATAR"], $max_size);
			if ($res == '')
			{
				$res = CFile::CheckImageFile($arFields["AVATAR"], $max_size, $size["width"], $size["height"]);
				if ($res <> '' && CFile::ResizeImage($arFields["AVATAR"], $size))
					$res = '';
			}
			if ($res <> '')
			{
				$aMsg[] = array(
					"id" => 'AVATAR',
					"text" => $res);
			}
		}

		if (!empty($aMsg))
		{
			$e = new CAdminException(array_reverse($aMsg));
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		// show name
		if (is_set($arFields, "SHOW_NAME") || $ACTION == "ADD")
		{
			if (empty($arFields["SHOW_NAME"]))
				$arFields["SHOW_NAME"] = COption::GetOptionString("forum", "USER_SHOW_NAME", "Y") == "Y" ? "Y" : "N";
			$arFields["SHOW_NAME"] = ($arFields["SHOW_NAME"] == "N" ? "N" : "Y");
		}
		// allow post
		if (is_set($arFields, "ALLOW_POST") || $ACTION=="ADD")
		{
			$arFields["ALLOW_POST"] = ($arFields["ALLOW_POST"] == "N" ? "N" : "Y");
		}
		return True;
	}

	public static function Add($fields, $strUploadDir = false)
	{
		if (!is_array($fields) || empty($fields))
		{
			return false;
		}

		if (!array_key_exists("AVATAR", $fields))
		{
			$user = CUser::GetByID($fields["USER_ID"])->fetch();
			if ($user["PERSONAL_PHOTO"] > 0)
			{
				$fields["AVATAR"] = CFile::MakeFileArray($user["PERSONAL_PHOTO"]);
			}
		}
		$result = \Bitrix\Forum\UserTable::add($fields);
		if (!$result->isSuccess())
		{
			return false;
		}

		global $CACHE_MANAGER;
		$CACHE_MANAGER->cleanDir("b_forum_user");

		$id = $result->getPrimary();
		return $id["ID"];
	}

	public static function Update($ID, $arFields, $strUploadDir = false, $UpdateByUserId = false)
	{
		if (!is_array($arFields) || empty($arFields))
		{
			return false;
		}

		if ($UpdateByUserId)
		{
			if ($res = \Bitrix\Forum\UserTable::getList([
				"select" => ["ID"],
				"filter" => ["USER_ID" => $ID]
			])->fetch())
			{
				$ID = intval($res["ID"]);
			}
		}
		if ($ID <= 0)
		{
			return false;
		}

		if (is_set($arFields, "ALLOW_POST"))
		{
			if (CACHED_b_forum_user !== false)
				$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum_user");
		}
		unset($GLOBALS["FORUM_CACHE"]["USER"]);
		unset($GLOBALS["FORUM_CACHE"]["USER_ID"]);

		$entity = \Bitrix\Forum\UserTable::getEntity();
		$data = [];
		foreach ($arFields as $k => $v)
		{
			$k = (mb_strpos($k, "=") === 0? mb_substr($k, 1) : $k);
			if ($entity->hasField($k))
			{
				$field = $entity->getField($k);
				$data[$k] = $v;
				if ($field instanceof \Bitrix\Main\ORM\Fields\DateField)
				{
					$data[$k] = new \Bitrix\Main\Type\DateTime(\Bitrix\Main\Type\DateTime::isCorrect($v) ? $v : null);
				}
				else if (
					isset($v)
					&& !is_array($v)
					&& preg_match("/{$k}\s*(\+|\-)\s*(\d+)/", $v, $matches)
				)
				{
					$data[$k] = new \Bitrix\Main\DB\SqlExpression("?# $matches[1] $matches[2]", $k);
				}
			}
		}

		$result = \Bitrix\Forum\User::update($ID, $data);
		if (!$result->isSuccess())
		{
			return false;
		}
		return $ID;
	}

	public static function Delete($ID)
	{
		$result = \Bitrix\Forum\UserTable::delete($ID);
		unset($GLOBALS["FORUM_CACHE"]["USER"]);
		unset($GLOBALS["FORUM_CACHE"]["USER_ID"]);
		return $result->isSuccess();
	}

	public static function CountUsers($bActive = False, $arFilter = array())
	{
		global $DB;
		$arFilter = (is_array($arFilter) ? $arFilter : array());
		$arSqlSearch = array();
		$strSqlSearch = "";
		if ($bActive)
			$arSqlSearch[] = "NUM_POSTS > 0";
		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = mb_strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ACTIVE":
					if ($val == '')
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(U.".$key." IS NULL OR ".($DB->type == "MSSQL" ? "LEN" : "LENGTH")."(U.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" U.".$key." IS NULL OR NOT (":"")."U.".$key." ".$strOperation." '".$DB->ForSql($val)."'".
							($strNegative=="Y"?")":"");
					break;
			}
		}
		if (count($arSqlSearch) > 0)
			$strSqlSearch = " WHERE (".implode(") AND (", $arSqlSearch).") ";

		$strSql = "SELECT COUNT(FU.ID) AS CNT FROM b_forum_user FU INNER JOIN b_user U ON (U.ID = FU.USER_ID)".$strSqlSearch;
		$db_res = $DB->Query($strSql);
		if ($ar_res = $db_res->Fetch())
			return $ar_res["CNT"];

		return 0;
	}

	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		if (isset($GLOBALS["FORUM_CACHE"]["USER"][$ID]) && is_array($GLOBALS["FORUM_CACHE"]["USER"][$ID]) && is_set($GLOBALS["FORUM_CACHE"]["USER"][$ID], "ID"))
		{
			return $GLOBALS["FORUM_CACHE"]["USER"][$ID];
		}
		else
		{
			$strSql =
				"SELECT FU.ID, FU.USER_ID, FU.SHOW_NAME, FU.DESCRIPTION, FU.IP_ADDRESS,
					FU.REAL_IP_ADDRESS, FU.AVATAR, FU.NUM_POSTS, FU.POINTS as NUM_POINTS, FU.INTERESTS,
					FU.HIDE_FROM_ONLINE, FU.SUBSC_GROUP_MESSAGE, FU.SUBSC_GET_MY_MESSAGE,
					FU.LAST_POST, FU.ALLOW_POST, FU.SIGNATURE, FU.RANK_ID, FU.POINTS,
					".$DB->DateToCharFunction("FU.DATE_REG", "SHORT")." as DATE_REG,
					".$DB->DateToCharFunction("FU.LAST_VISIT", "FULL")." as LAST_VISIT
				FROM b_forum_user FU
				WHERE FU.ID = ".$ID;
			$db_res = $DB->Query($strSql);
			if ($res = $db_res->Fetch())
			{
				$GLOBALS["FORUM_CACHE"]["USER"][$ID] = $res;
				return $res;
			}
		}
		return False;
	}

	public static function GetByLogin($Name)
	{
		global $DB;
		$Name = $DB->ForSql(trim($Name));
		if (
			isset($GLOBALS["FORUM_CACHE"]["USER_NAME"]) &&
			is_set($GLOBALS["FORUM_CACHE"]["USER_NAME"], $Name) &&
			is_array($GLOBALS["FORUM_CACHE"]["USER_NAME"][$Name]) &&
			is_set($GLOBALS["FORUM_CACHE"]["USER_NAME"][$Name], "ID"))
		{
			return $GLOBALS["FORUM_CACHE"]["USER_NAME"][$Name];
		}
		else
		{
			$strSql =
				"SELECT ID AS USER_ID
				FROM b_user
				WHERE LOGIN='".$Name."'";
			$db_res = $DB->Query($strSql);
			$res = $db_res->Fetch();
			if (!empty($res["USER_ID"]))
			{
				$strSql =
					"SELECT FU.ID, FU.USER_ID, FU.SHOW_NAME, FU.DESCRIPTION, FU.IP_ADDRESS,
						FU.REAL_IP_ADDRESS, FU.AVATAR, FU.NUM_POSTS, FU.POINTS as NUM_POINTS,
						FU.INTERESTS, FU.HIDE_FROM_ONLINE, FU.SUBSC_GROUP_MESSAGE, FU.SUBSC_GET_MY_MESSAGE,
						FU.LAST_POST, FU.ALLOW_POST, FU.SIGNATURE, FU.RANK_ID, FU.POINTS,
						".$DB->DateToCharFunction("FU.DATE_REG", "SHORT")." as DATE_REG,
						".$DB->DateToCharFunction("FU.LAST_VISIT", "FULL")." as LAST_VISIT
					FROM b_forum_user FU
					WHERE FU.USER_ID = ".$res["USER_ID"];
				$db_res = $DB->Query($strSql);
				if ($res = $db_res->Fetch())
				{
					$GLOBALS["FORUM_CACHE"]["USER"][$res["USER_ID"]] = $res;
					$GLOBALS["FORUM_CACHE"]["USER_NAME"][$Name] = $res;
					return $res;
				}
			}
		}

		return False;
	}

	public static function GetByIDEx($ID, $arAddParams = array())
	{
		global $DB;
		$arAddParams = (is_array($arAddParams) ? $arAddParams : array($arAddParams));

		$ID = intval($ID);
		$strSql =
			"SELECT FU.ID, FU.USER_ID, FU.SHOW_NAME, FU.DESCRIPTION, FU.IP_ADDRESS,\n ".
			"	FU.REAL_IP_ADDRESS, FU.AVATAR, FU.NUM_POSTS, FU.POINTS as NUM_POINTS, FU.INTERESTS,\n ".
			"	FU.LAST_POST, FU.ALLOW_POST, FU.SIGNATURE, FU.RANK_ID,\n ".
			"	U.EMAIL, U.NAME, U.SECOND_NAME, U.LAST_NAME, U.LOGIN, U.PERSONAL_BIRTHDATE,\n ".
			"	".$DB->DateToCharFunction("FU.DATE_REG", "SHORT")." as DATE_REG,\n ".
			"	".$DB->DateToCharFunction("FU.LAST_VISIT", "FULL")." as LAST_VISIT,\n ".
			"	U.PERSONAL_ICQ, U.PERSONAL_WWW, U.PERSONAL_PROFESSION,\n ".
			"	U.PERSONAL_CITY, U.PERSONAL_COUNTRY, U.EXTERNAL_AUTH_ID, U.PERSONAL_PHOTO,\n ".
			"	U.PERSONAL_GENDER, FU.POINTS, FU.HIDE_FROM_ONLINE, FU.SUBSC_GROUP_MESSAGE, FU.SUBSC_GET_MY_MESSAGE,\n ".
			"	".$DB->DateToCharFunction("U.PERSONAL_BIRTHDAY", "SHORT")." as PERSONAL_BIRTHDAY ".
			(array_key_exists("SHOW_ABC", $arAddParams) || in_array("SHOW_ABC", $arAddParams) ?
				", \n\t".CForumUser::GetFormattedNameFieldsForSelect(
					array_merge(
						$arAddParams,
						array(
							"sUserTablePrefix" => "U.",
							"sForumUserTablePrefix" => "FU.",
							"sFieldName" => "SHOW_ABC"),
						false
					)
				) : "")."\n".
			" FROM b_user U, b_forum_user FU \n".
			" WHERE FU.USER_ID = U.ID AND FU.ID = ".$ID." ";
		$db_res = $DB->Query($strSql);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	public static function GetByUSER_ID($USER_ID)
	{
		global $DB;

		$USER_ID = intval($USER_ID);
		if (isset($GLOBALS["FORUM_CACHE"]["USER_ID"][$USER_ID]) && is_array($GLOBALS["FORUM_CACHE"]["USER_ID"][$USER_ID]) && is_set($GLOBALS["FORUM_CACHE"]["USER_ID"][$USER_ID], "ID"))
		{
			return $GLOBALS["FORUM_CACHE"]["USER_ID"][$USER_ID];
		}
		else
		{
			$strSql =
				"SELECT FU.ID, FU.USER_ID, FU.SHOW_NAME, FU.DESCRIPTION, FU.IP_ADDRESS,
					FU.REAL_IP_ADDRESS, FU.AVATAR, FU.NUM_POSTS, FU.POINTS as NUM_POINTS,
					FU.INTERESTS, FU.HIDE_FROM_ONLINE, FU.SUBSC_GROUP_MESSAGE, FU.SUBSC_GET_MY_MESSAGE,
					FU.LAST_POST, FU.ALLOW_POST, FU.SIGNATURE, FU.RANK_ID, FU.POINTS,
					".$DB->DateToCharFunction("FU.DATE_REG", "SHORT")." as DATE_REG,
					".$DB->DateToCharFunction("FU.LAST_VISIT", "FULL")." as LAST_VISIT
				FROM b_forum_user FU
				WHERE FU.USER_ID = ".$USER_ID;
			$db_res = $DB->Query($strSql);

			if ($db_res && $res = $db_res->Fetch())
			{
				$GLOBALS["FORUM_CACHE"]["USER_ID"][$USER_ID] = $res;
				return $res;
			}
		}
		return False;
	}

	public static function GetByUSER_IDEx($USER_ID, $arAddParams = array())
	{
		global $DB;
		$arAddParams = (is_array($arAddParams) ? $arAddParams : array($arAddParams));

		$USER_ID = intval($USER_ID);
		$strSql =
			"SELECT F_USER.*, FU.ID, FU.USER_ID, FU.SHOW_NAME, FU.DESCRIPTION, FU.IP_ADDRESS,\n ".
			"	FU.REAL_IP_ADDRESS, FU.AVATAR, FU.NUM_POSTS, FU.POINTS as NUM_POINTS,\n ".
			"	FU.INTERESTS, FU.HIDE_FROM_ONLINE, FU.SUBSC_GROUP_MESSAGE, FU.SUBSC_GET_MY_MESSAGE,\n ".
			"	FU.LAST_POST, FU.ALLOW_POST, FU.SIGNATURE, FU.RANK_ID, FU.POINTS,\n ".
			"	".$DB->DateToCharFunction("FU.DATE_REG", "SHORT")." as DATE_REG,\n ".
			"	".$DB->DateToCharFunction("FU.LAST_VISIT", "FULL")." as LAST_VISIT,\n ".
			"	U.EMAIL, U.NAME, U.SECOND_NAME, U.LAST_NAME, U.LOGIN, U.PERSONAL_BIRTHDATE,\n ".
			"	U.PERSONAL_ICQ, U.PERSONAL_WWW, U.PERSONAL_PROFESSION,\n ".
			"	U.PERSONAL_CITY, U.PERSONAL_COUNTRY, U.EXTERNAL_AUTH_ID, U.PERSONAL_PHOTO, U.PERSONAL_GENDER,\n ".
			"	".$DB->DateToCharFunction("U.PERSONAL_BIRTHDAY", "SHORT")." as PERSONAL_BIRTHDAY ".
			(array_key_exists("SHOW_ABC", $arAddParams) || in_array("SHOW_ABC", $arAddParams) ?
				", \n\t".CForumUser::GetFormattedNameFieldsForSelect(
					array_merge(
						$arAddParams,
						array(
							"sUserTablePrefix" => "U.",
							"sForumUserTablePrefix" => "FU.",
							"sFieldName" => "SHOW_ABC"),
						false
					)
				) : ""). "\n".
			" FROM b_forum_user FU \n".
			" INNER JOIN b_user U ON (FU.USER_ID = U.ID) \n".
			" LEFT JOIN ( \n".
			"	 SELECT FM.AUTHOR_ID, MAX(FM.ID) AS LAST_MESSAGE_ID, COUNT(FM.ID) AS CNT \n".
			"	 FROM b_forum_message FM \n".
			"	 WHERE (FM.AUTHOR_ID = ".$USER_ID." AND FM.APPROVED = 'Y') \n".
			"	 GROUP BY FM.AUTHOR_ID \n".
			"	) F_USER ON (F_USER.AUTHOR_ID = FU.USER_ID) \n".
			" WHERE (FU.USER_ID = ".$USER_ID.")";
		$db_res = $DB->Query($strSql);

		if ($db_res && $res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}


	public static function GetUserRank($USER_ID, $strLang = false)
	{
		$USER_ID = intval($USER_ID);
		$arUser = false;
		if ($USER_ID <= 0) return false;

		if (COption::GetOptionString("forum", "SHOW_VOTES", "Y") == "Y")
			$arUser = CForumUser::GetByUSER_ID($USER_ID);
		else
		{
			$authorityRatingId = CRatings::GetAuthorityRating();
			$arRatingResult = CRatings::GetRatingResult($authorityRatingId, $USER_ID);
			if (isset($arRatingResult['CURRENT_VALUE']))
				$arUser = array('POINTS' => round(floatval($arRatingResult['CURRENT_VALUE'])/COption::GetOptionString("main", "rating_vote_weight", 1)));
		}
		if ($arUser)
		{
			if ($strLang === false || mb_strlen($strLang) != 2)
				$db_res = CForumPoints::GetList(array("MIN_POINTS"=>"DESC"), array("<=MIN_POINTS"=>$arUser["POINTS"]));
			else
				$db_res = CForumPoints::GetListEx(array("MIN_POINTS"=>"DESC"), array("<=MIN_POINTS"=>$arUser["POINTS"], "LID" => $strLang));

			if ($db_res && ($ar_res = $db_res->Fetch()))
				return $ar_res;
		}
		return false;
	}
	//---------------> User visited
	public static function SetUserForumLastVisit($USER_ID, $FORUM_ID = 0, $LAST_VISIT = false)
	{
		global $DB;
		$USER_ID = intval($USER_ID);
		$FORUM_ID = intval($FORUM_ID);
		if (is_int($LAST_VISIT)):
			$LAST_VISIT = $DB->CharToDateFunction(date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL")), $LAST_VISIT), "FULL");
		elseif (is_string($LAST_VISIT)):
			$LAST_VISIT = $DB->CharToDateFunction(trim($LAST_VISIT), "FULL");
		else:
			$LAST_VISIT = false;
		endif;

		if (!$LAST_VISIT):
			$Fields = array("LAST_VISIT" => $DB->GetNowFunction());
			$rows = $DB->Update("b_forum_user_forum", $Fields, "WHERE (FORUM_ID=".$FORUM_ID." AND USER_ID=".$USER_ID.")", "File: ".__FILE__."<br>Line: ".__LINE__);

			if (intval($rows) <= 0):
				$Fields["USER_ID"] = $USER_ID;
				$Fields["FORUM_ID"] = $FORUM_ID;
				$DB->Insert("b_forum_user_forum", $Fields, "File: ".__FILE__."<br>Line: ".__LINE__);
			elseif ($FORUM_ID <= 0):
				$DB->Query("DELETE FROM b_forum_user_forum WHERE (FORUM_ID > 0 AND USER_ID=".$USER_ID.")");
				$DB->Query("DELETE FROM b_forum_user_topic WHERE (USER_ID=".$USER_ID.")");
			else:
				$DB->Query("DELETE FROM b_forum_user_topic WHERE (FORUM_ID=".$FORUM_ID." AND USER_ID=".$USER_ID.")");
			endif;
		else:
			$Fields = array("LAST_VISIT" => $LAST_VISIT);
			$rows = $DB->Update("b_forum_user_forum", $Fields,
				"WHERE (FORUM_ID=".$FORUM_ID." AND USER_ID=".$USER_ID.")", "File: ".__FILE__."<br>Line: ".__LINE__);

			if (intval($rows) <= 0):
				$Fields = array("LAST_VISIT" => $LAST_VISIT, "FORUM_ID" => $FORUM_ID, "USER_ID" => $USER_ID);
				$DB->Insert("b_forum_user_forum", $Fields, "File: ".__FILE__."<br>Line: ".__LINE__);
			elseif ($FORUM_ID <= 0):
				$DB->Query("DELETE FROM b_forum_user_forum WHERE (FORUM_ID > 0 AND USER_ID=".$USER_ID." AND LAST_VISIT <= ".$LAST_VISIT.")");
				$DB->Query("DELETE FROM b_forum_user_topic WHERE (USER_ID=".$USER_ID." AND LAST_VISIT <= ".$LAST_VISIT.")");
			else:
				$DB->Query("DELETE FROM b_forum_user_topic WHERE (FORUM_ID=".$FORUM_ID." AND USER_ID=".$USER_ID." AND LAST_VISIT <= ".$LAST_VISIT.")");
			endif;
		endif;
		return true;
	}

	public static function GetListUserForumLastVisit($arOrder = Array("LAST_VISIT"=>"DESC"), $arFilter = Array())
	{
		global $DB;
		$arSqlSearch = Array();
		$arSqlOrder = Array();
		$strSqlSearch = "";
		$strSqlOrder = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = mb_strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ID":
				case "USER_ID":
				case "FORUM_ID":
					if (intval($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FUF.".$key." IS NULL OR FUF.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FUF.".$key." IS NULL OR NOT ":"")."(FUF.".$key." ".$strOperation." ".intval($val)." )";
					break;
			}
		}
		if (!empty($arSqlSearch))
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).")";
		foreach ($arOrder as $by=>$order)
		{
			$by = mb_strtoupper($by);
			$order = mb_strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "USER_ID") $arSqlOrder[] = " FUF.USER_ID ".$order." ";
			elseif ($by == "FORUM_ID") $arSqlOrder[] = " FUF.FORUM_ID ".$order." ";
			elseif ($by == "LAST_VISIT") $arSqlOrder[] = " FUF.LAST_VISIT ".$order." ";
			else
			{
				$arSqlOrder[] = " FU.ID ".$order." ";
				$by = "ID";
			}
		}
		DelDuplicateSort($arSqlOrder);
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		$strSql = "
			SELECT FUF.ID, FUF.FORUM_ID,  FUF.USER_ID, ".$DB->DateToCharFunction("FUF.LAST_VISIT", "FULL")." as LAST_VISIT
			FROM b_forum_user_forum FUF
				INNER JOIN b_user U ON (U.ID = FUF.USER_ID)
			WHERE 1=1 ".$strSqlSearch."
			".$strSqlOrder;
		$db_res = $DB->Query($strSql);
		return $db_res;
	}
	//---------------> User visited

	//---------------> User utils/

	/**
	 * Returns formatted user name
	 * @param integer $userID
	 * @param string $template
	 * @param array $arUser - array with user data
	 * @return string
	 */
	public static function GetFormattedNameByUserID($userID, $template = "", $arUser = array())
	{
		if (empty($userID))
			return false;

		static $arUsers = array();

		if (!array_key_exists($userID, $arUsers))
		{
			$arUsers[$userID] = (!empty($arUser) ? $arUser : CForumUser::GetByUSER_ID($userID));
			$arUsers[$userID] = (!empty($arUsers[$userID]) ? $arUsers[$userID] : array());
			$arUsers[$userID]["SHOW_NAME"] = ($arUsers[$userID]["SHOW_NAME"] == "Y" ? "Y" : "N");
			if (!array_key_exists("LOGIN", $arUsers[$userID]) || !array_key_exists("NAME", $arUsers[$userID]) ||
				!array_key_exists("SECOND_NAME", $arUsers[$userID]) || !array_key_exists("LAST_NAME", $arUsers[$userID])
			)
			{
				$dbRes = CUser::GetByID($userID);
				if (($arRes = $dbRes->Fetch()) && $arRes)
					$arUsers[$userID] = array_merge($arRes, $arUsers[$userID]);
			}
			$arUsers[$userID]["FORMATTED_NAME"] = ($arUsers[$userID]["SHOW_NAME"] == "Y" ?
				CUser::FormatName($template, $arUsers[$userID], false, false) : "");
			$arUsers[$userID]["FORMATTED_NAME"] = (empty($arUsers[$userID]["FORMATTED_NAME"]) ||
				$arUsers[$userID]["FORMATTED_NAME"] == GetMessage("FORMATNAME_NONAME")?
					$arUsers[$userID]["LOGIN"] : $arUsers[$userID]["FORMATTED_NAME"]);
		}
		return $arUsers[$userID]["FORMATTED_NAME"];
	}

	public static function GetUserPoints($USER_ID, $arAddParams = array())
	{
		global $DB;
		$USER_ID = intval($USER_ID);
		if ($USER_ID <= 0) return 0;
		$arAddParams = (is_array($arAddParams) ? $arAddParams : array($arAddParams));
		$arAddParams["INCREMENT"] = intval($arAddParams["INCREMENT"] ?? 0);
		$arAddParams["DECREMENT"] = intval($arAddParams["DECREMENT"] ?? 0);
		$arAddParams["NUM_POSTS"] = (is_set($arAddParams, "NUM_POSTS") ? $arAddParams["NUM_POSTS"] : false);
		$arAddParams["RETURN_FETCH"] = (isset($arAddParams["RETURN_FETCH"]) && $arAddParams["RETURN_FETCH"] == "Y" ? "Y" : "N");
		$strSql = "
			SELECT
			(".
				($arAddParams["NUM_POSTS"] ? $arAddParams["NUM_POSTS"] : "FU.NUM_POSTS").
				($arAddParams["INCREMENT"] > 0 ? "+".$arAddParams["INCREMENT"] : "").
				($arAddParams["DECREMENT"] > 0 ? "-".$arAddParams["DECREMENT"] : "").
				") AS NUM_POSTS, FP2P.MIN_NUM_POSTS, FP2P.POINTS_PER_POST, SUM(FUP.POINTS) AS POINTS_FROM_USER
			FROM
				b_forum_user FU
				LEFT JOIN b_forum_points2post FP2P ON (FP2P.MIN_NUM_POSTS <= ".
				($arAddParams["NUM_POSTS"] ? $arAddParams["NUM_POSTS"] : "FU.NUM_POSTS").
				($arAddParams["INCREMENT"] > 0 ? "+".$arAddParams["INCREMENT"] : "").
				($arAddParams["DECREMENT"] > 0 ? "-".$arAddParams["DECREMENT"] : "").")
				LEFT JOIN b_forum_user_points FUP ON (FUP.TO_USER_ID = FU.USER_ID)
			WHERE
				FU.user_id = ".$USER_ID."
			GROUP BY
				".($arAddParams["NUM_POSTS"] ? "" : "FU.NUM_POSTS, ")."FP2P.MIN_NUM_POSTS, FP2P.POINTS_PER_POST
			ORDER BY FP2P.MIN_NUM_POSTS DESC";

		$db_res = $DB->Query($strSql);
		if ($arAddParams["RETURN_FETCH"] == "Y"):
			return $db_res;
		elseif ($db_res && ($res = $db_res->Fetch())):
			$result = floor(doubleVal($res["POINTS_PER_POST"])*intval($res["NUM_POSTS"]) + intval($res["POINTS_FROM_USER"]));
			return $result;
		endif;
		return false;
	}

	public static function CountUserPoints($USER_ID = 0, $iCnt = false)
	{
		$USER_ID = intval($USER_ID);
		$iNumUserPosts = intval($iCnt);
		$iNumUserPoints = 0;
		$fPointsPerPost = 0.0;
		if ($USER_ID <= 0) return 0;

		if ($iCnt === false):
			$iNumUserPoints = CForumUser::GetUserPoints($USER_ID);
		endif;

		if ($iNumUserPoints === false || $iCnt != false):
			$iNumUserPosts = CForumMessage::GetList(array(), array("AUTHOR_ID" => $USER_ID, "APPROVED" => "Y"), true);
			$db_res = CForumPoints2Post::GetList(array("MIN_NUM_POSTS" => "DESC"), array("<=MIN_NUM_POSTS" => $iNumUserPosts));
			if ($ar_res = $db_res->Fetch())
				$fPointsPerPost = DoubleVal($ar_res["POINTS_PER_POST"]);
			$iNumUserPoints = floor($fPointsPerPost*$iNumUserPosts);
			$iCnt = CForumUserPoints::CountSumPoints($USER_ID);
			$iNumUserPoints += $iCnt;
		endif;
		return $iNumUserPoints;
	}

	public static function SetStat($userId = 0, $params = [])
	{
		$enableCalculateStatistics = COption::GetOptionString('forum', 'enable_calculate_statistics', 'Y');
		if (
			$enableCalculateStatistics === 'N'
			|| empty($userId)
			|| (!empty($params['MESSAGE']['APPROVED']) && $params['MESSAGE']['APPROVED'] !== 'Y')
		)
		{
			return;
		}

		$userId = intval($userId);
		$params = is_array($params) ? $params : [];
		$bNeedCreateUser = false;
		$arUser = [];
		$arUserFields = [];

		if (isset($params['MESSAGE']['AUTHOR_ID']) && $params['MESSAGE']['AUTHOR_ID'] === $userId)
		{
			$arMessage = $params['MESSAGE'];
			$params['ACTION'] = $params['ACTION'] ?? 'INCREMENT';
			if ($params['ACTION'] == 'UPDATE')
			{
				$params['ACTION'] = ($arMessage['APPROVED'] == 'Y' ? 'INCREMENT' : 'DECREMENT');
				$arMessage['APPROVED'] = 'Y';
			}

			$params['POSTS'] = intval($params['POSTS'] ?? 1);
			$bNeedCreateUser = !($arUser = CForumUser::GetByUSER_ID($userId));

			if ($params['ACTION'] == 'DECREMENT' && $arMessage['ID'] < $arUser['LAST_POST'])
			{
				$arUserFields = [
					'=NUM_POSTS' => 'NUM_POSTS-'.$params['POSTS'],
					'POINTS' => intval(CForumUser::GetUserPoints($userId, ['DECREMENT' => $params['POSTS']]))
				];
			}
			else if ($params['ACTION'] == 'INCREMENT' && $arMessage['ID'] < $arUser['LAST_POST'])
			{
				$arUserFields = [
					'=NUM_POSTS' => 'NUM_POSTS+'.$params['POSTS'],
					'POINTS' => intval(CForumUser::GetUserPoints($userId, ['INCREMENT' => $params['POSTS']]))
				];
			}
			else if ($params['ACTION'] == 'INCREMENT')
			{
				$arUserFields['IP_ADDRESS'] = $arMessage['AUTHOR_IP'];
				$arUserFields['REAL_IP_ADDRESS'] = $arMessage['AUTHOR_REAL_IP'];
				$arUserFields['LAST_POST'] = intval($arMessage['ID']);
				$arUserFields['=NUM_POSTS'] = 'NUM_POSTS+' . $params['POSTS'];
				$arUserFields['POINTS'] = CForumUser::GetUserPoints($userId, ['INCREMENT' => $params['POSTS']]);
			}
		}

		if (empty($arUserFields))
		{
			$arUserFields = [
				'LAST_POST' => false
			];
			if ($bNeedCreateUser === false)
				$arUser = CForumUser::GetByUSER_IDEx($userId);
			if (empty($arUser) || $bNeedCreateUser == true):
				$bNeedCreateUser = true;
				$arUser = CForumMessage::GetList(array(), array('AUTHOR_ID' => $userId, 'APPROVED' => 'Y'), 'cnt_and_last_mid');
				$arUser = (is_array($arUser) ? $arUser : array());
			endif;
			$arMessage = CForumMessage::GetByID($arUser['LAST_MESSAGE_ID'], array('FILTER' => 'N'));
			if ($arMessage):
				$arUserFields['IP_ADDRESS'] = $arMessage['AUTHOR_IP'];
				$arUserFields['REAL_IP_ADDRESS'] = $arMessage['AUTHOR_REAL_IP'];
				$arUserFields['LAST_POST'] = intval($arMessage['ID']);
			endif;
			$arUserFields['NUM_POSTS'] = intval($arUser['CNT']);
			$arUserFields['POINTS'] = intval(CForumUser::GetUserPoints($userId, array('NUM_POSTS' => $arUserFields['NUM_POSTS'])));
		}

		if ($bNeedCreateUser):
			$arUserFields['USER_ID'] = $userId;
			CForumUser::Add($arUserFields);
		else:
			CForumUser::Update($userId, $arUserFields, false, true);
		endif;

		return $userId;
	}
	//---------------> User actions
	public static function OnUserDelete($user_id)
	{
		global $DB;
		$user_id = intval($user_id);
		if ($user_id>0)
		{
			$DB->Query("UPDATE b_forum SET LAST_POSTER_ID = NULL WHERE LAST_POSTER_ID = ".$user_id."");
			$DB->Query("UPDATE b_forum_topic SET LAST_POSTER_ID = NULL WHERE LAST_POSTER_ID = ".$user_id."");
			$DB->Query("UPDATE b_forum_topic SET USER_START_ID = NULL WHERE USER_START_ID = ".$user_id."");
			$DB->Query("UPDATE b_forum_message SET AUTHOR_ID = NULL WHERE AUTHOR_ID = ".$user_id."");
			$DB->Query("DELETE FROM b_forum_subscribe WHERE USER_ID = ".$user_id."");
			$DB->Query("DELETE FROM b_forum_stat WHERE USER_ID = ".$user_id."");

			$strSql = "
				SELECT
					F.ID
				FROM
					b_forum_user FU,
					b_file F
				WHERE
					FU.USER_ID = $user_id
				and FU.AVATAR = F.ID
				";
			$z = $DB->Query($strSql);
			while ($zr = $z->Fetch()) CFile::Delete($zr["ID"]);

			$DB->Query("DELETE FROM b_forum_user WHERE USER_ID = ".$user_id."");

			if(CModule::IncludeModule("socialnetwork"))
			{
				$dbRes = $DB->Query("select ID from b_forum_topic where OWNER_ID=".$user_id);
				while($arRes = $dbRes->Fetch())
				{
					$DB->Query("DELETE FROM b_forum_message WHERE TOPIC_ID = ".$arRes["ID"]);
					$DB->Query("DELETE FROM b_forum_topic WHERE ID = ".$arRes["ID"]);
				}

			}
		}
		return true;
	}
	// >-- Using for private message
	public static function SearchUser($template)
	{
		global $DB;
		$template = $DB->ForSql(str_replace("*", "%", $template));

		$strSql =
			"SELECT U.ID, U.NAME, U.LAST_NAME, U.LOGIN, F.SHOW_NAME ".
			"FROM b_forum_user F LEFT JOIN b_user U ON(F.USER_ID = U.ID)".
			"WHERE ((F.SHOW_NAME='Y')AND(U.NAME LIKE '".$template."' OR U.LAST_NAME LIKE '".$template."')) OR(( U.LOGIN LIKE '".$template."')AND(F.SHOW_NAME='N'))";
		$dbRes = $DB->Query($strSql);
		return $dbRes;
	}

	public static function UserAddInfo($arOrder = array(), $arFilter = Array(), $mode = false, $iNum = 0, $check_permission = true, $arNavigation = array())
	{
		global $DB, $USER;

		$arSqlFrom = array();
		$arSqlOrder = array();
		$arSqlSearch = array();
		$strSqlFrom = "";
		$strSqlOrder = "";
		$strSqlSearch = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());
		if (!CForumUser::IsAdmin() && $check_permission)
		{
			$arFilter["LID"] = SITE_ID;
			$arFilter["PERMISSION"] = true;
		}

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = mb_strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];
			switch ($key)
			{
				case "ID":
				case "AUTHOR_ID":
				case "FORUM_ID":
				case "TOPIC_ID":
					if ($strOperation == 'IN'):
						$res = (is_array($val) ? $val : explode(",", $val));
						$val = array();
						foreach ($res as $v)
							$val[] = intval($v);
						$val = implode(",", $val);
					else:
						$val = intval($val);
					endif;
					if ($val <= 0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FM.".$key." IS NULL OR FM.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FM.".$key." IS NULL OR NOT ":"")."FM.".$key." ".$strOperation." (".$DB->ForSql($val).")";
					break;
				case "APPROVED":
					if ($val == '')
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FM.".$key." IS NULL OR ".($DB->type == "MSSQL" ? "LEN" : "LENGTH")."(FM.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FM.".$key." IS NULL OR NOT ":"")."FM.".$key." ".$strOperation." '".$DB->ForSql($val)."'";
					break;
				case "DATE":
				case "POST_DATE":
					if ($val == '')
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."FM.".$key." IS NULL";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FM.".$key." IS NULL OR NOT ":"")."FM.".$key." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "SHORT");
					break;
				case "LID":
					$arSqlFrom["FS2"] = "LEFT JOIN b_forum2site FS2 ON (FS2.FORUM_ID = FM.FORUM_ID)";
					$arSqlSearch[] = ($strNegative=="Y"?" NOT ":"")."(FS2.SITE_ID ".$strOperation." '".$DB->ForSql($val)."')";
					break;
				case "ACTIVE":
					$arSqlFrom["F"] = "INNER JOIN b_forum F ON (F.ID = FM.FORUM_ID)";
					if ($val == '')
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(F.".$key." IS NULL OR ".($DB->type == "MSSQL" ? "LEN" : "LENGTH")."(F.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" F.".$key." IS NULL OR NOT ":"")."F.".$key." ".$strOperation." '".$DB->ForSql($val)."'";
					break;
				case "USER_START_ID":
					if (!is_array($val))
						$val = array($val);
					$tmp = array();
					foreach ($val as $k=>$v)
						$tmp[] = intval(trim($v));
					$val = implode(",", $tmp);
					$arSqlFrom["FT"] = "INNER JOIN b_forum_topic FT ON (FT.ID = FM.TOPIC_ID)";
					if ($val == '')
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."FT.".$key." IS NULL OR FT.".$key."<=0";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FT.".$key." IS NULL OR NOT ":"")."FT.".$key." ".$strOperation." (".$DB->ForSql($val).")";
					break;
				case "PERMISSION":
					$arSqlFrom["FP"] = "
						INNER JOIN (
							SELECT FP.FORUM_ID, MAX(FP.PERMISSION) AS PERMISSION
							FROM b_forum_perms FP
							WHERE FP.GROUP_ID IN (".$DB->ForSql(implode(",", $USER->GetUserGroupArray())).") AND FP.PERMISSION > 'A'
							GROUP BY FP.FORUM_ID) FPP ON (FPP.FORUM_ID = FM.FORUM_ID) ";
					$arSqlSearch[] = "(FPP.PERMISSION > 'A' AND (FM.APPROVED='Y' OR FPP.PERMISSION >= 'Q'))";
					break;
				case "TOPIC_TITLE":
				case "POST_MESSAGE":
					if ($key == "TOPIC_TITLE")
					{
						$key = "FT.TITLE";
						$arSqlFrom["FT"] = "INNER JOIN b_forum_topic FT ON (FT.ID = FM.TOPIC_ID)";
					}
					else
						$key = "FM.POST_MESSAGE";
					if ($strOperation == "LIKE")
						$val = "%".$val."%";

					if ($val == '')
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(".$key." IS NULL OR ".($DB->type == "MSSQL" ? "LEN" : "LENGTH")."(".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" ".$key." IS NULL OR NOT ":"")."(".$key." ".$strOperation." '".$DB->ForSQL($val)."')";
					break;
			}
		}
		ksort($arSqlFrom);
		if (count($arSqlFrom) > 0)
			$strSqlFrom = " ".implode(" ", $arSqlFrom);

		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).")";

		foreach ($arOrder as $key=>$val)
		{
			$key = mb_strtoupper($key); $val = (mb_strtoupper($val) != "ASC" ? "DESC" : "ASC");
			switch ($key)
			{
				case "FIRST_POST":
				case "LAST_POST":
					$arSqlOrder["LAST_POST"] = "FMM.".$key." ".$val;
				break;
				case "FORUM_ID":
				case "TOPIC_ID":
					$arSqlOrder["ID"] = " FT.".$key." ".$val;
				break;
			}
		}
		if (count($arSqlOrder)>0)
			$strSqlOrder = "ORDER BY ".implode(", ", $arSqlOrder);
		else
			$strSqlOrder = "ORDER BY FMM.FIRST_POST DESC";

		// *****************************************************
		$strSql = "
		SELECT FMM.*, FT.TITLE, FT.DESCRIPTION, FT.VIEWS, FT.LAST_POSTER_ID,
			".CForumNew::Concat("-", array("FT.ID", "FT.TITLE_SEO"))." as TITLE_SEO,
			".$DB->DateToCharFunction("FT.START_DATE", "FULL")." as START_DATE,
			FT.USER_START_NAME,	FT.USER_START_ID, FT.POSTS, FT.LAST_POSTER_NAME,
			FT.LAST_MESSAGE_ID, '' as IMAGE, '' as IMAGE_DESCR,
			FT.APPROVED, FT.STATE, FT.FORUM_ID, FT.ICON, FT.SORT, FT.HTML
		FROM
		(
			SELECT FM.TOPIC_ID, COUNT(FM.ID) AS COUNT_MESSAGE, MIN(FM.ID) AS FIRST_POST, MAX(FM.ID) AS LAST_POST
			FROM b_forum_message FM
			".$strSqlFrom."
			WHERE 1=1
			".$strSqlSearch."
			GROUP BY FM.TOPIC_ID
		) FMM
		LEFT JOIN b_forum_topic FT ON (FT.ID = FMM.TOPIC_ID)
		".$strSqlOrder;


		$cnt = false;
		if (! empty($arNavigation))
		{
			$strCountSql = "
				SELECT COUNT( DISTINCT FM.TOPIC_ID ) CNT
				FROM b_forum_message FM
				".$strSqlFrom."
				WHERE 1=1
				".$strSqlSearch;

			$dbCount_res = $DB->Query($strCountSql);
			if ($dbCount_res && $arCount = $dbCount_res->Fetch())
			{
				$cnt = $arCount['CNT'];
			}
		}

		if (empty($arNavigation) || !$cnt)
		{
			$db_res = $DB->Query($strSql);
		}
		else
		{
			if (isset($arNavigation["SIZEN"]) && $arNavigation["SIZEN"])
				$arNavigation["nPageSize"] = $arNavigation["SIZEN"];
			if (isset($arNavigation["PAGEN"]) && $arNavigation["PAGEN"])
				$arNavigation["iNumPage"] = $arNavigation["PAGEN"];
			$db_res = new CDBResult();
			$db_res->NavQuery($strSql, $cnt, $arNavigation);
		}

		return new _CTopicDBResult($db_res, $arNavigation);
	}
	// <-- Using for private message

	public static function OnSocNetGroupDelete($group_id)
	{
		global $DB;
		$group_id = intval($group_id);
		if ($group_id>0)
		{
			if(CModule::IncludeModule("socialnetwork"))
			{
				$dbRes = $DB->Query("select ID from b_forum_topic where SOCNET_GROUP_ID=".$group_id);
				while($arRes = $dbRes->Fetch())
				{
					CForumTopic::Delete($arRes["ID"]);
				}

			}
		}
		return true;
	}

	public static function OnAfterUserUpdate($arFields = array())
	{
		if ($arFields["RESULT"] &&
			array_key_exists("PERSONAL_PHOTO", $arFields) && $arFields["PERSONAL_PHOTO"] > 0 &&
			($user = CForumUser::GetByUSER_ID($arFields["ID"])) && $user &&
			($avatar = CFile::MakeFileArray($arFields["PERSONAL_PHOTO"])) &&
			is_array($avatar))
		{
			self::Update($user["ID"], array( "AVATAR" => $avatar + array("old_file" => $user["AVATAR"])));
		}
	}
}


/**********************************************************************/
/************** SUBSCRIBE *********************************************/
/**********************************************************************/
class CAllForumSubscribe
{
	//---------------> User insert, update, delete
	public static function CanUserAddSubscribe($FID, $arUserGroups)
	{
		if (CForumNew::GetUserPermission($FID, $arUserGroups)>="E") return True;
		return False;
	}

	public static function CanUserUpdateSubscribe($ID, $arUserGroups, $CurrentUserID = 0)
	{
		$ID = intval($ID);
		$CurrentUserID = intval($CurrentUserID);
		if (in_array(1, $arUserGroups)) return True;

		$arSubscr = CForumSubscribe::GetByID($ID);
		if ($arSubscr && intval($arSubscr["USER_ID"]) == $CurrentUserID) return True;
		return False;
	}

	public static function CanUserDeleteSubscribe($ID, $arUserGroups, $CurrentUserID = 0)
	{
		$ID = intval($ID);
		$CurrentUserID = intval($CurrentUserID);
		if (in_array(1, $arUserGroups)) return True;

		$arSubscr = CForumSubscribe::GetByID($ID);
		if ($arSubscr && intval($arSubscr["USER_ID"]) == $CurrentUserID) return True;
		return False;
	}

	public static function CheckFields($ACTION, &$arFields)
	{
		if ((is_set($arFields, "USER_ID") || $ACTION=="ADD") && intval($arFields["USER_ID"])<=0) return false;
		if ((is_set($arFields, "FORUM_ID") || $ACTION=="ADD") && intval($arFields["FORUM_ID"])<=0) return false;
		if ((is_set($arFields, "SITE_ID") || $ACTION=="ADD") && $arFields["SITE_ID"] == '') return false;

		if ((is_set($arFields, "TOPIC_ID") || $ACTION=="ADD") && intval($arFields["TOPIC_ID"])<=0) $arFields["TOPIC_ID"] = false;
		if ((is_set($arFields, "NEW_TOPIC_ONLY") || $ACTION=="ADD") && ($arFields["NEW_TOPIC_ONLY"]!="Y")) $arFields["NEW_TOPIC_ONLY"] = "N";

		if ($arFields["TOPIC_ID"]!==false) $arFields["NEW_TOPIC_ONLY"] = "N";
		if ($ACTION=="ADD")
		{
			$arFilter = array("USER_ID"=>intval($arFields["USER_ID"]), "FORUM_ID"=>intval($arFields["FORUM_ID"]), "TOPIC_ID"=>intval($arFields["TOPIC_ID"]));
			if(isset($arFields["SOCNET_GROUP_ID"]) && $arFields["SOCNET_GROUP_ID"])
				$arFilter["SOCNET_GROUP_ID"] = $arFields["SOCNET_GROUP_ID"];
			$db_res = CForumSubscribe::GetList(array(), $arFilter);
			if ($res = $db_res->Fetch())
			{
				return false;
			}
		}

		return True;
	}

	public static function Add($arFields)
	{
		global $DB;

		if (!CForumSubscribe::CheckFields("ADD", $arFields))
			return false;

		$Fields = array(
			"USER_ID" => intval($arFields["USER_ID"]),
			"FORUM_ID" => intval($arFields["FORUM_ID"]),
			"START_DATE" => $DB->GetNowFunction(),
			"NEW_TOPIC_ONLY" => "'".$DB->ForSQL($arFields["NEW_TOPIC_ONLY"], 1)."'",
			"SITE_ID" => "'".$DB->ForSQL($arFields["SITE_ID"], 2)."'",
			);

		if(isset($arFields["SOCNET_GROUP_ID"]) && intval($arFields["SOCNET_GROUP_ID"])>0)
			$Fields["SOCNET_GROUP_ID"] = intval($arFields["SOCNET_GROUP_ID"]);

		if (intval($arFields["TOPIC_ID"]) > 0)
			$Fields["TOPIC_ID"] = intval($arFields["TOPIC_ID"]);

		return $DB->Insert("b_forum_subscribe", $Fields, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	public static function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);

		if (!CForumSubscribe::CheckFields("UPDATE", $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_forum_subscribe", $arFields);
		$strSql = "UPDATE b_forum_subscribe SET ".$strUpdate." WHERE ID = ".$ID;
		$DB->Query($strSql);

		return $ID;
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		return $DB->Query("DELETE FROM b_forum_subscribe WHERE ID = ".$ID, True);
	}

	public static function DeleteUSERSubscribe($USER_ID)
	{
		global $DB;
		$USER_ID = intval($USER_ID);
		return $DB->Query("DELETE FROM b_forum_subscribe WHERE USER_ID = ".$USER_ID);
	}

	public static function UpdateLastSend($MID, $sIDs)
	{
		global $DB;
		$MID = intval($MID);
		$arID = explode(",", $sIDs);
		if ($MID <= 0 || empty($sIDs) || (count($arID) == 1 && intval($arID[0]) == 0))
			return false;

		$arUpdateIDs = array();
		foreach ($arID as $sID)
		{
			$value = intval($sID);
			if ($value > 0) $arUpdateIDs[] = $value;
		}
		if (count($arUpdateIDs) < 1)
			return false;

		$DB->Query("UPDATE b_forum_subscribe SET LAST_SEND = ".$MID." WHERE ID IN (".implode(',', $arUpdateIDs).")");
	}

	public static function GetList($arOrder = array("ID"=>"ASC"), $arFilter = array(), $arAddParams = array())
	{
		global $DB;
		$arSqlSearch = Array();
		$arFilter = (is_array($arFilter) ? $arFilter : array());
		$arAddParams = (is_array($arAddParams) ? $arAddParams : array());

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = mb_strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ID":
				case "USER_ID":
				case "FORUM_ID":
				case "TOPIC_ID":
				case "LAST_SEND":
					if (intval($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FP.".$key." IS NULL OR FP.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FP.".$key." IS NULL OR NOT ":"")."(FP.".$key." ".$strOperation." ".intval($val)." )";
					break;
				case "TOPIC_ID_OR_NULL":
					$arSqlSearch[] = "(FP.TOPIC_ID = ".intval($val)." OR FP.TOPIC_ID = 0 OR FP.TOPIC_ID IS NULL)";
					break;
				case "NEW_TOPIC_ONLY":
					if ($val == '')
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FP.NEW_TOPIC_ONLY IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FP.NEW_TOPIC_ONLY IS NULL OR NOT ":"")."(FP.NEW_TOPIC_ONLY ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "SOCNET_GROUP_ID":
					if($val>0)
						$arSqlSearch[] = "FP.SOCNET_GROUP_ID=".intval($val);
					else
						$arSqlSearch[] = "FP.SOCNET_GROUP_ID IS NULL";
					break;
				case "LAST_SEND_OR_NULL":
					$arSqlSearch[] = "(FP.LAST_SEND IS NULL OR FP.LAST_SEND = 0 OR FP.LAST_SEND < ".intval($val).")";
					break;
			}
		}

		$strSqlSearch = (empty($arSqlSearch) ? "" : " AND (".implode(") AND (", $arSqlSearch).") ");

		$iCnt = 0;
		if (is_set($arAddParams, "bDescPageNumbering") || is_set($arAddParams, "nCount"))
		{
			$strSql = "SELECT COUNT(FP.ID) AS CNT FROM b_forum_subscribe FP WHERE 1 = 1 ".$strSqlSearch;
			$db_res = $DB->Query($strSql);
			if ($ar_res = $db_res->Fetch())
				$iCnt = intval($ar_res["CNT"]);
			if (is_set($arAddParams, "nCount"))
				return $iCnt;
		}

		$strSql =
			"SELECT FP.ID, FP.USER_ID, FP.FORUM_ID, FP.TOPIC_ID, FP.LAST_SEND, FP.NEW_TOPIC_ONLY, FP.SITE_ID, ".
			"	".$DB->DateToCharFunction("FP.START_DATE", "FULL")." as START_DATE ".
			"FROM b_forum_subscribe FP ".
			"WHERE 1 = 1 ".
			"	".$strSqlSearch." ";

		$arSqlOrder = Array();
		foreach ($arOrder as $by=>$order)
		{
			$by = mb_strtoupper($by);
			$order = mb_strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "FORUM_ID") $arSqlOrder[] = " FP.FORUM_ID ".$order." ";
			elseif ($by == "USER_ID") $arSqlOrder[] = " FP.USER_ID ".$order." ";
			elseif ($by == "TOPIC_ID") $arSqlOrder[] = " FP.TOPIC_ID ".$order." ";
			elseif ($by == "NEW_TOPIC_ONLY") $arSqlOrder[] = " FP.NEW_TOPIC_ONLY ".$order." ";
			elseif ($by == "START_DATE") $arSqlOrder[] = " FP.START_DATE ".$order." ";
			else
			{
				$arSqlOrder[] = " FP.ID ".$order." ";
				$by = "ID";
			}
		}

		DelDuplicateSort($arSqlOrder);
		$strSqlOrder = (empty($arSqlOrder) ? "" : " ORDER BY ".implode(", ", $arSqlOrder));

		$strSql .= $strSqlOrder.(isset($arAddParams["nTopCount"]) ? ($arAddParams["nTopCount"] > 0 ? "\nLIMIT 0,".intval($arAddParams["nTopCount"]) : "") : '');

		if (isset($arAddParams["nTopCount"]) && $arAddParams["nTopCount"] <= 0 && is_set($arAddParams, "bDescPageNumbering"))
		{
			$db_res =  new CDBResult();
			$db_res->NavQuery($strSql, $iCnt, $arAddParams);
		}
		else
		{
			$db_res = $DB->Query($strSql);
		}

		return $db_res;
	}

	public static function GetListEx($arOrder = array("ID"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = array();
		$arSqlFrom = array();
		$arSqlGroup = array();
		$arSqlSelect = array();
		$arSqlOrder = array();
		$strSqlSelect = "";
		$strSqlSearch = "";
		$strSqlFrom = "";
		$strSqlGroup = "";
		$strSqlOrder = "";
		$arSqlSelectConst = array(
			"FS.ID" =>"FS.ID",
			"FS.USER_ID" => "FS.USER_ID",
			"FS.FORUM_ID" => "FS.FORUM_ID",
			"FS.TOPIC_ID" => "FS.TOPIC_ID",
			"TITLE_SEO" => CForumNew::Concat("-", array("FS.TOPIC_ID", "FT.TITLE_SEO")),
			"FS.LAST_SEND" => "FS.LAST_SEND",
			"FS.NEW_TOPIC_ONLY" => "FS.NEW_TOPIC_ONLY",
			"FS.SITE_ID" => "FS.SITE_ID",
			"START_DATE" => $DB->DateToCharFunction("FS.START_DATE", "FULL"),
			"U.EMAIL" => "U.EMAIL",
			"U.LOGIN" => "U.LOGIN",
			"U.NAME" => "U.NAME",
			"U.LAST_NAME" =>"U.LAST_NAME",
			"FT.TITLE" => "FT.TITLE",
			"FORUM_NAME" => "F.NAME"
		);
		$arFilter = (is_array($arFilter) ? $arFilter : array());

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = mb_strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ID":
				case "USER_ID":
				case "FORUM_ID":
				case "TOPIC_ID":
				case "LAST_SEND":
					if (intval($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FS.".$key." IS NULL OR FS.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FS.".$key." IS NULL OR NOT ":"")."(FS.".$key." ".$strOperation." ".intval($val)." )";
					break;
				case "TOPIC_ID_OR_NULL":
					$arSqlSearch[] = "(FS.TOPIC_ID = ".intval($val)." OR FS.TOPIC_ID = 0 OR FS.TOPIC_ID IS NULL)";
					break;
				case "NEW_TOPIC_ONLY":
					if ($val == '')
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FS.".$key." IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FS.".$key." IS NULL OR NOT ":"")."(FS.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "START_DATE":
					if($val == '')
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FS.".$key." IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FS.".$key." IS NULL OR NOT ":"")."(FS.".$key." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "SHORT").")";
					break;
				case "LAST_SEND_OR_NULL":
					$arSqlSearch[] = "(FS.LAST_SEND IS NULL OR FS.LAST_SEND = 0 OR FS.LAST_SEND < ".intval($val).")";
					break;
				case "ACTIVE":
					if ($val == '')
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(U.".$key." IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" U.".$key." IS NULL OR NOT ":"")."(U.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "FORUM":
				case "TOPIC":
					$key = ($key == "FORUM"	? "F.NAME" : "FT.TITLE");
					$arSqlSearch[] = GetFilterQuery($key, $val);
					break;
				case "SOCNET_GROUP_ID":
					if($val>0)
						$arSqlSearch[] = "FS.SOCNET_GROUP_ID=".intval($val);
					else
						$arSqlSearch[] = "FS.SOCNET_GROUP_ID IS NULL";
					break;
				case "PERMISSION":
					if($arFilter["SOCNET_GROUP_ID"]>0)
					{
						$arSqlSearch[] = "EXISTS(SELECT 'x'
							FROM b_sonet_features SF
								INNER JOIN b_sonet_features2perms SFP ON SFP.FEATURE_ID = SF.ID AND SFP.OPERATION_ID = 'view'
							WHERE SF.ENTITY_TYPE = 'G'
								AND SF.ENTITY_ID = FS.SOCNET_GROUP_ID
								AND SF.FEATURE = 'forum'
								AND SFP.ROLE = 'N' OR EXISTS(SELECT 'x' FROM b_sonet_user2group UG WHERE UG.USER_ID = FS.USER_ID AND ".$DB->IsNull("SFP.ROLE", "'K'")." >= UG.ROLE AND UG.GROUP_ID = FS.SOCNET_GROUP_ID)
						) ";
					}
					elseif ($val <> '')
					{
						$arSqlSearch[] = "(
							(FP.PERMISSION >= '".$DB->ForSql($val)."') OR
							(FP1.PERMISSION >= '".$DB->ForSql($val)."') OR
							((FP.ID IS NULL) AND (UG.GROUP_ID = 1)))";
						$arSqlSelect[] = "FU.SUBSC_GROUP_MESSAGE, FU.SUBSC_GET_MY_MESSAGE";
						$arSqlFrom[] = "
							LEFT JOIN b_forum_user FU ON (U.ID = FU.USER_ID)
							LEFT JOIN b_user_group UG ON (U.ID = UG.USER_ID)
							LEFT JOIN b_forum_perms FP ON (FP.FORUM_ID = FS.FORUM_ID AND FP.GROUP_ID=UG.GROUP_ID)
							LEFT JOIN b_forum_perms FP1 ON (FP1.FORUM_ID = FS.FORUM_ID AND FP1.GROUP_ID=2)";
						$arSqlGroup = array_values($arSqlSelectConst);
						$arSqlGroup[] = "FU.SUBSC_GROUP_MESSAGE, FU.SUBSC_GET_MY_MESSAGE";
					}
					break;
			}
		}

		if (count($arSqlSelect) > 0)
			$strSqlSelect .= ", ".implode(", ", $arSqlSelect);

		if (count($arSqlSearch) > 0)
			$strSqlSearch .= " AND (".implode(")
			AND
			(", $arSqlSearch).") ";

		if (count($arSqlFrom)>0)
			$strSqlFrom .= " ".implode(" ", $arSqlFrom)." ";

		if (count($arSqlGroup)>0)
			$strSqlGroup .= " GROUP BY ".implode(", ", $arSqlGroup)." ";

		foreach ($arOrder as $by=>$order)
		{
			$by = mb_strtoupper($by);
			$order = mb_strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "FORUM_ID") $arSqlOrder[] = " FS.FORUM_ID ".$order." ";
			elseif ($by == "USER_ID") $arSqlOrder[] = " FS.USER_ID ".$order." ";
			elseif ($by == "FORUM_NAME") $arSqlOrder[] = " F.NAME ".$order." ";
			elseif ($by == "TOPIC_ID") $arSqlOrder[] = " FS.TOPIC_ID ".$order." ";
			elseif ($by == "TITLE") $arSqlOrder[] = " FT.TITLE ".$order." ";
			elseif ($by == "START_DATE") $arSqlOrder[] = " FS.START_DATE ".$order." ";
			elseif ($by == "NEW_TOPIC_ONLY") $arSqlOrder[] = " FS.NEW_TOPIC_ONLY ".$order." ";
			elseif ($by == "LAST_SEND") $arSqlOrder[] = " FS.LAST_SEND ".$order." ";
			else
			{
				$arSqlOrder[] = " FS.ID ".$order." ";
				$by = "ID";
			}
		}
		DelDuplicateSort($arSqlOrder);
		if (count($arSqlOrder)>0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		$strSql = "
			SELECT FS.ID, FS.USER_ID, FS.FORUM_ID, FS.TOPIC_ID, FS.LAST_SEND, FS.NEW_TOPIC_ONLY, FS.SITE_ID,
				".CForumNew::Concat("-", array("FS.TOPIC_ID", "FT.TITLE_SEO"))." as TITLE_SEO,
				".$DB->DateToCharFunction("FS.START_DATE", "FULL")." as START_DATE,
				U.EMAIL, U.LOGIN, U.NAME, U.LAST_NAME, FT.TITLE, F.NAME AS FORUM_NAME".$strSqlSelect."
			FROM b_forum_subscribe FS
				INNER JOIN b_user U ON (FS.USER_ID = U.ID)
				LEFT JOIN b_forum_topic FT ON (FS.TOPIC_ID = FT.ID)
				LEFT JOIN b_forum F ON (FS.FORUM_ID = F.ID)
				".$strSqlFrom."
			WHERE 1 = 1
				".$strSqlSearch."
			".$strSqlGroup."
			".$strSqlOrder;

		$db_res = $DB->Query($strSql);
		return $db_res;
	}

	public static function GetByID($ID)
	{
		global $DB;
		$ID = intval($ID);

		$strSql =
			"SELECT FP.ID, FP.USER_ID, FP.FORUM_ID, FP.TOPIC_ID, FP.LAST_SEND, FP.NEW_TOPIC_ONLY, FP.SITE_ID, ".
			"	".$DB->DateToCharFunction("FP.START_DATE", "FULL")." as START_DATE ".
			"FROM b_forum_subscribe FP ".
			"WHERE FP.ID = ".$ID."";
		$db_res = $DB->Query($strSql);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}
}

/**********************************************************************/
/************** RANK **************************************************/
/**********************************************************************/
class CAllForumRank
{
	//---------------> User insert, update, delete
	public static function CanUserAddRank($arUserGroups)
	{
		if (in_array(1, $arUserGroups)) return True;
		return False;
	}

	public static function CanUserUpdateRank($ID, $arUserGroups)
	{
		if (in_array(1, $arUserGroups)) return True;
		return False;
	}

	public static function CanUserDeleteRank($ID, $arUserGroups)
	{
		if (in_array(1, $arUserGroups)) return True;
		return False;
	}

	public static function CheckFields($ACTION, &$arFields)
	{
		if (is_set($arFields, "LANG") || $ACTION=="ADD")
		{
			foreach ($arFields["LANG"] as $val)
			{
				if (!is_set($val, "LID") || empty($val["LID"])) return false;
				if (!is_set($val, "NAME") || empty($val["NAME"])) return false;
			}

			$db_lang = CLang::GetList();
			while ($arLang = $db_lang->Fetch())
			{
				$bFound = false;
				foreach ($arFields["LANG"] as $val):
					$bFound = ($bFound ? $bFound : ($val["LID"] == $arLang["LID"]));
				endforeach;
				if (!$bFound)
					return false;
			}
		}

		return True;
	}

	// Tekuwie statusy posetitelej srazu ne pereschityvayutsya. Tol'ko postepenno v processe raboty.
	public static function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);
		if ($ID <= 0)
			return False;

		if (!CForumRank::CheckFields("UPDATE", $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_forum_rank", $arFields);
		$strSql = "UPDATE b_forum_rank SET ".$strUpdate." WHERE ID = ".$ID;
		$DB->Query($strSql);

		if (is_set($arFields, "LANG"))
		{
			$DB->Query("DELETE FROM b_forum_rank_lang WHERE RANK_ID = ".$ID);

			foreach ($arFields["LANG"] as $i => $val)
			{
				$arInsert = $DB->PrepareInsert("b_forum_rank_lang", $arFields["LANG"][$i]);
				$strSql = "INSERT INTO b_forum_rank_lang(RANK_ID, ".$arInsert[0].") VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql);
			}
		}
		return $ID;
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);

		$arUsers = array();
		$db_res = CForumUser::GetList(array(), array("RANK_ID"=>$ID));
		while ($ar_res = $db_res->Fetch())
		{
			$arUsers[] = $ar_res["USER_ID"];
		}

		$DB->Query("DELETE FROM b_forum_rank_lang WHERE RANK_ID = ".$ID, True);
		$DB->Query("DELETE FROM b_forum_rank WHERE ID = ".$ID, True);

		foreach ($arUsers as $userId)
			CForumUser::SetStat($userId);

		return true;
	}

	public static function GetList($arOrder = array("MIN_NUM_POSTS"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = array();
		$arSqlOrder = array();
		$strSqlSearch = "";
		$strSqlOrder = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = mb_strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ID":
				case "MIN_NUM_POSTS":
					if (intval($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.".$key." IS NULL OR FR.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.".$key." IS NULL OR NOT ":"")."(FR.".$key." ".$strOperation." ".intval($val)." )";
					break;
			}
		}

		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).") ";

		foreach ($arOrder as $by=>$order)
		{
			$by = mb_strtoupper($by);
			$order = mb_strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " FR.ID ".$order." ";
			else
			{
				$arSqlOrder[] = " FR.MIN_NUM_POSTS ".$order." ";
				$by = "MIN_NUM_POSTS";
			}
		}
		DelDuplicateSort($arSqlOrder);
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		$strSql =
			"SELECT FR.ID, FR.MIN_NUM_POSTS
			FROM b_forum_rank FR
			WHERE 1 = 1
			".$strSqlSearch."
			".$strSqlOrder;

		$db_res = $DB->Query($strSql);
		return $db_res;
	}

	public static function GetListEx($arOrder = array("MIN_NUM_POSTS"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = array();
		$arSqlOrder = array();
		$strSqlSearch = "";
		$strSqlOrder = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = mb_strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ID":
				case "MIN_NUM_POSTS":
					if (intval($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.".$key." IS NULL OR FR.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.".$key." IS NULL OR NOT ":"")."(FR.".$key." ".$strOperation." ".intval($val)." )";
					break;
				case "LID":
					if ($val == '')
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FRL.LID IS NULL OR ".($DB->type == "MSSQL" ? "LEN" : "LENGTH")."(FRL.LID)<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FRL.LID IS NULL OR NOT ":"")."(FRL.LID ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
			}
		}
		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".imlode(" ) AND (", $arSqlSearch).") ";

		foreach ($arOrder as $by=>$order)
		{
			$by = mb_strtoupper($by);
			$order = mb_strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " FR.ID ".$order." ";
			elseif ($by == "LID") $arSqlOrder[] = " FRL.LID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " FRL.NAME ".$order." ";
			else
			{
				$arSqlOrder[] = " FR.MIN_NUM_POSTS ".$order." ";
				$by = "MIN_NUM_POSTS";
			}
		}
		DelDuplicateSort($arSqlOrder);
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		$strSql = "
			SELECT FR.ID, FR.MIN_NUM_POSTS, FRL.LID, FRL.NAME
			FROM b_forum_rank FR
				LEFT JOIN b_forum_rank_lang FRL ON FR.ID = FRL.RANK_ID
			WHERE 1 = 1
			".$strSqlSearch."
			".$strSqlOrder;

		$db_res = $DB->Query($strSql);
		return $db_res;
	}

	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		$strSql =
			"SELECT FR.ID, FR.MIN_NUM_POSTS ".
			"FROM b_forum_rank FR ".
			"WHERE FR.ID = ".$ID."";
		$db_res = $DB->Query($strSql);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	public static function GetByIDEx($ID, $strLang)
	{
		global $DB;

		$ID = intval($ID);
		$strSql =
			"SELECT FR.ID, FRL.LID, FRL.NAME, FR.MIN_NUM_POSTS ".
			"FROM b_forum_rank FR ".
			"	LEFT JOIN b_forum_rank_lang FRL ON (FR.ID = FRL.RANK_ID AND FRL.LID = '".$DB->ForSql($strLang)."') ".
			"WHERE FR.ID = ".$ID."";
		$db_res = $DB->Query($strSql);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	public static function GetLangByID($RANK_ID, $strLang)
	{
		global $DB;

		$RANK_ID = intval($RANK_ID);
		$strSql =
			"SELECT FRL.ID, FRL.RANK_ID, FRL.LID, FRL.NAME ".
			"FROM b_forum_rank_lang FRL ".
			"WHERE FRL.RANK_ID = ".$RANK_ID." ".
			"	AND FRL.LID = '".$DB->ForSql($strLang)."' ";
		$db_res = $DB->Query($strSql);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}
}

class CALLForumStat
{

	/**
	 * @param array $arFields
	 * @return bool
	 * @deprecated
	 * @see CALLForumStat::RegisterUSER()
	 */
	public static function RegisterUSER_OLD($arFields = array())
	{
		global $DB, $USER;
		$tmp = "";
		if ($_SESSION["FORUM"]["SHOW_NAME"] == "Y" && trim($USER->GetFormattedName(false)) <> '')
			$tmp = $USER->GetFormattedName(false);
		else
			$tmp = $USER->GetLogin();


		$session_id = \Bitrix\Main\Application::getInstance()->getKernelSession()->getId();
		$session_id = "'".$DB->ForSQL($session_id, 255)."'";
		$Fields = array(
			"FS.USER_ID" => intval($USER->GetID()),
			"FS.IP_ADDRESS" => "'".$DB->ForSql($_SERVER["REMOTE_ADDR"],15)."'",
			"FS.SHOW_NAME" => "'".$DB->ForSQL($tmp, 255)."'",
			"FS.LAST_VISIT" => $DB->GetNowFunction(),
			"FS.FORUM_ID" => intval($arFields["FORUM_ID"]),
			"FS.TOPIC_ID" => intval($arFields["TOPIC_ID"])
			);
		$FieldsForInsert = array(
			"USER_ID" => $Fields["FS.USER_ID"],
			"IP_ADDRESS" => $Fields["FS.IP_ADDRESS"],
			"SHOW_NAME" => $Fields["FS.SHOW_NAME"],
			"LAST_VISIT" => $Fields["FS.LAST_VISIT"],
			"FORUM_ID" => $Fields["FS.FORUM_ID"],
			"TOPIC_ID" => $Fields["FS.TOPIC_ID"],
			"PHPSESSID" => $session_id
			);


		if (intval($USER->GetID()) > 0)
		{
			$FieldsForUpdate = $Fields;
			$FieldsForUpdate["FU.LAST_VISIT"] = $DB->GetNowFunction();
			$rows = $DB->Update(
				"b_forum_user FU, b_forum_stat FS",
				$FieldsForUpdate,
				"WHERE (FU.USER_ID=".$Fields["FS.USER_ID"].") AND (FS.PHPSESSID=".$session_id.")",
				"File: ".__FILE__."<br>Line: ".__LINE__,
				false);

			if (intval($rows) < 2)
			{
				if (intval($rows)<=0)
				{
					$rows = $DB->Update(
						"b_forum_user",
						array("USER_ID" => $Fields["FS.USER_ID"]),
						"WHERE (USER_ID=".$Fields["FS.USER_ID"].")",
						"File: ".__FILE__."<br>Line: ".__LINE__,
						false);
					if (intval($rows) <= 0)
					{
						$ID = CForumUser::Add(array("USER_ID" => $Fields["FS.USER_ID"]));
					}

					$rows = $DB->Update(
						"b_forum_stat",
						array(
							"USER_ID" => $Fields["FS.USER_ID"],
							"IP_ADDRESS" => $Fields["FS.IP_ADDRESS"],
							"SHOW_NAME" => $Fields["FS.SHOW_NAME"],
							"LAST_VISIT" => $Fields["FS.LAST_VISIT"],
							"FORUM_ID" => $Fields["FS.FORUM_ID"],
							"TOPIC_ID" => $Fields["FS.TOPIC_ID"],
							),
						"WHERE (PHPSESSID=".$session_id.")",
						"File: ".__FILE__."<br>Line: ".__LINE__,
						false);
					if (intval($rows) <= 0)
					{
						$DB->Insert("b_forum_stat", $FieldsForInsert, "File: ".__FILE__."<br>Line: ".__LINE__);
					}
				}
			}
		}
		else
		{
			$rows = $DB->Update(
				"b_forum_stat",
				array(
					"USER_ID" => $Fields["FS.USER_ID"],
					"IP_ADDRESS" => $Fields["FS.IP_ADDRESS"],
					"SHOW_NAME" => $Fields["FS.SHOW_NAME"],
					"LAST_VISIT" => $Fields["FS.LAST_VISIT"],
					"FORUM_ID" => $Fields["FS.FORUM_ID"],
					"TOPIC_ID" => $Fields["FS.TOPIC_ID"],
					),
				"WHERE (PHPSESSID=".$session_id.")", "File: ".__FILE__."<br>Line: ".__LINE__);

			if (intval($rows)<=0)
			{
				$DB->Insert("b_forum_stat", $FieldsForInsert, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
		return true;
	}

	public static function RegisterUSER($arFields = array())
	{
		global $DB, $USER;
		$tmp = ($_SESSION["FORUM"]["SHOW_NAME"] == "Y" && trim($USER->GetFullName()) <> '' ?
			trim($USER->GetFullName()) : $USER->GetLogin());
		if (method_exists(\Bitrix\Main\Application::getInstance(), "getKernelSession"))
		{
			$session_id = \Bitrix\Main\Application::getInstance()->getKernelSession()->getId();
		}
		else
		{
			$session_id = bitrix_sessid();
		}

		$session_id = "'".$DB->ForSQL($session_id, 255)."'";

		$Fields = array(
			"USER_ID" => intval($USER->GetID()),
			"IP_ADDRESS" => "'".$DB->ForSql($_SERVER["REMOTE_ADDR"], 15)."'",
			"SHOW_NAME" => "'".$DB->ForSQL($tmp, 255)."'",
			"LAST_VISIT" => $DB->GetNowFunction(),
			"SITE_ID" => "'".$DB->ForSQL($arFields["SITE_ID"], 2)."'",
			"FORUM_ID" => intval($arFields["FORUM_ID"]),
			"TOPIC_ID" => intval($arFields["TOPIC_ID"]));
		$rows = $DB->Update("b_forum_stat", $Fields, "WHERE PHPSESSID=".$session_id."", "File: ".__FILE__."<br>Line: ".__LINE__);
		if (intval($rows)<=0)
		{
			$Fields = array(
				"USER_ID" => intval($USER->GetID()),
				"IP_ADDRESS" => "'".$DB->ForSql($_SERVER["REMOTE_ADDR"], 15)."'",
				"SHOW_NAME" => "'".$DB->ForSQL($tmp, 255)."'",
				"PHPSESSID" => $session_id,
				"LAST_VISIT" => $DB->GetNowFunction(),
				"SITE_ID" => "'".$DB->ForSQL($arFields["SITE_ID"], 2)."'",
				"FORUM_ID" => intval($arFields["FORUM_ID"]),
				"TOPIC_ID" => intval($arFields["TOPIC_ID"]));
			return $DB->Insert("b_forum_stat", $Fields, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return true;
	}

	public static function Add($arFields)
	{
		global $DB, $USER;
		$session_id = \Bitrix\Main\Application::getInstance()->getKernelSession()->getId();
		$Fields = array(
			"USER_ID" => $USER->GetID(),
			"IP_ADDRESS" => "'".$DB->ForSql($_SERVER["REMOTE_ADDR"],15)."'",
			"PHPSESSID" => "'".$DB->ForSQL($session_id, 255) . "'",
			"LAST_VISIT" => "'".$DB->GetNowFunction()."'",
			"FORUM_ID" => intval($arFields["FORUM_ID"]),
			"TOPIC_ID" => intval($arFields["TOPIC_ID"]));

		return $DB->Insert("b_forum_stat", $Fields, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	public static function GetListEx($arOrder = Array("ID"=>"ASC"), $arFilter = Array())
	{
		global $DB;
		$arSqlSearch = array();
		$arSqlSelect = array();
		$arSqlFrom = array();
		$arSqlGroup = array();
		$arSqlOrder = array();
		$arSql = array();
		$strSqlSearch = "";
		$strSqlSelect = "";
		$strSqlFrom = "";
		$strSqlGroup = "";
		$strSqlOrder = "";
		$strSql = "";

		$arSqlSelectConst = array(
			"FSTAT.USER_ID" => "FSTAT.USER_ID",
			"FSTAT.IPADDRES" => "FSTAT.IPADDRES",
			"FSTAT.PHPSESSID" => "FSTAT.PHPSESSID",
			"LAST_VISIT" => $DB->DateToCharFunction("FSTAT.LAST_VISIT", "FULL"),
			"FSTAT.FORUM_ID" => "FSTAT.FORUM_ID",
			"FSTAT.TOPIC_ID" => "FSTAT.TOPIC_ID"
		);
		$arSqlSelect = $arSqlSelectConst;
		$arFilter = (is_array($arFilter) ? $arFilter : array());

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = mb_strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "TOPIC_ID":
				case "FORUM_ID":
				case "USER_ID":
					if (intval($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FSTAT.".$key." IS NULL OR FSTAT.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FSTAT.".$key." IS NULL OR NOT ":"")."(FSTAT.".$key." ".$strOperation." ".intval($val)." )";
					break;
				case "LAST_VISIT":
					if($val == '')
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FSTAT.".$key." IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FSTAT.".$key." IS NULL OR NOT ":"")."(FSTAT.".$key." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
					break;
				case "HIDE_FROM_ONLINE":
					$arSqlFrom["FU"] = "LEFT JOIN b_forum_user FU ON FSTAT.USER_ID=FU.USER_ID";
					if ($val == '')
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FU.".$key." IS NULL OR ".($DB->type == "MSSQL" ? "LEN" : "LENGTH")."(FU.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FU.".$key." IS NULL OR NOT ":"")."(FU.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				break;
				case "COUNT_GUEST":
					$arSqlSelect = array(
						"FSTAT.USER_ID" => "FSTAT.USER_ID",
						"FSTAT.SHOW_NAME" => "FSTAT.SHOW_NAME",
						"COUNT_USER" => "COUNT(FSTAT.PHPSESSID) AS COUNT_USER",
					);
					$arSqlGroup["FSTAT.USER_ID"] = "FSTAT.USER_ID";
					$arSqlGroup["FSTAT.SHOW_NAME"] = "FSTAT.SHOW_NAME";
					break;
			}
		}
		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).") ";
		if (count($arSqlSelect) > 0)
			$strSqlSelect = implode(", ", $arSqlSelect);
		if (count($arSqlFrom) > 0)
			$strSqlFrom = implode("	", $arSqlFrom);
		if (count($arSqlGroup) > 0)
			$strSqlGroup = " GROUP BY ".implode(", ", $arSqlGroup);


		foreach ($arOrder as $by=>$order)
		{
			$by = mb_strtoupper($by);
			$order = mb_strtoupper($order);
			$order = $order!="ASC" ? $order = "DESC" : "ASC";

			if ($by == "USER_ID") $arSqlOrder[] = " FSTAT.USER_ID ".$order." ";
		}

		DelDuplicateSort($arSqlOrder);
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		$strSql = " SELECT ".$strSqlSelect."
			FROM b_forum_stat FSTAT
			".$strSqlFrom."
			WHERE 1=1
			".$strSqlSearch."
			".$strSqlGroup."
			".$strSqlOrder;

		$db_res = $DB->Query($strSql);
		return $db_res;
	}

	public static function CleanUp()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$connection->query(
			"DELETE FROM b_forum_stat WHERE LAST_VISIT < " . $helper->addDaysToDateTime(-1)
		);
		return "CForumStat::CleanUp();";
	}
}
