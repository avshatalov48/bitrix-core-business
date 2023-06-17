<?
IncludeModuleLangFile(__FILE__);
/**********************************************************************/
/************** FORUM TOPIC *******************************************/
/**********************************************************************/
class CAllForumTopic
{
	public static function CanUserViewTopic($TID, $arUserGroups, $iUserID = 0, $ExternalPermission = false)
	{
		$TID = intval($TID);
		$arTopic = CForumTopic::GetByID($TID);
		if ($arTopic)
		{
			if ($ExternalPermission === false && CForumUser::IsAdmin($arUserGroups)):
				return true;
			endif;
			$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($arTopic["FORUM_ID"], $arUserGroups) : $ExternalPermission);
			if ($strPerms >= "Y")
				return true;
			if ($strPerms < "E" || ($strPerms < "Q" && $arTopic["APPROVED"] != "Y"))
				return false;
			$arForum = CForumNew::GetByID($arTopic["FORUM_ID"]);
			return ($arForum["ACTIVE"] == "Y" ? true : false);
		}
		return false;
	}

	public static function CanUserAddTopic($FID, $arUserGroups, $iUserID = 0, $arForum = false, $ExternalPermission = false)
	{
		if (!$arForum || (!is_array($arForum)) || (intval($arForum["ID"]) != intval($FID)))
			$arForum = CForumNew::GetByID($FID);
		if (is_array($arForum) && $arForum["ID"] = $FID)
		{
			if ($ExternalPermission === false && CForumUser::IsAdmin($arUserGroups)):
				return true;
			endif;
			if (!CForumUser::IsLocked($iUserID)):
				$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($arForum["ID"], $arUserGroups) : $ExternalPermission);
			else:
				$strPerms = CForumNew::GetPermissionUserDefault($arForum["ID"]);
			endif;
			if ($strPerms >= "Y")
				return true;
			if ($strPerms < "M")
				return false;
			return ($arForum["ACTIVE"] == "Y" ? true : false);
		}
		return false;
	}

	public static function CanUserUpdateTopic($TID, $arUserGroups, $iUserID = 0, $ExternalPermission = false)
	{
		$TID = intval($TID);
		$iUserID = intval($iUserID);
		$arTopic = CForumTopic::GetByID($TID);
		if ($arTopic)
		{
			if ($ExternalPermission === false && CForumUser::IsAdmin($arUserGroups)):
				return true;
			endif;
			if (!CForumUser::IsLocked($iUserID)):
				$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($arTopic["FORUM_ID"], $arUserGroups) : $ExternalPermission);
			else:
				$strPerms = CForumNew::GetPermissionUserDefault($arTopic["FORUM_ID"]);
			endif;
			if ($strPerms >= "Y")
				return true;
			elseif ($strPerms < "M" || ($strPerms < "Q" && ($arTopic["APPROVED"] != "Y" || $arTopic["STATE"] != "Y")))
				return false;
			$arForum = CForumNew::GetByID($arTopic["FORUM_ID"]);
			if ($arForum["ACTIVE"] != "Y")
				return false;
			elseif ($strPerms >= "U")
				return true;
			$db_res = CForumMessage::GetList(array("ID"=>"ASC"), array("TOPIC_ID"=>$TID, "FORUM_ID"=>$arTopic["FORUM_ID"]), False, 2);
			$iCnt = 0; $iOwner = 0;
			if (!($db_res && $res = $db_res->Fetch()))
				return false;
			else
			{
				$iCnt++; $iOwner = intval($res["AUTHOR_ID"]);
				if ($res = $db_res->Fetch())
					return false;
			}
			if ($iOwner <= 0 || $iUserID <= 0 || $iOwner != $iUserID)
				return false;
			return true;
		}
		return false;
	}

	public static function CanUserDeleteTopic($TID, $arUserGroups, $iUserID = 0, $ExternalPermission = false)
	{
		$TID = intval($TID);
		$arTopic = CForumTopic::GetByID($TID);
		if ($arTopic)
		{
			if ($ExternalPermission === false && CForumUser::IsAdmin($arUserGroups)):
				return true;
			endif;
			if (!CForumUser::IsLocked($iUserID)):
				$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($arTopic["FORUM_ID"], $arUserGroups) : $ExternalPermission);
			else:
				$strPerms = CForumNew::GetPermissionUserDefault($arTopic["FORUM_ID"]);
			endif;
			if ($strPerms >= "Y")
				return true;
			elseif ($strPerms < "U")
				return false;
			$arForum = CForumNew::GetByID($arTopic["FORUM_ID"]);
			return ($arForum["ACTIVE"] == "Y" ? true : false);
		}
		return false;
	}

	public static function CanUserDeleteTopicMessage($TID, $arUserGroups, $iUserID = 0, $ExternalPermission = false)
	{
		$TID = intval($TID);
		$arTopic = CForumTopic::GetByID($TID);
		if ($arTopic)
		{
			if ($ExternalPermission === false && CForumUser::IsAdmin($arUserGroups)):
				return true;
			endif;
			if (!CForumUser::IsLocked($iUserID)):
				$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($arTopic["FORUM_ID"], $arUserGroups) : $ExternalPermission);
			else:
				$strPerms = CForumNew::GetPermissionUserDefault($arTopic["FORUM_ID"]);
			endif;
			if ($strPerms >= "Y")
				return true;
			elseif ($strPerms < "U")
				return false;
			$arForum = CForumNew::GetByID($arTopic["FORUM_ID"]);
			return ($arForum["ACTIVE"] == "Y" ? true : false);
		}
		return false;
	}

	public static function CheckFields($ACTION, &$arFields)
	{
		// Fatal Errors
		if (is_set($arFields, "TITLE") || $ACTION=="ADD")
		{
			$arFields["TITLE"] = trim($arFields["TITLE"]);
			if ($arFields["TITLE"] == '')
				return false;
		}
		if (is_set($arFields, "TITLE_SEO") || $ACTION=="ADD")
		{
			$arFields["TITLE_SEO"] = trim($arFields["TITLE_SEO"], " -");
			if ($arFields["TITLE_SEO"] == '' && $arFields["TITLE"] <> '')
				$arFields["TITLE_SEO"] = CUtil::translit($arFields["TITLE"], LANGUAGE_ID, array("max_len"=>255, "safe_chars"=>".", "replace_space" => '-'));
			if ($arFields["TITLE_SEO"] == '')
				$arFields["TITLE_SEO"] = false;
		}
		if (is_set($arFields, "USER_START_NAME") || $ACTION=="ADD")
		{
			$arFields["USER_START_NAME"] = trim($arFields["USER_START_NAME"]);
			if ($arFields["USER_START_NAME"] == '')
				return false;
		}

		if (is_set($arFields, "FORUM_ID") || $ACTION=="ADD")
		{
			$arFields["FORUM_ID"] = intval($arFields["FORUM_ID"]);
			if ($arFields["FORUM_ID"] <= 0)
				return false;
		}
		if (is_set($arFields, "LAST_POSTER_NAME") || $ACTION=="ADD")
		{
			$arFields["LAST_POSTER_NAME"] = trim($arFields["LAST_POSTER_NAME"]);
			if ($arFields["LAST_POSTER_NAME"] == '' && $arFields["APPROVED"] !== "N" && $arFields["STATE"] !== "L")
				return false;
		}
		if (is_set($arFields, "ABS_LAST_POSTER_NAME") || $ACTION=="ADD")
		{
			$arFields["ABS_LAST_POSTER_NAME"] = trim($arFields["ABS_LAST_POSTER_NAME"]);
			if ($arFields["ABS_LAST_POSTER_NAME"] == '' && $ACTION == "ADD" && !empty($arFields["LAST_POSTER_NAME"]))
				$arFields["ABS_LAST_POSTER_NAME"] = $arFields["LAST_POSTER_NAME"];
			elseif ($arFields["ABS_LAST_POSTER_NAME"] == '' && $arFields["APPROVED"] !== "N" && $arFields["STATE"] !== "L")
				return false;
		}

		// Check Data
		if (is_set($arFields, "USER_START_ID") || $ACTION=="ADD")
			$arFields["USER_START_ID"] = (intval($arFields["USER_START_ID"]) > 0 ? intval($arFields["USER_START_ID"]) : false);
		if (is_set($arFields, "LAST_POSTER_ID") || $ACTION=="ADD")
			$arFields["LAST_POSTER_ID"] = (intval($arFields["LAST_POSTER_ID"]) > 0 ? intval($arFields["LAST_POSTER_ID"]) : false);
		if (is_set($arFields, "LAST_MESSAGE_ID") || $ACTION=="ADD")
			$arFields["LAST_MESSAGE_ID"] = (intval($arFields["LAST_MESSAGE_ID"]) > 0 ? intval($arFields["LAST_MESSAGE_ID"]) : false);
		if (is_set($arFields, "ICON") || $ACTION=="ADD")
			$arFields["ICON"] = trim($arFields["ICON"]);
		if (is_set($arFields, "STATE") || $ACTION=="ADD")
			$arFields["STATE"] = (in_array($arFields["STATE"], array("Y", "N", "L")) ?  $arFields["STATE"] : "Y");
		if (is_set($arFields, "APPROVED") || $ACTION=="ADD")
			$arFields["APPROVED"] = ($arFields["APPROVED"] == "N" ? "N" : "Y");
		if (is_set($arFields, "SORT") || $ACTION=="ADD")
			$arFields["SORT"] = (intval($arFields["SORT"]) > 0 ? intval($arFields["SORT"]) : 150);
		if (is_set($arFields, "VIEWS") || $ACTION=="ADD")
			$arFields["VIEWS"] = (intval($arFields["VIEWS"]) > 0 ? intval($arFields["VIEWS"]) : 0);
		if (is_set($arFields, "POSTS") || $ACTION=="ADD")
			$arFields["POSTS"] = (intval($arFields["POSTS"]) > 0 ? intval($arFields["POSTS"]) : 0);
		if (is_set($arFields, "TOPIC_ID"))
			$arFields["TOPIC_ID"]=intval($arFields["TOPIC_ID"]);
		if (is_set($arFields, "SOCNET_GROUP_ID") || $ACTION=="ADD")
			$arFields["SOCNET_GROUP_ID"] = (intval($arFields["SOCNET_GROUP_ID"]) > 0 ? intval($arFields["SOCNET_GROUP_ID"]) : false);
		if (is_set($arFields, "OWNER_ID") || $ACTION=="ADD")
			$arFields["OWNER_ID"] = (intval($arFields["OWNER_ID"]) > 0 ? intval($arFields["OWNER_ID"]) : false);
		return True;
	}

	public static function Add($arFields)
	{
		$entity = \Bitrix\Forum\TopicTable::getEntity();
		$data = [];
		foreach ($arFields as $k => $v)
		{
			if ($entity->hasField($k))
			{
				$data[$k] = $v;
			}
		}
		$result = \Bitrix\Forum\TopicTable::add($data);
		if ($result->isSuccess())
		{
			$id = $result->getPrimary();
			return $id["ID"];
		}
		return false;
	}

	public static function Update($ID, $arFields, $skip_counts = False)
	{
		$topic = \Bitrix\Forum\Topic::getById($ID);
		$entity = \Bitrix\Forum\TopicTable::getEntity();
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
				else if (preg_match("/{$k}\s*(\+|\-)\s*(\d+)/", $v, $matches))
				{
					$data[$k] = new \Bitrix\Main\DB\SqlExpression("?# $matches[1] $matches[2]", $k);
				}
			}
		}
		$fieldForEdit = array_intersect_key($data, array_flip(["TITLE",
			"TITLE_SEO",
			"TAGS",
			"DESCRIPTION",
			"ICON",
			"USER_START_NAME"])
		);
		if (!empty($fieldForEdit))
		{
			$topic->edit($data);
			$data = array_diff_key($data, $data);
		}
		if (array_key_exists("FORUM_ID", $data))
		{
			$topic->moveToForum($data["FORUM_ID"]);
			unset($GLOBALS["FORUM_CACHE"]["FORUM"]);
			unset($data["FORUM_ID"]);
		}
		if (!empty($data))
		{
			\Bitrix\Forum\Topic::update($topic->getId(), $data);
		}

		unset($GLOBALS["FORUM_CACHE"]["TOPIC"][$ID]);
		unset($GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID]);

		return $ID;
	}

	public static function MoveTopic2Forum($TID, $FID, $leaveLink = "N")
	{
		global $DB;
		$FID = intval($FID);
		$arForum = CForumNew::GetByID($FID);
		$arTopics = (is_array($TID) ? $TID : (intval($TID) > 0 ? array($TID) : array()));
		$leaveLink = (mb_strtoupper($leaveLink) == "Y" ? "Y" : "N");
		$arMsg = array();
		$arForums = array();

		if (empty($arForum))
		{
			$arMsg[] = array(
				"id" => "FORUM_NOT_EXIST",
				"text" =>  GetMessage("F_ERR_FORUM_NOT_EXIST", array("#FORUM_ID#" => $FID)));
		}
		if (empty($arTopics))
		{
			$arMsg[] = array(
				"id" => "TOPIC_EMPTY",
				"text" =>  GetMessage("F_ERR_EMPTY_TO_MOVE"));
		}

		if (!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		$arTopicsCopy = $arTopics;
		$arTopics = array();
		foreach ($arTopicsCopy as $res)
		{
			$arTopics[intval($res)] = array("ID" => intval($res));
		}

		$db_res = CForumTopic::GetList(array(), array("@ID" => implode(", ", array_keys($arTopics))));
		if ($db_res && ($res = $db_res->Fetch()))
		{
			do
			{
				if (intval($res["FORUM_ID"]) == $FID)
				{
					$arMsg[] = array(
						"id" => "FORUM_ID_IDENTICAL",
						"text" => GetMessage("F_ERR_THIS_TOPIC_IS_NOT_MOVE",
							array("#TITLE#" => $res["TITLE"], "#ID#" => $res["ID"])));
					continue;
				}

//				$DB->StartTransaction();

				if ($leaveLink != "N")
				{
					CForumTopic::Add(
						array(
							"TITLE" => $res["TITLE"],
							"DESCRIPTION" => $res["DESCRIPTION"],
							"STATE" => "L",
							"USER_START_NAME" => $res["USER_START_NAME"],
							"START_DATE" => $res["START_DATE"],
							"ICON" => $res["ICON"],
							"POSTS" => "0",
							"VIEWS" => "0",
							"FORUM_ID" => $res["FORUM_ID"],
							"TOPIC_ID" => $res["ID"],
							"APPROVED" => $res["APPROVED"],
							"SORT" => $res["SORT"],
							"LAST_POSTER_NAME" => $res["LAST_POSTER_NAME"],
							"LAST_POST_DATE" => $res["LAST_POST_DATE"],
							"HTML" => $res["HTML"],
							"USER_START_ID" => $res["USER_START_ID"],
							"SOCNET_GROUP_ID" => $res["SOCNET_GROUP_ID"],
							"OWNER_ID" => $res["OWNER_ID"]));
				}

				CForumTopic::Update($res["ID"], array("FORUM_ID" => $FID), true);
				// move message
				$strSql = "UPDATE b_forum_message SET FORUM_ID=".$FID.", POST_MESSAGE_HTML='' WHERE TOPIC_ID=".$res["ID"];
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				// move subscribe
				$strSql = "UPDATE b_forum_subscribe SET FORUM_ID=".intval($FID)." WHERE TOPIC_ID=".$res["ID"];
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

				$arForums[$res["FORUM_ID"]] = $res["FORUM_ID"];
				unset($GLOBALS["FORUM_CACHE"]["TOPIC"][$res["ID"]]);
				unset($GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$res["ID"]]);
				$arTopics[intval($res["ID"])] = $res;
//				$DB->Commit();

				CForumCacheManager::ClearTag("F", $res["ID"]);

				$res_log["DESCRIPTION"] = str_replace(array("#TOPIC_TITLE#", "#TOPIC_ID#", "#FORUM_TITLE#", "#FORUM_ID#"),
					array($res["TITLE"], $res["ID"], $arForum["NAME"], $arForum["ID"]),
					($leaveLink != "N" ? GetMessage("F_LOGS_MOVE_TOPIC_WITH_LINK") : GetMessage("F_LOGS_MOVE_TOPIC")));
				$res_log["FORUM_ID"] = $arForum["ID"];
				$res_log["TOPIC_ID"] = $res["ID"];
				$res_log["TITLE"] = $res["TITLE"];
				$res_log["FORUM_TITLE"] = $arForum["NAME"];
				CForumEventLog::Log("topic", "move", $res["ID"], serialize($res_log));
			} while ($res = $db_res->Fetch());
		}
/***************** Cleaning cache **********************************/
		unset($GLOBALS["FORUM_CACHE"]["FORUM"][$FID]);
		if(CACHED_b_forum !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum");
/***************** Cleaning cache/**********************************/
		CForumNew::SetStat($FID);
		foreach ($arForums as $key)
			CForumNew::SetStat($key);
		if (!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
		}
		else
		{

			CForumCacheManager::ClearTag("F", $FID);
			if ($leaveLink != "Y")
			{
				foreach($arTopics as $key => $res)
					CForumCacheManager::ClearTag("F", $res["FORUM_ID"]);
			}
		}
		return true;
	}

	public static function Delete($ID)
	{
		$arTopic = [];
		if ($topic = \Bitrix\Forum\Topic::getById($ID))
		{
			$arTopic = $topic->getData();
			$topic->remove();
		}
		unset($GLOBALS["FORUM_CACHE"]["TOPIC"][$ID]);
		unset($GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID]);

/***************** Event onAfterTopicDelete ************************/
		foreach(GetModuleEvents("forum", "onAfterTopicDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$ID, $arTopic));
/***************** /Event ******************************************/
		return true;
	}

	public static function GetByID($ID, $arAddParams = array())
	{
		global $DB;

		if (mb_strlen($ID) < 1) return False;

		$NoFilter = (isset($arAddParams["NoFilter"]) && $arAddParams["NoFilter"] == true) || COption::GetOptionString("forum", "FILTER", "Y") != "Y" ? true : false;

		if ($NoFilter && isset($GLOBALS["FORUM_CACHE"]["TOPIC"][$ID]) && is_array($GLOBALS["FORUM_CACHE"]["TOPIC"][$ID]) && is_set($GLOBALS["FORUM_CACHE"]["TOPIC"][$ID], "ID"))
		{
			return $GLOBALS["FORUM_CACHE"]["TOPIC"][$ID];
		}
		elseif (!$NoFilter && isset($GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID]) && is_array($GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID]) && is_set($GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID], "ID"))
		{
			return $GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID];
		}
		else
		{
			$strSql =
				"SELECT FT.*,
					FT.TITLE_SEO as TITLE_SEO_REAL,
					".CForumNew::Concat("-", array("FT.ID", "FT.TITLE_SEO"))." as TITLE_SEO,
					".$DB->DateToCharFunction("FT.START_DATE", "FULL")." as START_DATE,
					".$DB->DateToCharFunction("FT.LAST_POST_DATE", "FULL")." as LAST_POST_DATE
				FROM b_forum_topic FT ";

			if (intval($ID) > 0 || $ID === 0)
				$strSql .= "WHERE FT.ID = ".intval($ID);
			else
				$strSql .= "WHERE FT.XML_ID = '".$DB->ForSql($ID)."'";

			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($db_res && $res = $db_res->Fetch())
			{
				$GLOBALS["FORUM_CACHE"]["TOPIC"][$ID] = $res;
				if (COption::GetOptionString("forum", "FILTER", "Y") == "Y")
				{
					$db_res_filter = new CDBResult;
					$db_res_filter->InitFromArray(array($res));
					$db_res_filter = new _CTopicDBResult($db_res_filter);
					if ($res_filter = $db_res_filter->Fetch())
						$GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID] = $res_filter;
				}
				if (!$NoFilter)
					$res = $res_filter;
				return $res;
			}
		}
		return False;
	}

	public static function GetByIDEx($ID, $arAddParams = array())
	{
		global $DB;
		$ID = intval($ID);
		if ($ID <= 0):
			return false;
		endif;

		$arAddParams = (is_array($arAddParams) ? $arAddParams : array($arAddParams));
		$arAddParams["GET_FORUM_INFO"] = ($arAddParams["GET_FORUM_INFO"] == "Y" ? "Y" : "N");
		$arSQL = array("select" => array(), "join" => array());
		if (!empty($arAddParams["sNameTemplate"]))
		{
			$arSQL = array_merge_recursive(
				CForumUser::GetFormattedNameFieldsForSelect(array_merge(
					$arAddParams, array(
					"sUserTablePrefix" => "U_START.",
					"sForumUserTablePrefix" => "FU_START.",
					"sFieldName" => "USER_START_NAME_FRMT",
					"sUserIDFieldName" => "FT.USER_START_ID"))),
				CForumUser::GetFormattedNameFieldsForSelect(array_merge(
					$arAddParams, array(
					"sUserTablePrefix" => "U_LAST.",
					"sForumUserTablePrefix" => "FU_LAST.",
					"sFieldName" => "LAST_POSTER_NAME_FRMT",
					"sUserIDFieldName" => "FT.LAST_POSTER_ID"))),
				CForumUser::GetFormattedNameFieldsForSelect(array_merge(
					$arAddParams, array(
					"sUserTablePrefix" => "U_ABS_LAST.",
					"sForumUserTablePrefix" => "FU_ABS_LAST.",
					"sFieldName" => "ABS_LAST_POSTER_NAME_FRMT",
					"sUserIDFieldName" => "FT.ABS_LAST_POSTER_ID"))));
		}
		if ($arAddParams["GET_FORUM_INFO"] == "Y")
		{
			$arSQL["select"][] = CForumNew::GetSelectFields(array("sPrefix" => "F_", "sReturnResult" => "string"));
			$arSQL["join"][] =  "INNER JOIN b_forum F ON (FT.FORUM_ID = F.ID)";
		}
		$arSQL["select"] = (!empty($arSQL["select"]) ? ",\n\t".implode(",\n\t", $arSQL["select"]) : "");
		$arSQL["join"] = (!empty($arSQL["join"]) ? "\n\t".implode("\n", $arSQL["join"]) : "");

		$strSql =
			"SELECT FT.*,\n".
			"	FT.TITLE_SEO as TITLE_SEO_REAL, ".CForumNew::Concat("-", array("FT.ID", "FT.TITLE_SEO"))." as TITLE_SEO, \n".
			"	".$DB->DateToCharFunction("FT.START_DATE", "FULL")." as START_DATE, \n".
			"	".$DB->DateToCharFunction("FT.LAST_POST_DATE", "FULL")." as LAST_POST_DATE, \n".
			"	'' as IMAGE, '' as IMAGE_DESCR".$arSQL["select"]."\n".
			"FROM b_forum_topic FT \n".
			"	".$arSQL["join"]."\n".
			"WHERE FT.ID = ".$ID;
		$db_res = new _CTopicDBResult($DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__));

		if ($res = $db_res->Fetch())
		{
			if (is_array($res))
			{
				// Cache topic data for hits
				if ($arAddParams["GET_FORUM_INFO"] == "Y")
				{
					$res["TOPIC_INFO"] = array();
					$res["FORUM_INFO"] = array();
					foreach ($res as $key => $val)
					{
						if (mb_substr($key, 0, 2) == "F_")
							$res["FORUM_INFO"][mb_substr($key, 2)] = $val;
						else
							$res["TOPIC_INFO"][$key] = $val;
					}
					if (!empty($res["TOPIC_INFO"]))
					{
						$GLOBALS["FORUM_CACHE"]["TOPIC"][intval($res["TOPIC_INFO"]["ID"])] = $res["TOPIC_INFO"];
						if (COption::GetOptionString("forum", "FILTER", "Y") == "Y")
						{
							$db_res_filter = new CDBResult;
							$db_res_filter->InitFromArray(array($res["TOPIC_INFO"]));
							$db_res_filter = new _CTopicDBResult($db_res_filter);
							if ($res_filter = $db_res_filter->Fetch())
								$GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][intval($res["TOPIC_INFO"]["ID"])] = $res_filter;
						}
					}
					if (!empty($res["FORUM_INFO"]))
					{
						$GLOBALS["FORUM_CACHE"]["FORUM"][intval($res["FORUM_INFO"]["ID"])] = $res["FORUM_INFO"];
					}
				}
			}
			return $res;
		}
		return false;
	}

	public static function GetNeighboringTopics($TID, $arUserGroups) // out-of-date function
	{
		$TID = intval($TID);
		$arTopic = CForumTopic::GetByID($TID);
		if (!$arTopic) return False;

		//-- PREV_TOPIC
		$arFilter = array(
			"FORUM_ID" => $arTopic["FORUM_ID"],
			"<LAST_POST_DATE" => $arTopic["LAST_POST_DATE"]
			);
		if (CForumNew::GetUserPermission($arTopic["FORUM_ID"], $arUserGroups)<"Q")
			$arFilter["APPROVED"] = "Y";

		$db_res = CForumTopic::GetList(array("LAST_POST_DATE"=>"DESC"), $arFilter, false, 1);
		$PREV_TOPIC = 0;
		if ($ar_res = $db_res->Fetch()) $PREV_TOPIC = $ar_res["ID"];

		//-- NEXT_TOPIC
		$arFilter = array(
			"FORUM_ID" => $arTopic["FORUM_ID"],
			">LAST_POST_DATE" => $arTopic["LAST_POST_DATE"]
			);
		if (CForumNew::GetUserPermission($arTopic["FORUM_ID"], $arUserGroups)<"Q")
			$arFilter["APPROVED"] = "Y";

		$db_res = CForumTopic::GetList(array("LAST_POST_DATE"=>"ASC"), $arFilter, false, 1);
		$NEXT_TOPIC = 0;
		if ($ar_res = $db_res->Fetch()) $NEXT_TOPIC = $ar_res["ID"];

		return array($PREV_TOPIC, $NEXT_TOPIC);
	}

	public static function GetSelectFields($arAddParams = array(), $fields = array())
	{
		global $DB;
		$arAddParams = (is_array($arAddParams) ? $arAddParams : array());
		$arAddParams["sPrefix"] = $DB->ForSql(empty($arAddParams["sPrefix"]) ? "FT." : $arAddParams["sPrefix"]);
		$arAddParams["sTablePrefix"] = $DB->ForSql(empty($arAddParams["sTablePrefix"]) ? "FT." : $arAddParams["sTablePrefix"]);
		$arAddParams["sReturnResult"] = ($arAddParams["sReturnResult"] == "string" ? "string" : "array");
		$fields = (is_array($fields) ? $fields : array());
		$fields = array_merge(array(
			"ID" => "ID",
			"TITLE" => "TITLE",
			"TITLE_SEO_REAL" => $arAddParams["sTablePrefix"]."TITLE_SEO",
			"TITLE_SEO" => CForumNew::Concat("-", array($arAddParams["sTablePrefix"]."ID", $arAddParams["sTablePrefix"]."TITLE_SEO")),
			"TAGS" => "TAGS",
			"DESCRIPTION" => "DESCRIPTION",
			"VIEWS" => "VIEWS",
			"LAST_POSTER_ID" => "LAST_POSTER_ID",
			"START_DATE" => $DB->DateToCharFunction($arAddParams["sTablePrefix"]."START_DATE", "FULL"),
			"USER_START_NAME" => "USER_START_NAME",
			"USER_START_ID" => "USER_START_ID",
			"POSTS" => "POSTS",
			"LAST_POSTER_NAME" => "LAST_POSTER_NAME",
			"LAST_POST_DATE" => $DB->DateToCharFunction($arAddParams["sTablePrefix"]."LAST_POST_DATE", "FULL"),
			"LAST_MESSAGE_ID" => "LAST_MESSAGE_ID",
			"APPROVED" => "APPROVED",
			"STATE" => "STATE",
			"FORUM_ID" => "FORUM_ID",
			"TOPIC_ID" => "TOPIC_ID",
			"ICON" => "ICON",
			"SORT" => "SORT",
			"SOCNET_GROUP_ID" => "SOCNET_GROUP_ID",
			"OWNER_ID" => "OWNER_ID",
			"XML_ID" => "XML_ID"), $fields);
		$res = array();
		foreach($fields as $key => $val)
		{
			if ($key == $val)
			{
				$res[$arAddParams["sPrefix"].$key] = $arAddParams["sTablePrefix"].$val;
			}
			else
			{
				$res[($arAddParams["sPrefix"] == $arAddParams["sTablePrefix"] ? "" : $arAddParams["sPrefix"]).$key] = $val;
			}
		}
		if ($arAddParams["sReturnResult"] == "string")
		{
			$arRes = array();
			foreach ($res as $key => $val)
			{
				$arRes[] = $val.($key != $val ? " AS ".$key : "");
			}
			$res = implode(", ", $arRes);
		}
		return $res;
	}

	public static function SetReadLabels($ID, $arUserGroups) // out-of-date function
	{
		$ID = intval($ID);
		$arTopic = CForumTopic::GetByID($ID);
		if ($arTopic)
		{
			$FID = intval($arTopic["FORUM_ID"]);
			if (is_null($_SESSION["read_forum_".$FID]) || $_SESSION["read_forum_".$FID] == '')
			{
				$_SESSION["read_forum_".$FID] = "0";
			}

			$_SESSION["first_read_forum_".$FID] = intval($_SESSION["first_read_forum_".$FID]);

			$arFilter = array(
				"FORUM_ID" => $FID,
				"TOPIC_ID" => $ID
				);
			if (intval($_SESSION["first_read_forum_".$FID])>0)
				$arFilter[">ID"] = intval($_SESSION["first_read_forum_".$FID]);
			if ($_SESSION["read_forum_".$FID]!="0")
				$arFilter["!@ID"] = $_SESSION["read_forum_".$FID];
			if (CForumNew::GetUserPermission($FID, $arUserGroups)<"Q")
				$arFilter["APPROVED"] = "Y";
			$db_res = CForumMessage::GetList(array(), $arFilter);
			if ($db_res)
			{
				while ($ar_res = $db_res->Fetch())
				{
					$_SESSION["read_forum_".$FID] .= ",".intval($ar_res["ID"]);
				}
			}
			CForumTopic::Update($ID, array("=VIEWS"=>"VIEWS+1"));
		}
	}

	public static function SetReadLabelsNew($ID, $updateForum = false, $LastVisit = false, $arAddParams = array())
	{
		global $USER;
		$forumUser = \Bitrix\Forum\User::getById($USER->getId());
		if ($updateForum === true)
		{
			$forumUser->readTopicsOnForum($ID);
		}
		else
		{
			$forumUser->readTopic($ID);
		}
		return false;
	}

	public static function CleanUp($period = 168)
	{
		return "CForumTopic::CleanUp();";
	}


	//---------------> Topic utils
	public static function SetStat($ID = 0, $arParams = array())
	{
		global $DB;
		$ID = intval($ID);
		if ($ID <= 0):
			return false;
		endif;
		$arParams = (is_array($arParams) ? $arParams : array());
		$arMessage = (is_array($arParams["MESSAGE"]) ? $arParams["MESSAGE"] : array());
		if ($arMessage["TOPIC_ID"] != $ID)
			$arMessage = array();
		$arFields = array();

		if (!empty($arMessage))
		{
			$arFields = array(
				"ABS_LAST_POSTER_ID" => ((intval($arMessage["AUTHOR_ID"])>0) ? $arMessage["AUTHOR_ID"] : false),
				"ABS_LAST_POSTER_NAME" => $arMessage["AUTHOR_NAME"],
				"ABS_LAST_POST_DATE" => $arMessage["POST_DATE"],
				"ABS_LAST_MESSAGE_ID" => $arMessage["ID"]);
			if ($arMessage["APPROVED"] == "Y"):
				$arFields["APPROVED"] = "Y";
				$arFields["LAST_POSTER_ID"] = $arFields["ABS_LAST_POSTER_ID"];
				$arFields["LAST_POSTER_NAME"] = $arFields["ABS_LAST_POSTER_NAME"];
				$arFields["LAST_POST_DATE"] = $arFields["ABS_LAST_POST_DATE"];
				$arFields["LAST_MESSAGE_ID"] = $arFields["ABS_LAST_MESSAGE_ID"];
				if ($arMessage["NEW_TOPIC"] != "Y"):
					$arFields["=POSTS"] = "POSTS+1";
				endif;
			else:
				$arFields["=POSTS_UNAPPROVED"] = "POSTS_UNAPPROVED+1";
			endif;
		}
		else
		{
			$res = CForumMessage::GetList(array(), array("TOPIC_ID" => $ID), "cnt_not_approved");
			$res["CNT"] = (intval($res["CNT"]) - intval($res["CNT_NOT_APPROVED"]));
			$res["CNT"] = ($res["CNT"] > 0 ? $res["CNT"] : 0);
			if (intval($res["ABS_FIRST_MESSAGE_ID"]) > 0 && intval($res["ABS_FIRST_MESSAGE_ID"]) != intval($res["FIRST_MESSAGE_ID"]))
			{
				$GLOBALS["DB"]->Query("UPDATE b_forum_message SET NEW_TOPIC = (CASE WHEN ID=".intval($res["ABS_FIRST_MESSAGE_ID"])." THEN 'Y' ELSE 'N' END) WHERE TOPIC_ID=".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);

				CForumMessage::Reindex($res["ABS_FIRST_MESSAGE_ID"]);
				CForumMessage::Reindex($res["FIRST_MESSAGE_ID"]);
			}

			$arFields = array(
				"APPROVED" => ($res["CNT"] > 0 ? "Y" : "N"),
				"POSTS" => ($res["CNT"] > 0 ? ($res["CNT"] - 1) : 0),
				"LAST_POSTER_ID" => false,
				"LAST_POSTER_NAME" => false,
				"LAST_POST_DATE" => false,
				"LAST_MESSAGE_ID" => intval($res["LAST_MESSAGE_ID"]),
				"POSTS_UNAPPROVED" => intval($res["CNT_NOT_APPROVED"]),
				"ABS_LAST_POSTER_ID" => false,
				"ABS_LAST_POSTER_NAME" => false,
				"ABS_LAST_POST_DATE" => false,
				"ABS_LAST_MESSAGE_ID" => intval($res["ABS_LAST_MESSAGE_ID"]));

			if ($arFields["ABS_LAST_MESSAGE_ID"] > 0):
				$res = CForumMessage::GetByID($arFields["ABS_LAST_MESSAGE_ID"], array("FILTER" => "N"));
				$arFields["ABS_LAST_POSTER_ID"] = (intval($res["AUTHOR_ID"]) > 0 ? $res["AUTHOR_ID"] : false);
				$arFields["ABS_LAST_POSTER_NAME"] = $res["AUTHOR_NAME"];
				$arFields["ABS_LAST_POST_DATE"] = $res["POST_DATE"];
				if (intval($arFields["LAST_MESSAGE_ID"]) > 0):
					if ($arFields["LAST_MESSAGE_ID"] < $arFields["ABS_LAST_MESSAGE_ID"]):
						$res = CForumMessage::GetByID($arFields["LAST_MESSAGE_ID"], array("FILTER" => "N"));
					endif;
					$arFields["LAST_POSTER_ID"] = (intval($res["AUTHOR_ID"]) > 0 ? $res["AUTHOR_ID"] : false);
					$arFields["LAST_POSTER_NAME"] = $res["AUTHOR_NAME"];
					$arFields["LAST_POST_DATE"] = $res["POST_DATE"];
				endif;
			endif;

			foreach (array(
				"LAST_POST_DATE" => "START_DATE",
				"ABS_LAST_POST_DATE" => "START_DATE",
				"LAST_POSTER_NAME" => "USER_START_NAME",
				"ABS_LAST_POSTER_NAME" => "USER_START_NAME") as $key => $val)
			{
				if ($arFields[$key] == false)
				{
					$arFields["=".$key] = $val;
					unset($arFields[$key]);
				}
			}
		}
		return CForumTopic::Update($ID, $arFields);
	}

	public static function OnBeforeIBlockElementDelete($ELEMENT_ID)
	{
		$ELEMENT_ID = intval($ELEMENT_ID);
		if ($ELEMENT_ID > 0 && CModule::IncludeModule("iblock"))
		{
			$rsElement = CIBlockElement::GetList(
				array("ID" => "ASC"),
				array(
					"ID" => $ELEMENT_ID,
					"SHOW_HISTORY" => "Y",
					"CHECK_PERMISSIONS" => "N",
				),
				false,
				false,
				array("ID", "WF_PARENT_ELEMENT_ID", "IBLOCK_ID")
			);
			$arElement = $rsElement->Fetch();
			if(is_array($arElement) && $arElement["WF_PARENT_ELEMENT_ID"] == 0)
			{
				$rsProperty = CIBlockElement::GetProperty($arElement["IBLOCK_ID"], $arElement["ID"], array(), array("CODE" => "FORUM_TOPIC_ID"));
				if ($rsProperty && $arProperty = $rsProperty->Fetch())
				{
					if(is_array($arProperty) && $arProperty["VALUE"] > 0)
					{
						CForumTopic::Delete($arProperty["VALUE"]);
					}
				}
			}
		}
		return true;
	}

	public static function GetMessageCount($forumID, $topicID, $approved = null)
	{
		global $CACHE_MANAGER;
		static $arCacheCount = array();
		static $obCache = null;
		static $cacheLabel = 'forum_msg_count';
		static $notCached = 0;
		static $TTL = 3600000;

		if ($approved === true) $approved = "Y";
		if ($approved === false) $approved = "N";
		if ($approved === null) $approved = "A";

		if ($approved !== "Y" && $approved !== "N" && $approved !== "A")
			return false;

		if (isset($arCacheCount[$forumID][$topicID][$approved]))
		{
			return $arCacheCount[$forumID][$topicID][$approved];
		}

		if ($obCache === null)
			$obCache = new CPHPCache;

		$cacheID = md5($cacheLabel.$forumID);
		$cachePath = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$cacheLabel."/");
		if ($obCache->InitCache($TTL, $cacheID, $cachePath))
		{
			$resCache = $obCache->GetVars();
			if (is_array($resCache['messages']))
				$arCacheCount[$forumID] = $resCache['messages'];
		}

		if (isset($arCacheCount[$forumID][$topicID][$approved]))
		{
			return $arCacheCount[$forumID][$topicID][$approved];
		}
		else
		{
			$bCount = true;
			if ($approved === "N" || $approved === "Y")
				$bCount = "cnt_not_approved";

			if (intval($topicID) > 0 || $topicID === 0)
				$arFilter = array("TOPIC_ID" => $topicID);
			else
			{
				$arRes = CForumTopic::GetByID($topicID);
				if ($arRes)
					$arFilter = array("TOPIC_ID" => $arRes['ID']);
				else
					return false;
			}
			$count = CForumMessage::GetList(null, $arFilter, $bCount);

			$result = 0;
			if ($approved === "N")
			{
				$result = intval($count['CNT_NOT_APPROVED']);
			}
			elseif ($approved === "Y")
			{
				$result = $count['CNT'] - $count['CNT_NOT_APPROVED'];
			}
			else
			{
				$result = intval($count);
			}
			$notCached++;
		}

		$arCacheCount[$forumID][$topicID][$approved] = $result;

		if ($notCached > 2)
		{
			$obCache->StartDataCache($TTL, $cacheID, $cachePath);
			CForumCacheManager::SetTag($cachePath, $cacheLabel.$forumID);
			$obCache->EndDataCache(array("messages" => $arCacheCount[$forumID]));
			$notCached = 0;
		}
		return $result;
	}
}

