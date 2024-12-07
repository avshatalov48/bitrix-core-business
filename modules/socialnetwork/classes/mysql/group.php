<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/group.php");

use Bitrix\Socialnetwork\Util;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\WorkgroupSiteTable;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\Item\WorkgroupSubject;
use Bitrix\Socialnetwork\Integration;
use Bitrix\Socialnetwork\WorkgroupTagTable;
use Bitrix\Socialnetwork\Internals\EventService;

class CSocNetGroup extends CAllSocNetGroup
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function Add($arFields)
	{
		global $DB, $CACHE_MANAGER, $USER_FIELD_MANAGER;

		$arFields1 = Util::getEqualityFields($arFields);

		if (!CSocNetGroup::CheckFields("ADD", $arFields))
		{
			return false;
		}

		$arSiteID = [];
		if(array_key_exists("SITE_ID", $arFields))
		{
			if(is_array($arFields["SITE_ID"]))
			{
				foreach($arFields["SITE_ID"] as $site_id)
				{
					$arSiteID[$site_id] = $DB->ForSQL($site_id);
				}
			}
			else
			{
				$arSiteID[$arFields["SITE_ID"]] = $DB->ForSQL($arFields["SITE_ID"]);
			}
		}

		if(empty($arSiteID))
		{
			unset($arFields["SITE_ID"]);
		}
		else
		{
			$arFields["SITE_ID"] = end($arSiteID);
		}

		$arFields["SITE_ID_FULL"] = $arSiteID;
		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetGroupAdd");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
			{
				return false;
			}
		}
		unset($arFields["SITE_ID_FULL"]);

		if (
			array_key_exists("IMAGE_ID", $arFields)
			&& is_array($arFields["IMAGE_ID"])
			&& (
				!array_key_exists("MODULE_ID", $arFields["IMAGE_ID"])
				|| $arFields["IMAGE_ID"]["MODULE_ID"] == ''
			)
		)
		{
			$arFields["IMAGE_ID"]["MODULE_ID"] = "socialnetwork";
		}

		CFile::SaveForDB($arFields, "IMAGE_ID", "socialnetwork");

		$arInsert = $DB->PrepareInsert("b_sonet_group", $arFields);
		Util::processEqualityFieldsToInsert($arFields1, $arInsert);

		$ID = false;
		if ($arInsert[0] <> '')
		{
			$strSql =
				"INSERT INTO b_sonet_group(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql);

			$ID = (int)$DB->LastID();

			$events = GetModuleEvents("socialnetwork", "OnSocNetGroupAdd");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array($ID, &$arFields));
			}

			if ($ID > 0)
			{
				EventService\Service::addEvent(EventService\EventDictionary::EVENT_WORKGROUP_ADD, [
					'GROUP_ID' => $ID,
				]);

				if(!empty($arSiteID))
				{
					$DB->Query("
						DELETE FROM b_sonet_group_site WHERE GROUP_ID = ".$ID."
					");

					$DB->Query("
						INSERT INTO b_sonet_group_site(GROUP_ID, SITE_ID)
						SELECT ".$ID.", LID
						FROM b_lang
						WHERE LID IN ('".implode("', '", $arSiteID)."')
					");

					if (intval($arFields["SUBJECT_ID"]) > 0)
					{
						WorkgroupSubject::syncSiteId(array(
							'subjectId' => $arFields["SUBJECT_ID"],
							'siteId' => $arSiteID
						));
					}
				}

				if(defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->ClearByTag("sonet_group");
				}

				$USER_FIELD_MANAGER->Update("SONET_GROUP", $ID, $arFields);
				CSocNetGroup::SearchIndex($ID, $arSiteID);
				if (!empty($arFields["KEYWORDS"]))
				{
					$tagsList = explode(',', $arFields["KEYWORDS"]);
					if (
						!empty($tagsList)
						&& is_array($tagsList)
					)
					{
						$tagsList = array_map(function($a) { return trim($a, ' '); }, $tagsList);
						$tagsList = array_filter($tagsList, function($a) { return ($a <> ''); });
					}
					if (
						!empty($tagsList)
						&& is_array($tagsList)
					)
					{
						WorkgroupTagTable::set([
							'groupId' => $ID,
							'tags' => $tagsList
						]);
					}
				}

				Workgroup::setIndex(array(
					'fields' => $arFields
				));

				$groupItem = Workgroup::getById($ID, false);
				$groupItem->syncDeptConnection();
			}
		}

		return $ID;
	}

	public static function Update($ID, $arFields, $bAutoSubscribe = true, $bClearCommonTag = true, $bSync = true)
	{
		global $DB, $APPLICATION, $CACHE_MANAGER, $USER_FIELD_MANAGER;

		if (!CSocNetGroup::__ValidateID($ID))
		{
			return false;
		}

		$ID = (int)$ID;

		$arGroupOld = CSocNetGroup::GetByID($ID);
		if (!$arGroupOld)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_NO_GROUP"), "ERROR_NO_GROUP");
			return false;
		}

		$arFields1 = Util::getEqualityFields($arFields);

		if (!CSocNetGroup::CheckFields("UPDATE", $arFields, $ID))
		{
			return false;
		}

		$arSiteID = [];

		if (is_set($arFields, "SITE_ID"))
		{
			if(is_array($arFields["SITE_ID"]))
			{
				$arSiteID = $arFields["SITE_ID"];
			}
			else
			{
				$arSiteID[] = $arFields["SITE_ID"];
			}

			$arFields["SITE_ID"] = false;
			$str_SiteID = "''";
			foreach($arSiteID as $v)
			{
				$arFields["SITE_ID"] = $v;
				$str_SiteID .= ", '".$DB->ForSql($v)."'";
			}
		}

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetGroupUpdate");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields)) === false)
			{
				return false;
			}
		}

		EventService\Service::addEvent(EventService\EventDictionary::EVENT_WORKGROUP_BEFORE_UPDATE, [
			'GROUP_ID' => $ID,
		]);

		if (
			array_key_exists("IMAGE_ID", $arFields)
			&& is_array($arFields["IMAGE_ID"])
			&& (
				!array_key_exists("MODULE_ID", $arFields["IMAGE_ID"])
				|| $arFields["IMAGE_ID"]["MODULE_ID"] == ''
			)
		)
		{
			$arFields["IMAGE_ID"]["MODULE_ID"] = "socialnetwork";
		}

		CFile::SaveForDB($arFields, "IMAGE_ID", "socialnetwork");

		$strUpdate = $DB->PrepareUpdate("b_sonet_group", $arFields);
		Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		if ($strUpdate <> '')
		{
			$strSql =
				"UPDATE b_sonet_group SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql);

			if(!empty($arSiteID))
			{
				$strSql = "DELETE FROM b_sonet_group_site WHERE GROUP_ID=".$ID;
				$DB->Query($strSql);

				$strSql =
					"INSERT INTO b_sonet_group_site(GROUP_ID, SITE_ID) ".
					"SELECT ".$ID.", LID ".
					"FROM b_lang ".
					"WHERE LID IN (".$str_SiteID.") ";
				$DB->Query($strSql);

				$arLogID = array();

				$dbResult = CSocNetLog::GetList(
					array(),
					array("LOG_RIGHTS" => "SG".$ID),
					false,
					false,
					array("ID")
				);

				while ($arResult = $dbResult->Fetch())
				{
					$DB->Query("DELETE FROM b_sonet_log_site WHERE LOG_ID = ".$arResult["ID"]."");

					$DB->Query("
						INSERT INTO b_sonet_log_site(LOG_ID, SITE_ID)
						SELECT ".$arResult["ID"].", LID
						FROM b_lang
						WHERE LID IN (".$str_SiteID.")
					");

					$arLogID[] = $arResult["ID"];
				}
			}

			if (
				!empty($arSiteID)
				|| intval($arFields["SUBJECT_ID"] ?? 0) > 0
			)
			{
				$subjectId = 0;
				$groupSiteList = array();

				if (intval($arFields["SUBJECT_ID"]) <= 0)
				{
					$res = WorkgroupTable::getList(array(
						'filter' => array('=ID' => $ID),
						'select' => array('SUBJECT_ID')
					));
					if ($workgroupFieldsList = $res->fetch())
					{
						$subjectId = intval($workgroupFieldsList["SUBJECT_ID"]);
					}
				}
				else
				{
					$subjectId = intval($arFields["SUBJECT_ID"]);
				}

				if (empty($arSiteID))
				{
					$res = WorkgroupSiteTable::getList(array(
						'filter' => array('=GROUP_ID' => $ID),
						'select' => array('SITE_ID')
					));
					while ($workgroupSiteFieldsList = $res->fetch())
					{
						$groupSiteList[] = intval($workgroupSiteFieldsList["SITE_ID"]);
					}
				}
				else
				{
					$groupSiteList = $arSiteID;
				}

				if (
					$subjectId > 0
					&& !empty($groupSiteList)
				)
				{
					WorkgroupSubject::syncSiteId(array(
						'subjectId' => $subjectId,
						'siteId' => $groupSiteList
					));
				}
			}

			$sonetGroupCache = self::getStaticCache();
			unset($sonetGroupCache[$ID]);
			self::setStaticCache($sonetGroupCache);

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				if ($bClearCommonTag)
				{
					$CACHE_MANAGER->ClearByTag("sonet_group");
				}
				$CACHE_MANAGER->ClearByTag("sonet_group_".$ID);
				$CACHE_MANAGER->ClearByTag("sonet_user2group_G".$ID);
				$CACHE_MANAGER->ClearByTag("sonet_user2group");
			}

			$USER_FIELD_MANAGER->Update("SONET_GROUP", $ID, $arFields);

			$events = GetModuleEvents("socialnetwork", "OnSocNetGroupUpdate");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array($ID, &$arFields));
			}

			EventService\Service::addEvent(EventService\EventDictionary::EVENT_WORKGROUP_UPDATE, [
				'GROUP_ID' => $ID,
			]);

			CSocNetGroup::SearchIndex($ID, false, $arGroupOld);

			if (isset($arFields['KEYWORDS']))
			{
				$tagsList = explode(',', $arFields["KEYWORDS"]);
				if (
					!empty($tagsList)
					&& is_array($tagsList)
				)
				{
					$tagsList = array_map(function($a) { return trim($a, ' '); }, $tagsList);
					$tagsList = array_filter($tagsList, function($a) { return ($a <> ''); });
				}

				WorkgroupTagTable::set([
					'groupId' => $ID,
					'tags' => $tagsList
				]);
			}

			Workgroup::setIndex(array(
				'fields' => array_merge($arFields, array('ID' => $ID))
			));

			$arGroupNew = CSocNetGroup::GetByID($ID);
			if (
				$arGroupNew['OPENED'] === 'Y'
				&& $arGroupOld['OPENED'] === 'N'
			)
			{
				CSocNetGroup::ConfirmAllRequests($ID, $bAutoSubscribe);
			}

			if (
				!empty($arFields["NAME"])
				|| !empty($arFields["IMAGE_ID"])
				|| !empty($arFields["OWNER_ID"])
			)
			{
				$chatData = Integration\Im\Chat\Workgroup::getChatData(array(
					'group_id' => $ID
				));

				if (
					!empty($chatData)
					&& !empty($chatData[$ID])
					&& intval($chatData[$ID]) > 0
				)
				{
					$chatId = $chatData[$ID];

					$chat = new CIMChat(0);

					if(!empty($arFields["NAME"]))
					{
						$chat->rename(
							$chatId,
							Integration\Im\Chat\Workgroup::buildChatName(
								$arFields["NAME"],
								array(
									'project' => ($arGroupNew["PROJECT"] == 'Y')
								)
							),
							false,
							false
						);
					}

					if(!empty($arFields["IMAGE_ID"]))
					{
						$chat->setAvatarId($chatId, $arFields['IMAGE_ID']);
					}

					if(!empty($arFields["OWNER_ID"]))
					{
						$chat->setOwner($chatId, $arFields["OWNER_ID"], false);
					}
				}
			}
		}
		else
		{
			if($USER_FIELD_MANAGER->Update("SONET_GROUP", $ID, $arFields))
			{
				if(defined("BX_COMP_MANAGED_CACHE"))
				{
					if ($bClearCommonTag)
					{
						$CACHE_MANAGER->ClearByTag("sonet_group");
					}
					$CACHE_MANAGER->ClearByTag("sonet_group_".$ID);
				}
			}
			else
			{
				$ID = False;
			}
		}

		if ($ID && $bSync)
		{
			$groupItem = Workgroup::getById($ID, false);
			$groupItem->syncDeptConnection();
		}

		return $ID;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB, $USER_FIELD_MANAGER;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "SITE_ID", "NAME", "DESCRIPTION", "DATE_CREATE", "DATE_UPDATE", "ACTIVE", "VISIBLE", "OPENED", "CLOSED", "SUBJECT_ID", "OWNER_ID", "KEYWORDS", "IMAGE_ID", "NUMBER_OF_MEMBERS", "INITIATE_PERMS", "SPAM_PERMS", "DATE_ACTIVITY", "SUBJECT_NAME", "PROJECT");

		static $arFields1 = array(
			"ID" => Array("FIELD" => "G.ID", "TYPE" => "int"),
			"NAME" => Array("FIELD" => "G.NAME", "TYPE" => "string"),
			"DESCRIPTION" => Array("FIELD" => "G.DESCRIPTION", "TYPE" => "string"),
			"DATE_CREATE" => Array("FIELD" => "G.DATE_CREATE", "TYPE" => "datetime"),
			"DATE_UPDATE" => Array("FIELD" => "G.DATE_UPDATE", "TYPE" => "datetime"),
			"DATE_ACTIVITY" => Array("FIELD" => "G.DATE_ACTIVITY", "TYPE" => "datetime"),
			"ACTIVE" => Array("FIELD" => "G.ACTIVE", "TYPE" => "string"),
			"VISIBLE" => Array("FIELD" => "G.VISIBLE", "TYPE" => "string"),
			"OPENED" => Array("FIELD" => "G.OPENED", "TYPE" => "string"),
			"CLOSED" => Array("FIELD" => "G.CLOSED", "TYPE" => "string"),
			"SUBJECT_ID" => Array("FIELD" => "G.SUBJECT_ID", "TYPE" => "int"),
			"OWNER_ID" => Array("FIELD" => "G.OWNER_ID", "TYPE" => "int"),
			"KEYWORDS" => Array("FIELD" => "G.KEYWORDS", "TYPE" => "string"),
			"IMAGE_ID" => Array("FIELD" => "G.IMAGE_ID", "TYPE" => "int"),
			"NUMBER_OF_MEMBERS" => Array("FIELD" => "G.NUMBER_OF_MEMBERS", "TYPE" => "int"),
			"NUMBER_OF_MODERATORS" => Array("FIELD" => "G.NUMBER_OF_MODERATORS", "TYPE" => "int"),
			"INITIATE_PERMS" => Array("FIELD" => "G.INITIATE_PERMS", "TYPE" => "string"),
			"SPAM_PERMS" => Array("FIELD" => "G.SPAM_PERMS", "TYPE" => "string"),
			"SUBJECT_NAME" => Array("FIELD" => "S.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group_subject S ON (G.SUBJECT_ID = S.ID)"),
			"OWNER_NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (G.OWNER_ID = U.ID)"),
			"OWNER_LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (G.OWNER_ID = U.ID)"),
			"OWNER_LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (G.OWNER_ID = U.ID)"),
			"OWNER_EMAIL" => Array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (G.OWNER_ID = U.ID)"),
			"OWNER_USER" => array("FIELD" => "U.LOGIN,U.NAME,U.LAST_NAME,U.EMAIL,U.ID", "WHERE_ONLY" => "Y", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (G.OWNER_ID = U.ID)"),
			"PROJECT" => Array("FIELD" => "G.PROJECT", "TYPE" => "string"),
			"PROJECT_DATE_START" => Array("FIELD" => "G.PROJECT_DATE_START", "TYPE" => "datetime"),
			"PROJECT_DATE_FINISH" => Array("FIELD" => "G.PROJECT_DATE_FINISH", "TYPE" => "datetime"),
			"LANDING" => Array("FIELD" => "G.LANDING", "TYPE" => "string"),
			"SCRUM_OWNER_ID" => ["FIELD" => "G.SCRUM_OWNER_ID", "TYPE" => "int"],
			"SCRUM_MASTER_ID" => ["FIELD" => "G.SCRUM_MASTER_ID", "TYPE" => "int"],
			"SCRUM_SPRINT_DURATION" => ["FIELD" => "G.SCRUM_SPRINT_DURATION", "TYPE" => "int"],
			"SCRUM_TASK_RESPONSIBLE" => ["FIELD" => "G.SCRUM_TASK_RESPONSIBLE", "TYPE" => "string"],
			'AVATAR_TYPE' => [ 'FIELD' => 'G.AVATAR_TYPE', 'TYPE' => 'string'],
		);

		if (array_key_exists("SITE_ID", $arFilter))
		{
			$arFields["SITE_ID"] = Array("FIELD" => "SGS.SITE_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_group_site SGS ON G.ID = SGS.GROUP_ID");
			$strDistinct = " DISTINCT ";
			foreach ($arSelectFields as $i => $strFieldTmp)
			{
				if ($strFieldTmp === "SITE_ID")
				{
					unset($arSelectFields[$i]);
				}
			}

			foreach ($arOrder as $by => $order)
			{
				if (!in_array($by, $arSelectFields))
				{
					$arSelectFields[] = $by;
				}
			}
		}
		else
		{
			$arFields["SITE_ID"] = Array("FIELD" => "G.SITE_ID", "TYPE" => "string");
			$strDistinct = " ";
		}

		$arFields = array_merge($arFields1, $arFields);
		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields, array("ENTITY_ID" => "SONET_GROUP"));
		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", $strDistinct, $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_group G ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
			{
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			}
			if ($arSqls["GROUPBY"] <> '')
			{
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
			}

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}

		$checkPermissions = Array_Key_Exists("CHECK_PERMISSIONS", $arFilter);

		if ($checkPermissions)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_group G ".
				"	".$arSqls["FROM"]." ".
				"WHERE G.VISIBLE = 'Y' ";
			if ($arSqls["WHERE"] <> '')
			{
				$strSql .= "AND ".$arSqls["WHERE"]." ";
			}

			$strSql .= "UNION ".
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_group G ".
				"	INNER JOIN b_sonet_user2group UG ON (G.ID = UG.GROUP_ID AND UG.USER_ID = ".intval($arFilter["CHECK_PERMISSIONS"])." AND UG.ROLE <= '".$DB->ForSql(SONET_ROLES_USER, 1)."') ".
				"	".$arSqls["FROM"]." ".
				"WHERE G.VISIBLE = 'N' ";
			if ($arSqls["WHERE"] <> '')
				$strSql .= "AND ".$arSqls["WHERE"]." ";
			$strSql .= " ";

			if ($arSqls["GROUPBY"] <> '')
			{
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
			}

			if ($arSqls["ORDERBY"] <> '')
			{
				$strSql .= "ORDER BY ".Str_Replace(array(" G.", " UG.", " S."), array(" ", " ", " "), " ".$arSqls["ORDERBY"])." ";
			}
		}
		else
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_group G ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
			{
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			}
			if ($arSqls["GROUPBY"] <> '')
			{
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
			}
			if ($arSqls["ORDERBY"] <> '')
			{
				$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";
			}
		}

		if (
			is_array($arNavStartParams)
			&& intval($arNavStartParams["nTopCount"] ?? null) <= 0
		)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sonet_group G ".
				"	".$arSqls["FROM"]." ";
			if (
				$arSqls["WHERE"] <> ''
				|| $checkPermissions
			)
			{
				$strSql_tmp .= "WHERE ".($checkPermissions ? "G.VISIBLE = 'Y'" : "1 = 1").($arSqls["WHERE"] <> '' ? " AND " : "").$arSqls["WHERE"]." ";
			}
			if ($arSqls["GROUPBY"] <> '')
			{
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";
			}

			//echo "!2.1!=".htmlspecialcharsbx($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp);
			$cnt = 0;
			if ($arSqls["GROUPBY"] == '')
			{
				if ($arRes = $dbRes->Fetch())
				{
					$cnt = $arRes["CNT"];
				}
			}
			else
			{
				// ТОЛЬКО ДЛЯ MYSQL!!! ДЛЯ ORACLE ДРУГОЙ КОД
				$cnt = $dbRes->SelectedRowsCount();
			}

			if ($checkPermissions)
			{
				$strSql_tmp =
					"SELECT COUNT('x') as CNT ".
					"FROM b_sonet_group G ".
					"	INNER JOIN b_sonet_user2group UG ON (G.ID = UG.GROUP_ID AND UG.USER_ID = ".intval($arFilter["CHECK_PERMISSIONS"])." AND UG.ROLE <= '".$DB->ForSql(SONET_ROLES_USER, 1)."') ".
					"	".$arSqls["FROM"]." ".
					"WHERE G.VISIBLE = 'N' ";
				if ($arSqls["WHERE"] <> '')
				{
					$strSql_tmp .= "AND ".$arSqls["WHERE"]." ";
				}
				if ($arSqls["GROUPBY"] <> '')
				{
					$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";
				}

				//echo "!2.2!=".htmlspecialcharsbx($strSql_tmp)."<br>";

				$dbRes = $DB->Query($strSql_tmp);
				if ($arSqls["GROUPBY"] == '')
				{
					if ($arRes = $dbRes->Fetch())
					{
						$cnt += $arRes["CNT"];
					}
				}
				else
				{
					// ТОЛЬКО ДЛЯ MYSQL!!! ДЛЯ ORACLE ДРУГОЙ КОД
					$cnt += $dbRes->SelectedRowsCount();
				}
			}

			$dbRes = new CDBResult();

			//echo "!2.3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes->SetUserFields($USER_FIELD_MANAGER->GetUserFields("SONET_GROUP"));
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (
				is_array($arNavStartParams)
				&& intval($arNavStartParams["nTopCount"]) > 0
			)
			{
				$strSql .= "LIMIT ".intval($arNavStartParams["nTopCount"]);
			}

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql);
			$dbRes->SetUserFields($USER_FIELD_MANAGER->GetUserFields("SONET_GROUP"));
		}

		return $dbRes;
	}
}
