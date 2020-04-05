<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2009 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/classes/general/forum_new.php");

/**********************************************************************/
/************** FORUM *************************************************/
/**********************************************************************/
class CForumNew extends CAllForumNew
{
	public static function Add($arFields)
	{
		global $DB;

		if (!CForumNew::CheckFields("ADD", $arFields))
			return false;
/***************** Event onBeforeForumAdd **************************/
		foreach (GetModuleEvents("forum", "onBeforeForumAdd", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
				return false;
		}
/***************** /Event ******************************************/
		if (empty($arFields))
			return false;
		$arInsert = $DB->PrepareInsert("b_forum", $arFields);
		$strSql = "INSERT INTO b_forum(".$arInsert[0].") VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ID = intVal($DB->LastID());

		if ($ID > 0)
		{
			foreach ($arFields["SITES"] as $key => $value)
			{
				$DB->Query("INSERT INTO b_forum2site (FORUM_ID, SITE_ID, PATH2FORUM_MESSAGE) VALUES(".$ID.", '".$DB->ForSql($key, 2)."', '".$DB->ForSql($value, 250)."')",
					false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
			if (is_set($arFields, "GROUP_ID") && is_array($arFields["GROUP_ID"]))
			{
				CForumNew::SetAccessPermissions($ID, $arFields["GROUP_ID"]);
			}
		}
/***************** Event onAfterForumAdd ***************************/
		foreach (GetModuleEvents("forum", "onAfterForumAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$ID, &$arFields));
/***************** /Event ******************************************/
		return $ID;
	}

	public static function reindex(&$NS, $oCallback = NULL, $callback_method = "")
	{
		global $DB;

		$join = array();
		$filter = array();

		$lastMessageId = intval($NS["ID"]);
		if ($NS["MODULE"] == "forum" && $lastMessageId > 0)
		{
			$filter[] = ( intval($NS["CNT"]) > 0 ?
				"FM.ID>".$lastMessageId :
				"FM.ID>=".$lastMessageId
			);
		}

		if ($NS["SITE_ID"] != "")
		{
			$join[] = " INNER JOIN b_forum2site FS ON (FS.FORUM_ID=F.ID) ";
			$filter[] = "FS.SITE_ID='".$DB->ForSQL($NS["SITE_ID"])."' ";
		}
		if (array_key_exists("FILTER", $NS))
			foreach ($NS["FILTER"] as $f)
				$filter[] = $f;
		if (array_key_exists("JOIN", $NS))
			foreach ($NS["JOIN"] as $j)
				$join[] = $j;
		$NS["SKIPPED"] = array();

		$strSql =
			"SELECT STRAIGHT_JOIN FT.ID as TID, FM.ID as MID,
				".CForumTopic::GetSelectFields(array("sPrefix" => "FT_", "sReturnResult" => "string")).", 
				FM.*, ".$DB->DateToCharFunction("FM.POST_DATE", "FULL")." as POST_DATE,
				".$DB->DateToCharFunction("FM.EDIT_DATE", "FULL")." as EDIT_DATE,
				FU.SHOW_NAME, FU.DESCRIPTION, FU.NUM_POSTS, FU.POINTS as NUM_POINTS, FU.SIGNATURE, FU.AVATAR, FU.RANK_ID,
				".$DB->DateToCharFunction("FU.DATE_REG", "SHORT")." as DATE_REG,
				U.EMAIL, U.PERSONAL_ICQ, U.LOGIN, U.NAME, U.SECOND_NAME, U.LAST_NAME, U.PERSONAL_PHOTO
			FROM b_forum_message FM use index (PRIMARY)
				LEFT JOIN b_forum_topic FT ON (FM.TOPIC_ID = FT.ID)
				LEFT JOIN b_forum F ON (F.ID = FT.FORUM_ID) 
				LEFT JOIN b_forum_user FU ON (FM.AUTHOR_ID = FU.USER_ID)
				LEFT JOIN b_user U ON (FM.AUTHOR_ID = U.ID)
			".implode(" ", $join)."
			WHERE (F.INDEXATION = 'Y' AND FM.APPROVED = 'Y') ".(empty($filter) ? "" : " AND ".implode(" AND ", $filter))."
			ORDER BY FM.ID ASC ";
		$cnt = intval(COption::GetOptionInt("forum", "search_message_count", 0));
		if ($cnt > 0)
			$strSql .= " LIMIT 0, ".$cnt;

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if (COption::GetOptionString("forum", "FILTER", "Y") == "Y")
			$db_res = new _CMessageDBResult($db_res);

		$return = array();

		$rownum = 0;
		$lastMessageId = 0;
		if ($res = $db_res->Fetch())
		{
			static $permissions = array();
			static $sites = array();
			do
			{
				$lastMessageId = $res["ID"];
				$rownum++;
				if (!array_key_exists($res["FORUM_ID"], $permissions))
				{
					$permissions[$res["FORUM_ID"]] = array();
					$groups = CForumNew::GetAccessPermissions($res["FORUM_ID"]);
					foreach ($groups as $group)
					{
						if ($group[1] >= "E")
						{
							$permissions[$res["FORUM_ID"]][] = $group[0];
							if ($group[0]==2)
								break;
						}
					}
				}

				$result = array(
					"ID" => $res["ID"],
					"LID" => array(),
					"LAST_MODIFIED" => ((!empty($res["EDIT_DATE"])) ? $res["EDIT_DATE"] : $res["POST_DATE"]),
					"PARAM1" => $res["FORUM_ID"],
					"PARAM2" => $res["TOPIC_ID"],
					"USER_ID" => $res["AUTHOR_ID"],
					"ENTITY_TYPE_ID"  => ($res["NEW_TOPIC"] == "Y" ? "FORUM_TOPIC" : "FORUM_POST"),
					"ENTITY_ID" => ($res["NEW_TOPIC"] == "Y" ? $res["TOPIC_ID"] : $res["ID"]),
					"PERMISSIONS" => $permissions[$res["FORUM_ID"]],
					"TITLE" => $res["FT_TITLE"].($res["NEW_TOPIC"] == "Y" && !empty($res["FT_DESCRIPTION"]) ?
							", ".$res["FT_DESCRIPTION"] : ""),
					"TAGS" => ($res["NEW_TOPIC"] == "Y" ? $res["FT_TAGS"] : ""),
					"BODY" => GetMessage("AVTOR_PREF")." ".$res["AUTHOR_NAME"].". ".
						forumTextParser::clearAllTags(
							COption::GetOptionString("forum", "FILTER", "Y") != "Y" ? $res["POST_MESSAGE"] : $res["POST_MESSAGE_FILTER"]),
					"URL" => "",
					"INDEX_TITLE" => $res["NEW_TOPIC"] == "Y",
				);
				if (!array_key_exists($res["FORUM_ID"], $sites))
					$sites[$res["FORUM_ID"]] =  CForumNew::GetSites($res["FORUM_ID"]);
				foreach ($sites[$res["FORUM_ID"]] as $key => $val)
				{
					$result["LID"][$key] = CForumNew::PreparePath2Message($val,
						array(
							"FORUM_ID"=>$res["FORUM_ID"],
							"TOPIC_ID"=>$res["TOPIC_ID"],
							"TITLE_SEO"=>$res["FT_TITLE_SEO"],
							"MESSAGE_ID"=>$res["ID"],
							"SOCNET_GROUP_ID" =>$res["FT_SOCNET_GROUP_ID"],
							"OWNER_ID" => $res["FT_OWNER_ID"],
							"PARAM1" => $res["PARAM1"],
							"PARAM2" => $res["PARAM2"]));
					if (empty($result["URL"]) && !empty($result["LID"][$key]))
						$result["URL"] = $result["LID"][$key];
				}

				if (empty($result["URL"]))
				{
					static $defaultUrl = array();
					if (array_key_exists($res["FORUM_ID"], $defaultUrl))
					{
						$defaultUrl[$res["FORUM_ID"]] = "/";
						foreach ($sites[$res["FORUM_ID"]] as $key => $val)
						{
							if (($lang = CLang::GetByID($key)->Fetch()) && !empty($lang))
							{
								$defaultUrl[$res["FORUM_ID"]] = $lang["DIR"];
								break;
							}
						}
						$defaultUrl[$res["FORUM_ID"]] .= COption::GetOptionString("forum", "REL_FPATH", "")."forum/read.php?FID=#FID#&TID=#TID#&MID=#MID##message#MID#";
					}
					$result["URL"] = CForumNew::PreparePath2Message(
						$defaultUrl[$res["FORUM_ID"]],
						array(
							"FORUM_ID"=>$res["FORUM_ID"],
							"TOPIC_ID"=>$res["TOPIC_ID"],
							"TITLE_SEO"=>$res["FT_TITLE_SEO"],
							"MESSAGE_ID"=>$res["ID"],
							"SOCNET_GROUP_ID" =>$res["FT_SOCNET_GROUP_ID"],
							"OWNER_ID" => $res["FT_OWNER_ID"],
							"PARAM1" => $res["PARAM1"],
							"PARAM2" => $res["PARAM2"]
						)
					);
				}
				/***************** Events onMessageIsIndexed ***********************/
				$index = true;
				foreach(GetModuleEvents("forum", "onMessageIsIndexed", true) as $arEvent)
				{
					if (ExecuteModuleEventEx($arEvent, array($res["ID"], $res, &$result)) === false)
					{
						$index = false;
						break;
					}
				}
				/***************** /Events *****************************************/
				if ($index === true)
				{
					if ($oCallback && !call_user_func(array($oCallback, $callback_method), $result))
					{
						return $result["ID"];
					}
					$return[] = $result;
				}
				else
				{
					$NS["SKIPPED"][] = $res["ID"];
				}
			} while ($res = $db_res->Fetch());
		}

		if ($oCallback && ($cnt > 0) && ($rownum >= ($cnt - 1)))
			return $lastMessageId;
		if ($oCallback)
			return false;
		return $return;
	}

	public static function GetNowTime($ResultType = "timestamp")
	{
		global $DB;
		static $result = array();
		$ResultType = (in_array($ResultType, array("timestamp", "time")) ? $ResultType : "timestamp");
		if (empty($result)):
			$db_res = $DB->Query("SELECT ".$DB->DateToCharFunction($DB->GetNowFunction(), "FULL")." FORUM_DATE", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$res = $db_res->Fetch();
			$result["time"] = $res["FORUM_DATE"];
			$result["timestamp"] = MakeTimeStamp($res["FORUM_DATE"]);
		endif;
		return $result[$ResultType];
	}

	public static function Concat($glue = "", $pieces = array())
	{
		return "TRIM(BOTH '".$glue."' FROM REPLACE(CONCAT_WS('".$glue."',".implode(",", $pieces)."), '".$glue.$glue."', '".$glue."'))";
	}
}

/**********************************************************************/
/************** FORUM GROUP *******************************************/
/**********************************************************************/
class CForumGroup extends CAllForumGroup
{
	public static function Add($arFields)
	{
		global $DB;

		if (!CForumGroup::CheckFields("ADD", $arFields))
			return false;
		if(CACHED_b_forum_group !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum_group");
/***************** Event onBeforeGroupForumsAdd ********************/
		$events = GetModuleEvents("forum", "onBeforeGroupForumsAdd");
		while ($arEvent = $events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
				return false;
		}
/***************** /Event ******************************************/
		if (empty($arFields))
			return false;
		$arInsert = $DB->PrepareInsert("b_forum_group", $arFields);
		$strSql = "INSERT INTO b_forum_group(".$arInsert[0].") VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ID = intVal($DB->LastID());

		if (array_key_exists("LANG", $arFields))
		{
			foreach ($arFields["LANG"] as $l)
			{
				$arInsert = $DB->PrepareInsert("b_forum_group_lang", $l);
				$strSql = "INSERT INTO b_forum_group_lang(FORUM_GROUP_ID, ".$arInsert[0].") VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
		CForumGroup::Resort();
/***************** Event onAfterGroupForumsAdd *********************/
		foreach (GetModuleEvents("forum", "onAfterGroupForumsAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
/***************** /Event ******************************************/
		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;
		$ID = intVal($ID);
		if ($ID <= 0):
			return false;
		endif;

		if (!CForumGroup::CheckFields("UPDATE", $arFields, $ID))
			return false;
		if(CACHED_b_forum_group !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum_group");
/***************** Event onBeforeGroupForumsUpdate *****************/
		foreach (GetModuleEvents("forum", "onBeforeGroupForumsUpdate", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array(&$ID, &$arFields)) === false)
				return false;
		}
/***************** /Event ******************************************/
		if (empty($arFields))
			return false;
		$strUpdate = $DB->PrepareUpdate("b_forum_group", $arFields);
		if (!empty($strUpdate))
		{
			$strSql = "UPDATE b_forum_group SET ".$strUpdate." WHERE ID = ".$ID;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		if (is_set($arFields, "LANG"))
		{
			$DB->Query("DELETE FROM b_forum_group_lang WHERE FORUM_GROUP_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			foreach ($arFields["LANG"] as $l)
			{
				$arInsert = $DB->PrepareInsert("b_forum_group_lang", $l);
				$strSql = "INSERT INTO b_forum_group_lang(FORUM_GROUP_ID, ".$arInsert[0].") VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
		CForumGroup::Resort();
/***************** Event onAfterGroupForumsUpdate *****************/
		foreach (GetModuleEvents("forum", "onAfterGroupForumsUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
/***************** /Event ******************************************/
		return $ID;
	}
}