class _CTopicDBResult extends CDBResult
{
	private $sNameTemplate = '';
	private $noFilter = false;
	private static $icons;

	public function __construct($res, $params = array())
	{
		$this->sNameTemplate = (!empty($params["sNameTemplate"]) ? $params["sNameTemplate"] : '');
		$this->noFilter = (array_key_exists('NoFilter', $params) && $params['NoFilter'] === true);
		parent::__construct($res);
	}
	protected static function getIcon($iconTyping)
	{
		if (!is_array(self::$icons))
		{
			$result = array();
			$smiles = CForumSmile::GetByType(CSmile::TYPE_ICON, LANGUAGE_ID);
			foreach ($smiles as $smile)
				$result[$smile["TYPING"]] = $smile["IMAGE"];
			self::$icons = $result;
		}
		return (array_key_exists($iconTyping, self::$icons) ? self::$icons[$iconTyping] : '');
	}
	function Fetch()
	{
		global $DB;
		if($res = parent::Fetch())
		{
			if (array_key_exists("ICON", $res) && !empty($res["ICON"]))
			{
				$res["IMAGE"] = self::getIcon($res["ICON"]);
			}

			if ($this->noFilter !== true)
			{
				if (COption::GetOptionString("forum", "FILTER", "Y") == "Y")
				{
					if (!empty($res["HTML"]))
					{
						$arr = unserialize($res["HTML"], ["allowed_classes" => false]);
						if (is_array($arr) && is_set($arr, "TITLE"))
						{
							foreach ($arr as $key => $val)
							{
								if ($val <> '')
									$res[$key] = $val;
							}
						}
					}
					if (!empty($res["F_HTML"]))
					{
						$arr = unserialize($res["F_HTML"], ["allowed_classes" => false]);
						if (is_array($arr))
						{
							foreach ($arr as $key => $val)
							{
								$res["F_".$key] = $val;
							}
						}
						if (!empty($res["TITLE"]))
							$res["F_TITLE"] = $res["TITLE"];
					}
				}

				/* For CForumUser::UserAddInfo only */
				if (is_set($res, "FIRST_POST") || is_set($res, "LAST_POST"))
				{
					$arSqlSearch = array();
					if (is_set($res, "FIRST_POST"))
						$arSqlSearch["FIRST_POST"] = "FM.ID=".intval($res["FIRST_POST"]);
					if (is_set($res, "LAST_POST"))
						$arSqlSearch["LAST_POST"] = "FM.ID=".intval($res["LAST_POST"]);
					if (!empty($arSqlSearch)):
						$strSql = "SELECT FM.ID, ".$DB->DateToCharFunction("FM.POST_DATE", "FULL")." AS POST_DATE ".
							"FROM b_forum_message FM WHERE ".implode(" OR ", $arSqlSearch);
						$db_res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
						if($db_res && $val = $db_res->Fetch()):
							do
							{
								if (is_set($res, "FIRST_POST") && $res["FIRST_POST"] == $val["ID"])
									$res["FIRST_POST_DATE"] = $val["POST_DATE"];
								if (is_set($res, "LAST_POST") && $res["LAST_POST"] == $val["ID"])
									$res["LAST_POST_DATE"] = $val["POST_DATE"];
							}while ($val = $db_res->Fetch());
						endif;
					endif;
				}

				if (!empty($this->sNameTemplate))
				{
					$arTmp = array();
					foreach (array(
						"USER_START_ID" => "USER_START_NAME",
						"LAST_POSTER_ID" => "LAST_POSTER_NAME",
						"ABS_LAST_POSTER_ID" => "ABS_LAST_POSTER_NAME") as $id => $name)
					{
						$tmp = "";
						if (!empty($res[$id]))
						{
							if (in_array($res[$id], $arTmp))
							{
								$tmp = $arTmp[$res[$id]];
							}
							else
							{
								$arTmp[$res[$id]] = $tmp = (!empty($res[$name."_FRMT"]) ? $res[$name."_FRMT"] :
									CForumUser::GetFormattedNameByUserID($res[$id], $this->sNameTemplate));
							}
						}

						$res[$name] = (!empty($tmp) ? $tmp : $res[$name]);
					}
				}
			}
		}
		return $res;
	}
}
