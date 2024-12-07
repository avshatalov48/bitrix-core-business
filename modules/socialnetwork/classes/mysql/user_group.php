<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/user_group.php");

use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Internals\Counter;
use Bitrix\Socialnetwork\Util;
use Bitrix\Socialnetwork\Internals\EventService;

class CSocNetUserToGroup extends CAllSocNetUserToGroup
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function Add($arFields)
	{
		global $DB, $CACHE_MANAGER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$arFields1 = Util::getEqualityFields($arFields);

		if (!self::CheckFields("ADD", $arFields))
		{
			return false;
		}

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetUserToGroupAdd");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
			{
				return false;
			}
		}

		$arInsert = $DB->PrepareInsert("b_sonet_user2group", $arFields);

		Util::processEqualityFieldsToInsert($arFields1, $arInsert);

		$ID = false;
		if ($arInsert[0] <> '')
		{
			$queryFields = [];
			$tableFields = $connection->getTableFields('b_sonet_user2group');
			foreach ($arFields as $columnName => $columnValue)
			{
				if (array_key_exists($columnName, $tableFields))
				{
					$queryFields[$columnName] = $columnValue;
				}
			}

			$insert = $queryFields;
			$insert['DATE_CREATE'] = (new \Bitrix\Main\Type\DateTime());
			$insert['DATE_UPDATE'] = $insert['DATE_CREATE'];
			$update = $queryFields;
			$update['DATE_UPDATE'] = $insert['DATE_CREATE'];
			$merge = $helper->prepareMerge('b_sonet_user2group', ['USER_ID', 'GROUP_ID'], $insert, $update);
			if ($merge[0])
			{
				$connection->query($merge[0]);
			}
			$strSql ="SELECT ID FROM b_sonet_user2group WHERE USER_ID = "
				. intval($arFields['USER_ID']) . " AND GROUP_ID = " . intval($arFields['GROUP_ID']);
			$ar = $DB->Query($strSql)->Fetch();
			$ID = $ar ? intval($ar['ID']) : 0;
		}

		if ($ID)
		{
			CSocNetGroup::SetStat($arFields["GROUP_ID"]);
			CSocNetSearch::OnUserRelationsChange($arFields["USER_ID"]);

			$events = GetModuleEvents("socialnetwork", "OnSocNetUserToGroupAdd");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array($ID, &$arFields));
			}

			EventService\Service::addEvent(EventService\EventDictionary::EVENT_WORKGROUP_USER_ADD, [
				'GROUP_ID' => $arFields['GROUP_ID'],
				'USER_ID' => $arFields['USER_ID'],
				'ROLE' => $arFields['ROLE'],
				'INITIATED_BY_TYPE' => $arFields['INITIATED_BY_TYPE'],
			]);

			EventService\Service::addEvent(EventService\EventDictionary::EVENT_SPACE_USER_ROLE_CHANGE, [
				'GROUP_ID' => $arFields['GROUP_ID'],
				'USER_ID' => $arFields['USER_ID'],
			]);

			if (
				$arFields['INITIATED_BY_TYPE'] === SONET_INITIATED_BY_GROUP
				&& $arFields['SEND_MAIL'] !== 'N'
				&& !ModuleManager::isModuleInstalled('im')
			)
			{
				self::SendEvent($ID);
			}

			self::$roleCache[$arFields["USER_ID"]."_".$arFields["GROUP_ID"]] = array(
				"ROLE" => $arFields["ROLE"],
				"AUTO_MEMBER" => (isset($arFields["AUTO_MEMBER"]) ? $arFields["AUTO_MEMBER"] : "N")
			);

			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->ClearByTag("sonet_user2group_G".$arFields["GROUP_ID"]);
				$CACHE_MANAGER->ClearByTag("sonet_user2group_U".$arFields["USER_ID"]);
				$CACHE_MANAGER->ClearByTag("sonet_user2group");
			}

			Counter\CounterService::addEvent(Counter\Event\EventDictionary::EVENT_WORKGROUP_USER_ADD, [
				'GROUP_ID' => (int)$arFields['GROUP_ID'],
				'USER_ID' => (int)$arFields['USER_ID'],
				'ROLE' => $arFields['ROLE'],
				'INITIATED_BY_TYPE' => $arFields['INITIATED_BY_TYPE'],
				'RELATION_ID' => $ID,
			]);
		}

		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		global $DB, $APPLICATION, $CACHE_MANAGER;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = (int)$ID;

		$arUser2GroupOld = self::GetByID($ID);
		if (!$arUser2GroupOld)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_NO_USER2GROUP"), "ERROR_NO_USER2GROUP");
			return false;
		}

		$arFields1 = Util::getEqualityFields($arFields);

		if (!self::CheckFields("UPDATE", $arFields, $ID))
		{
			return false;
		}

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetUserToGroupUpdate");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array($ID, $arFields)) === false)
			{
				return false;
			}
		}

		$strUpdate = $DB->PrepareUpdate("b_sonet_user2group", $arFields);
		Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		if ($strUpdate <> '')
		{
			$strSql =
				"UPDATE b_sonet_user2group SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql);

			CSocNetGroup::SetStat($arUser2GroupOld["GROUP_ID"]);
			CSocNetSearch::OnUserRelationsChange($arUser2GroupOld["USER_ID"]);
			if (
				array_key_exists("GROUP_ID", $arFields)
				&& $arUser2GroupOld["GROUP_ID"] != $arFields["GROUP_ID"]
			)
			{
				CSocNetGroup::SetStat($arFields["GROUP_ID"]);
			}

			$events = GetModuleEvents("socialnetwork", "OnSocNetUserToGroupUpdate");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array($ID, $arFields, $arUser2GroupOld));
			}

			EventService\Service::addEvent(EventService\EventDictionary::EVENT_WORKGROUP_USER_UPDATE, [
				'GROUP_ID' => $arUser2GroupOld['GROUP_ID'],
				'USER_ID' => $arUser2GroupOld['USER_ID'],
				'OLD_ROLE' => $arUser2GroupOld['ROLE'] ?? null,
				'OLD_INITIATED_BY_TYPE' => $arUser2GroupOld['INITIATED_BY_TYPE'] ?? null,
				'NEW_ROLE' => $arFields['ROLE'] ?? null,
			]);

			if (!empty($arFields['ROLE']))
			{
				EventService\Service::addEvent(EventService\EventDictionary::EVENT_SPACE_USER_ROLE_CHANGE, [
					'GROUP_ID' => $arUser2GroupOld['GROUP_ID'],
					'USER_ID' => $arUser2GroupOld['USER_ID'],
				]);
			}

			if (array_key_exists($arUser2GroupOld["USER_ID"]."_".$arUser2GroupOld["GROUP_ID"], self::$roleCache))
			{
				unset(self::$roleCache[$arUser2GroupOld["USER_ID"]."_".$arUser2GroupOld["GROUP_ID"]]);
			}

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->ClearByTag("sonet_user2group_G".$arUser2GroupOld["GROUP_ID"]);
				$CACHE_MANAGER->ClearByTag("sonet_user2group_U".$arUser2GroupOld["USER_ID"]);
				$CACHE_MANAGER->ClearByTag("sonet_user2group");
			}

			Counter\CounterService::addEvent(Counter\Event\EventDictionary::EVENT_WORKGROUP_USER_UPDATE, [
				'GROUP_ID' => (int)($arFields['GROUP_ID'] ?? $arUser2GroupOld['GROUP_ID']),
				'USER_ID' => (int)($arFields['USER_ID'] ?? $arUser2GroupOld['USER_ID']),
				'ROLE_OLD' => $arUser2GroupOld['ROLE'],
				'ROLE_NEW' => ($arFields['ROLE'] ?? null),
				'INITIATED_BY_TYPE' => ($arFields['INITIATED_BY_TYPE'] ?? $arUser2GroupOld['INITIATED_BY_TYPE']),
				'RELATION_ID' => $arUser2GroupOld['USER_ID'],
			]);
		}
		else
		{
			$ID = False;
		}

		return $ID;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		if (count($arSelectFields) <= 0)
		{
			$arSelectFields = array("ID", "USER_ID", "GROUP_ID", "ROLE", "AUTO_MEMBER", "DATE_CREATE", "DATE_UPDATE", "INITIATED_BY_TYPE", "INITIATED_BY_USER_ID", "MESSAGE");
		}

		$online_interval = (
			array_key_exists("ONLINE_INTERVAL", $arFilter)
			&& (int)$arFilter["ONLINE_INTERVAL"] > 0
				? $arFilter["ONLINE_INTERVAL"]
				: 120
		);

		static $arFields1 = array(
			"ID" => Array("FIELD" => "UG.ID", "TYPE" => "int"),
			"USER_ID" => Array("FIELD" => "UG.USER_ID", "TYPE" => "int"),
			"GROUP_ID" => Array("FIELD" => "UG.GROUP_ID", "TYPE" => "int"),
			"ROLE" => Array("FIELD" => "UG.ROLE", "TYPE" => "string"),
			"AUTO_MEMBER" => Array("FIELD" => "UG.AUTO_MEMBER", "TYPE" => "string"),
			"DATE_CREATE" => Array("FIELD" => "UG.DATE_CREATE", "TYPE" => "datetime"),
			"DATE_UPDATE" => Array("FIELD" => "UG.DATE_UPDATE", "TYPE" => "datetime"),
			"INITIATED_BY_TYPE" => Array("FIELD" => "UG.INITIATED_BY_TYPE", "TYPE" => "string"),
			"INITIATED_BY_USER_ID" => Array("FIELD" => "UG.INITIATED_BY_USER_ID", "TYPE" => "int"),
			"MESSAGE" => Array("FIELD" => "UG.MESSAGE", "TYPE" => "string"),
			"GROUP_NAME" => Array("FIELD" => "G.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_DESCRIPTION" => Array("FIELD" => "G.DESCRIPTION", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_ACTIVE" => Array("FIELD" => "G.ACTIVE", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_PROJECT" => Array("FIELD" => "G.PROJECT", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_IMAGE_ID" => Array("FIELD" => "G.IMAGE_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_VISIBLE" => Array("FIELD" => "G.VISIBLE", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_OWNER_ID" => Array("FIELD" => "G.OWNER_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_INITIATE_PERMS" => Array("FIELD" => "G.INITIATE_PERMS", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_OPENED" => Array("FIELD" => "G.OPENED", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_NUMBER_OF_MEMBERS" => Array("FIELD" => "G.NUMBER_OF_MEMBERS", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_DATE_ACTIVITY" => Array("FIELD" => "G.DATE_ACTIVITY", "TYPE" => "datetime", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_CLOSED" => Array("FIELD" => "G.CLOSED", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_LANDING" => Array("FIELD" => "G.LANDING", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"USER_ACTIVE" => Array("FIELD" => "U.ACTIVE", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_WORK_POSITION" => Array("FIELD" => "U.WORK_POSITION", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_EMAIL" => Array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_CONFIRM_CODE" => Array("FIELD" => "U.CONFIRM_CODE", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_PERSONAL_PHOTO" => Array("FIELD" => "U.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_PERSONAL_GENDER" => Array("FIELD" => "U.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_LID" => Array("FIELD" => "U.LID", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"INITIATED_BY_USER_NAME" => Array("FIELD" => "U1.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"INITIATED_BY_USER_LAST_NAME" => Array("FIELD" => "U1.LAST_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"INITIATED_BY_USER_SECOND_NAME" => Array("FIELD" => "U1.SECOND_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"INITIATED_BY_USER_LOGIN" => Array("FIELD" => "U1.LOGIN", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"INITIATED_BY_USER_EMAIL" => Array("FIELD" => "U1.EMAIL", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"INITIATED_BY_USER_PHOTO" => Array("FIELD" => "U1.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"INITIATED_BY_USER_GENDER" => Array("FIELD" => "U1.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"SCRUM_OWNER_ID" => Array("FIELD" => "G.SCRUM_OWNER_ID", "TYPE" => "int"),
			"GROUP_SCRUM_MASTER_ID" => [ 'FIELD' => 'G.SCRUM_MASTER_ID', 'TYPE' => 'int' ],
		);
		$arFields1['RAND'] = Array("FIELD" => $helper->getRandomFunction(), "TYPE" => "string");
		$arFields["USER_IS_ONLINE"] = Array("FIELD" => "CASE WHEN U.LAST_ACTIVITY_DATE > " . $helper->addSecondsToDateTime(-$online_interval) . " THEN 'Y' ELSE 'N' END", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)");

		if (array_key_exists("GROUP_SITE_ID", $arFilter))
		{
			$arFields["GROUP_SITE_ID"] = Array("FIELD" => "SGS.SITE_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_group_site SGS ON UG.GROUP_ID = SGS.GROUP_ID");
			$strDistinct = " DISTINCT ";
			foreach ($arSelectFields as $i => $strFieldTmp)
			{
				if ($strFieldTmp === "GROUP_SITE_ID")
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
			$arFields["GROUP_SITE_ID"] = Array("FIELD" => "G.SITE_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)");
			$strDistinct = " ";
		}

		$arFields = array_merge($arFields1, $arFields);
		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);
		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", $strDistinct, $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_user2group UG ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
			{
				$strSql .= "WHERE " . $arSqls["WHERE"] . " ";
			}
			if ($arSqls["GROUPBY"] <> '')
			{
				$strSql .= "GROUP BY " . $arSqls["GROUPBY"] . " ";
			}

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql);
			return (($arRes = $dbRes->Fetch()) ? $arRes["CNT"] : false);
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sonet_user2group UG ".
			"	".$arSqls["FROM"]." ";
		if ($arSqls["WHERE"] <> '')
		{
			$strSql .= "WHERE " . $arSqls["WHERE"] . " ";
		}
		if ($arSqls["GROUPBY"] <> '')
		{
			$strSql .= "GROUP BY " . $arSqls["GROUPBY"] . " ";
		}
		if ($arSqls["ORDERBY"] <> '')
		{
			$strSql .= "ORDER BY " . $arSqls["ORDERBY"] . " ";
		}

		if (
			is_array($arNavStartParams)
			&& (int)($arNavStartParams["nTopCount"] ?? 0) <= 0
		)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sonet_user2group UG ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

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

			$dbRes = new CDBResult();

			//echo "!2.2!=".htmlspecialcharsbx($strSql)."<br>";

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
		}

		return $dbRes;
	}
}
