<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/log.php");

use Bitrix\Socialnetwork\Item\LogIndex;
use Bitrix\Socialnetwork\LogIndexTable;
use Bitrix\Socialnetwork\LogRightTable;
use Bitrix\Socialnetwork\LogTable;
use Bitrix\Socialnetwork\LogTagTable;
use Bitrix\Socialnetwork\UserContentViewTable;
use Bitrix\Socialnetwork\Util;

class CSocNetLog extends CAllSocNetLog
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function Add($arFields, $bSendEvent = true)
	{
		global $DB, $USER_FIELD_MANAGER;

		$arFields1 = Util::getEqualityFields($arFields);

		if (!self::CheckFields("ADD", $arFields))
		{
			return false;
		}

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetLogAdd");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
			{
				return false;
			}
		}

		$arSiteID = array();
		if (isset($arFields['SITE_ID']))
		{
			if (is_array($arFields["SITE_ID"]))
			{
				foreach ($arFields["SITE_ID"] as $site_id)
				{
					$arSiteID[$site_id] = $DB->ForSQL($site_id);
				}
			}
			else
			{
				$arSiteID[$arFields["SITE_ID"]] = $DB->ForSQL($arFields["SITE_ID"]);
			}
		}

		if (empty($arSiteID))
		{
			unset($arFields["SITE_ID"]);
		}
		else
		{
			$arFields["SITE_ID"] = end($arSiteID);
		}

		unset($arFields["LOG_UPDATE"]);
		if (empty($arFields1["LOG_UPDATE"]))
		{
			$arFields["~LOG_UPDATE"] = CDatabase::CurrentTimeFunction();
		}

		$arInsert = $DB->PrepareInsert("b_sonet_log", $arFields);
		Util::processEqualityFieldsToInsert($arFields1, $arInsert);

		$ID = false;
		if ($arInsert[0] <> '')
		{
			$strSql =
				"INSERT INTO b_sonet_log(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = (int)$DB->LastID();

			if ($ID > 0)
			{
				\Bitrix\Socialnetwork\ComponentHelper::userLogSubscribe(array(
					'logId' => $ID,
					'userId' => (isset($arFields["USER_ID"]) ? (int)$arFields["USER_ID"] : 0),
					'typeList' => array(
						'FOLLOW',
					)
				));

				if ($bSendEvent)
				{
					self::SendEvent($ID);
				}

				if (!empty($arSiteID))
				{
					$DB->Query("
					INSERT INTO b_sonet_log_site(LOG_ID, SITE_ID)
					SELECT ".$ID.", LID
					FROM b_lang
					WHERE LID IN ('".implode("', '", $arSiteID)."')
				", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
				}

				if (isset($arFields["TAG"]))
				{
					LogTagTable::set(array(
						'itemType' => LogTagTable::ITEM_TYPE_LOG,
						'itemId' => $ID,
						'tags' => $arFields["TAG"]
					));
				}

				$USER_FIELD_MANAGER->Update("SONET_LOG", $ID, $arFields);

				$arFields["ID"] = $ID;
				$events = GetModuleEvents("socialnetwork", "OnAfterSocNetLogAdd");
				while ($arEvent = $events->Fetch())
				{
					ExecuteModuleEventEx($arEvent, array($arFields));
				}

				LogIndex::setIndex([
					'itemType' => LogIndexTable::ITEM_TYPE_LOG,
					'itemId' => $ID,
					'fields' => $arFields,
				]);
			}
		}

		CSocNetLogTools::SetCacheLastLogID("log", $ID);

		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		global $DB, $CACHE_MANAGER, $APPLICATION, $USER_FIELD_MANAGER;

		$ID = (int)$ID;
		if ($ID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_L_WRONG_PARAMETER_ID"), "ERROR_NO_ID");
			return false;
		}

		$str_SiteID = '';

		$arFields1 = Util::getEqualityFields($arFields);
		if (!self::CheckFields("UPDATE", $arFields, $ID))
		{
			return false;
		}

		$res = GetModuleEvents('socialnetwork', 'OnBeforeSocNetLogUpdate');
		while ($eventFields = $res->fetch())
		{
			if (ExecuteModuleEventEx($eventFields, [ &$arFields ]) === false)
			{
				return false;
			}
		}

		$arSiteID = [];
		if (is_set($arFields, "SITE_ID"))
		{
			if (is_array($arFields["SITE_ID"]))
			{
				$arSiteID = $arFields["SITE_ID"];
			}
			else
			{
				$arSiteID[] = $arFields["SITE_ID"];
			}

			$arFields["SITE_ID"] = false;
			$str_SiteID = "''";
			foreach ($arSiteID as $v)
			{
				$arFields["SITE_ID"] = $v;
				$str_SiteID .= ", '".$DB->ForSql($v)."'";
			}
		}

		$strUpdate = $DB->PrepareUpdate("b_sonet_log", $arFields);
		Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		if ($strUpdate !== '')
		{
			$strSql =
				"UPDATE b_sonet_log SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			if (!empty($arSiteID))
			{
				$strSql = "DELETE FROM b_sonet_log_site WHERE LOG_ID=".$ID;
				$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

				$strSql =
					"INSERT INTO b_sonet_log_site(LOG_ID, SITE_ID) " .
					"SELECT " . $ID . ", LID " .
					"FROM b_lang " .
					"WHERE LID IN (" . $str_SiteID . ") " .
					"ON DUPLICATE KEY UPDATE LOG_ID = " . $ID;
				$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			}

			if (isset($arFields["TAG"]))
			{
				LogTagTable::set([
					'itemType' => LogTagTable::ITEM_TYPE_LOG,
					'itemId' => $ID,
					'tags' => $arFields["TAG"],
				]);
			}

			$USER_FIELD_MANAGER->Update("SONET_LOG", $ID, $arFields);

			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->ClearByTag("SONET_LOG_".$ID);
			}

			$cache = new CPHPCache;
			$cache->CleanDir("/sonet/log/" . (int)($ID / 1000) . "/" . $ID . "/comments/");
		}
		elseif (!$USER_FIELD_MANAGER->Update("SONET_LOG", $ID, $arFields))
		{
			$ID = false;
		}

		if ((int)$ID > 0)
		{
			$events = GetModuleEvents("socialnetwork", "OnAfterSocNetLogUpdate");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));
			}

			if (
				!empty($arFields['TITLE'])
				|| !empty($arFields['MESSAGE'])
			)
			{
				LogIndex::setIndex(array(
					'itemType' => LogIndexTable::ITEM_TYPE_LOG,
					'itemId' => $ID,
					'fields' => $arFields
				));
			}

			if (
				!empty($arFields['LOG_UPDATE'])
				|| !empty($arFields1['LOG_UPDATE'])
			)
			{
				$res = LogTable::getList(array(
					'filter' => array(
						'ID' => $ID
					),
					'select' => array('LOG_UPDATE')
				));
				if (
					($logFields = $res->fetch())
					&& !empty($logFields['LOG_UPDATE'])
				)
				{
					LogRightTable::setLogUpdate(array(
						'logId' => $ID,
						'value' => $logFields['LOG_UPDATE']
					));
					LogIndexTable::setLogUpdate(array(
						'logId' => $ID,
						'value' => $logFields['LOG_UPDATE']
					));
				}
			}
		}

		return $ID;
	}

	public static function ClearOld($days = 90)
	{
		global $DB;

		$days = (int)$days;
		if ($days <= 0)
		{
			return true;
		}

		$DB->Query("DELETE LC FROM b_sonet_log_comment LC INNER JOIN (SELECT L.TMP_ID FROM b_sonet_log L LEFT JOIN b_sonet_log_favorites LF ON L.ID = LF.LOG_ID WHERE LF.USER_ID IS NULL AND L.LOG_UPDATE < DATE_SUB(NOW(), INTERVAL ".$days." DAY)) L1 ON LC.LOG_ID = L1.TMP_ID", true);
		$DB->Query("DELETE LS FROM b_sonet_log_site LS INNER JOIN (SELECT L.ID FROM b_sonet_log L LEFT JOIN b_sonet_log_favorites LF ON L.ID = LF.LOG_ID WHERE LF.USER_ID IS NULL AND L.LOG_UPDATE < DATE_SUB(NOW(), INTERVAL ".$days." DAY)) L1 ON LS.LOG_ID = L1.ID", true);
		$DB->Query("DELETE LR FROM b_sonet_log_right LR INNER JOIN (SELECT L.ID FROM b_sonet_log L LEFT JOIN b_sonet_log_favorites LF ON L.ID = LF.LOG_ID WHERE LF.USER_ID IS NULL AND L.LOG_UPDATE < DATE_SUB(NOW(), INTERVAL ".$days." DAY)) L1 ON LR.LOG_ID = L1.ID", true);

		return $DB->Query("DELETE FROM b_sonet_log WHERE LOG_UPDATE < DATE_SUB(NOW(), INTERVAL ".$days." DAY)", true);
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array(), $arParams = array())
	{
		global $DB, $USER, $USER_FIELD_MANAGER;

		$arSocNetAllowedSubscribeEntityTypesDesc = CSocNetAllowed::GetAllowedEntityTypesDesc();

		$obUserFieldsSql = new CUserTypeSQL;
		$obUserFieldsSql->SetEntity("SONET_LOG", "L.ID");
		$obUserFieldsSql->SetSelect($arSelectFields);
		$obUserFieldsSql->SetFilter($arFilter);
		$obUserFieldsSql->SetOrder($arOrder);

		if (count($arSelectFields) <= 0)
		{
			$arSelectFields = array(
				"ID", "TMP_ID", "ENTITY_TYPE", "ENTITY_ID", "USER_ID", "EVENT_ID", "LOG_DATE", "LOG_UPDATE", "TITLE_TEMPLATE", "TITLE", "MESSAGE", "TEXT_MESSAGE", "URL", "MODULE_ID", "CALLBACK_FUNC", "EXTERNAL_ID", "SITE_ID", "PARAMS",
				"COMMENTS_COUNT", "ENABLE_COMMENTS", "SOURCE_ID",
				"GROUP_NAME", "GROUP_OWNER_ID", "GROUP_INITIATE_PERMS", "GROUP_VISIBLE", "GROUP_OPENED", "GROUP_IMAGE_ID",
				"USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER",
				"CREATED_BY_NAME", "CREATED_BY_LAST_NAME", "CREATED_BY_SECOND_NAME", "CREATED_BY_LOGIN", "CREATED_BY_PERSONAL_PHOTO", "CREATED_BY_PERSONAL_GENDER",
				"RATING_TYPE_ID", "RATING_ENTITY_ID", "RATING_TOTAL_VALUE", "RATING_TOTAL_VOTES", "RATING_TOTAL_POSITIVE_VOTES", "RATING_TOTAL_NEGATIVE_VOTES", "RATING_USER_VOTE_VALUE",
				"SOURCE_TYPE"
			);
			if (
				!isset($arParams["USE_FAVORITES"])
				|| $arParams["USE_FAVORITES"] != "N"
			)
			{
				$arSelectFields[] = "FAVORITES_USER_ID";
			}
		}

		static $arFields1 = array(
			"ID" => Array("FIELD" => "L.ID", "TYPE" => "int"),
			"TMP_ID" => Array("FIELD" => "L.TMP_ID", "TYPE" => "int"),
			"SOURCE_ID" => Array("FIELD" => "L.SOURCE_ID", "TYPE" => "int"),
			"ENTITY_TYPE" => Array("FIELD" => "L.ENTITY_TYPE", "TYPE" => "string"),
			"ENTITY_ID" => Array("FIELD" => "L.ENTITY_ID", "TYPE" => "int"),
			"USER_ID" => Array("FIELD" => "L.USER_ID", "TYPE" => "int"),
			"EVENT_ID" => Array("FIELD" => "L.EVENT_ID", "TYPE" => "string"),
			"LOG_DATE" => Array("FIELD" => "L.LOG_DATE", "TYPE" => "datetime"),
			"LOG_DATE_TS" => Array("FIELD" => "UNIX_TIMESTAMP(L.LOG_DATE)", "TYPE" => "int"),
			"LOG_UPDATE" => Array("FIELD" => "L.LOG_UPDATE", "TYPE" => "datetime"),
			"TITLE_TEMPLATE" => Array("FIELD" => "L.TITLE_TEMPLATE", "TYPE" => "string"),
			"TITLE" => Array("FIELD" => "L.TITLE", "TYPE" => "string"),
			"MESSAGE" => Array("FIELD" => "L.MESSAGE", "TYPE" => "string"),
			"TEXT_MESSAGE" => Array("FIELD" => "L.TEXT_MESSAGE", "TYPE" => "string"),
			"URL" => Array("FIELD" => "L.URL", "TYPE" => "string"),
			"MODULE_ID" => Array("FIELD" => "L.MODULE_ID", "TYPE" => "string"),
			"CALLBACK_FUNC" => Array("FIELD" => "L.CALLBACK_FUNC", "TYPE" => "string"),
			"EXTERNAL_ID" => Array("FIELD" => "L.EXTERNAL_ID", "TYPE" => "string"),
			"PARAMS" => Array("FIELD" => "L.PARAMS", "TYPE" => "string"),
			"COMMENTS_COUNT" => Array("FIELD" => "L.COMMENTS_COUNT", "TYPE" => "int"),
			"ENABLE_COMMENTS" => Array("FIELD" => "L.ENABLE_COMMENTS", "TYPE" => "string"),
			"SOURCE_TYPE" => Array("FIELD" => "L.SOURCE_TYPE", "TYPE" => "string"),
			"INACTIVE" => Array("FIELD" => "L.INACTIVE", "TYPE" => "string"),
			"CONTENT" => Array("FIELD" => "LI.CONTENT", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_log_index LI ON (LI.LOG_ID = L.ID)"),
			"CONTENT_LOG_UPDATE" => Array("FIELD" => "LI.LOG_UPDATE", "TYPE" => "datetime", "FROM" => "INNER JOIN b_sonet_log_index LI ON (LI.LOG_ID = L.ID)"),
			"CONTENT_ITEM_TYPE" => Array("FIELD" => "LI.ITEM_TYPE", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_log_index LI ON (LI.LOG_ID = L.ID)"),
			"CONTENT_ITEM_ID" => Array("FIELD" => "LI.ITEM_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sonet_log_index LI ON (LI.LOG_ID = L.ID)"),
			"CONTENT_DATE_CREATE" => Array("FIELD" => "LI.DATE_CREATE", "TYPE" => "datetime", "FROM" => "INNER JOIN b_sonet_log_index LI ON (LI.LOG_ID = L.ID)"),
			"GROUP_NAME" => Array("FIELD" => "G.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_group G ON (L.ENTITY_TYPE = 'G' AND L.ENTITY_ID = G.ID)"),
			"GROUP_OWNER_ID" => Array("FIELD" => "G.OWNER_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sonet_group G ON (L.ENTITY_TYPE = 'G' AND L.ENTITY_ID = G.ID)"),
			"GROUP_INITIATE_PERMS" => Array("FIELD" => "G.INITIATE_PERMS", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_group G ON (L.ENTITY_TYPE = 'G' AND L.ENTITY_ID = G.ID)"),
			"GROUP_VISIBLE" => Array("FIELD" => "G.VISIBLE", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_group G ON (L.ENTITY_TYPE = 'G' AND L.ENTITY_ID = G.ID)"),
			"GROUP_OPENED" => Array("FIELD" => "G.OPENED", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_group G ON (L.ENTITY_TYPE = 'G' AND L.ENTITY_ID = G.ID)"),
			"GROUP_IMAGE_ID" => Array("FIELD" => "G.IMAGE_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sonet_group G ON (L.ENTITY_TYPE = 'G' AND L.ENTITY_ID = G.ID)"),
			"USER_NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (L.ENTITY_TYPE = 'U' AND L.ENTITY_ID = U.ID)"),
			"USER_LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (L.ENTITY_TYPE = 'U' AND L.ENTITY_ID = U.ID)"),
			"USER_SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (L.ENTITY_TYPE = 'U' AND L.ENTITY_ID = U.ID)"),
			"USER_LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (L.ENTITY_TYPE = 'U' AND L.ENTITY_ID = U.ID)"),
			"USER_PERSONAL_PHOTO" => Array("FIELD" => "U.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "LEFT JOIN b_user U ON (L.ENTITY_TYPE = 'U' AND L.ENTITY_ID = U.ID)"),
			"USER_PERSONAL_GENDER" => Array("FIELD" => "U.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (L.ENTITY_TYPE = 'U' AND L.ENTITY_ID = U.ID)"),
			"CREATED_BY_NAME" => Array("FIELD" => "U1.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON L.USER_ID = U1.ID"),
			"CREATED_BY_LAST_NAME" => Array("FIELD" => "U1.LAST_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON L.USER_ID = U1.ID"),
			"CREATED_BY_SECOND_NAME" => Array("FIELD" => "U1.SECOND_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON L.USER_ID = U1.ID"),
			"CREATED_BY_LOGIN" => Array("FIELD" => "U1.LOGIN", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON L.USER_ID = U1.ID"),
			"CREATED_BY_PERSONAL_PHOTO" => Array("FIELD" => "U1.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "LEFT JOIN b_user U1 ON L.USER_ID = U1.ID"),
			"CREATED_BY_PERSONAL_GENDER" => Array("FIELD" => "U1.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON L.USER_ID = U1.ID"),
			"USER_ID|COMMENT_USER_ID" => Array("FIELD" => "L.USER_ID|LC.USER_ID", "WHERE" => array("CSocNetLog", "GetSimpleOrQuery"), "FROM" => "LEFT JOIN b_sonet_log_comment LC ON LC.LOG_ID = L.ID"),
		);

		$arFields = array(
			"RATING_TYPE_ID" => Array("FIELD" => "L.RATING_TYPE_ID", "TYPE" => "string"),
			"RATING_ENTITY_ID" => Array("FIELD" => "L.RATING_ENTITY_ID", "TYPE" => "int"),
			"RATING_TOTAL_VALUE" => Array("FIELD" => $DB->IsNull('RG.TOTAL_VALUE', '0'), "TYPE" => "double", "FROM" => "LEFT JOIN b_rating_voting RG ON L.RATING_TYPE_ID = RG.ENTITY_TYPE_ID AND L.RATING_ENTITY_ID = RG.ENTITY_ID"),
			"RATING_TOTAL_VOTES" => Array("FIELD" => $DB->IsNull('RG.TOTAL_VOTES', '0'), "TYPE" => "double", "FROM" => "LEFT JOIN b_rating_voting RG ON L.RATING_TYPE_ID = RG.ENTITY_TYPE_ID AND L.RATING_ENTITY_ID = RG.ENTITY_ID"),
			"RATING_TOTAL_POSITIVE_VOTES" => Array("FIELD" => $DB->IsNull('RG.TOTAL_POSITIVE_VOTES', '0'), "TYPE" => "int", "FROM" => "LEFT JOIN b_rating_voting RG ON L.RATING_TYPE_ID = RG.ENTITY_TYPE_ID AND L.RATING_ENTITY_ID = RG.ENTITY_ID"),
			"RATING_TOTAL_NEGATIVE_VOTES" => Array("FIELD" => $DB->IsNull('RG.TOTAL_NEGATIVE_VOTES', '0'), "TYPE" => "int", "FROM" => "LEFT JOIN b_rating_voting RG ON L.RATING_TYPE_ID = RG.ENTITY_TYPE_ID AND L.RATING_ENTITY_ID = RG.ENTITY_ID"),
		);

		if (isset($USER) && is_object($USER))
		{
			$arFields["RATING_USER_VOTE_VALUE"] = array(
				"FIELD" => $DB->IsNull('RV.VALUE', '0'),
				"TYPE" => "double",
				"FROM" => "LEFT JOIN b_rating_vote RV ON L.RATING_TYPE_ID = RV.ENTITY_TYPE_ID AND L.RATING_ENTITY_ID = RV.ENTITY_ID AND RV.USER_ID = " . (int)$USER->GetID()
			);

			if (
				!isset($arParams["USE_FAVORITES"])
				|| $arParams["USE_FAVORITES"] !== "N"
			)
			{
				$join_type = "LEFT";
				$field_value = $DB->IsNull("SLF.USER_ID", "0");

				foreach ($arFilter as $key => $value)
				{
					if (mb_strpos($key, "FAVORITES_USER_ID") !== false)
					{
						$join_type = "INNER";
						$field_value = "SLF.USER_ID";
						break;
					}
				}

				$arFields["FAVORITES_USER_ID"] = [
					"FIELD" => $field_value,
					"TYPE" => "double",
					"FROM" => $join_type . " JOIN b_sonet_log_favorites SLF ON L.ID = SLF.LOG_ID AND SLF.USER_ID = " . (int)$USER->GetID(),
				];
			}

			if (
				isset($arParams["USE_PINNED"])
				&& $arParams["USE_PINNED"] === "Y"
			)
			{
				$join_type = "LEFT";
				$field_value = $DB->IsNull("SLP.USER_ID", "0");

				foreach ($arFilter as $key => $value)
				{
					if (
						$value
						&& mb_strpos($key, "PINNED_USER_ID") !== false
					)
					{
						$join_type = "INNER";
						$field_value = "SLP.USER_ID";
						$arOrder['PINNED_DATE'] = 'DESC';
						break;
					}
				}

				$arFields["PINNED_USER_ID"] = Array("FIELD" => $field_value, "TYPE" => "double", "FROM" => $join_type." JOIN b_sonet_log_pinned SLP ON L.ID = SLP.LOG_ID AND SLP.USER_ID = " . (int)$USER->GetID());
				$arFields["PINNED_DATE"] = Array("FIELD" => "SLP.PINNED_DATE", "TYPE" => "datetime", "FROM" => $join_type." JOIN b_sonet_log_pinned SLP ON L.ID = SLP.LOG_ID AND SLP.USER_ID = " . (int)$USER->GetID());
			}
		}

		if (
			isset($USER)
			&& is_object($USER)
			&& $USER->IsAuthorized()
		)
		{
			$default_follow = CSocNetLogFollow::GetDefaultValue($USER->GetID());
			$default_field = ($default_follow == "Y" ? "LOG_UPDATE" : "LOG_DATE");

			$arFields["DATE_FOLLOW"] = Array(
				"FIELD" => "CASE
					WHEN LFW.USER_ID IS NULL
						THEN L.".$default_field."
					WHEN LFW.FOLLOW_DATE IS NOT NULL
						THEN LFW.FOLLOW_DATE
					WHEN LFW.TYPE = 'Y'
						THEN L.LOG_UPDATE
					ELSE L.LOG_DATE
				END",
				"TYPE" => "datetime",
				"FROM" => "LEFT JOIN b_sonet_log_follow LFW ON LFW.USER_ID = ".$USER->GetID()." AND LFW.REF_ID = L.ID AND LFW.CODE = ".$DB->Concat("'L'", "L.ID")
			);

			$arFields["FOLLOW"] = Array(
				"FIELD" => "CASE
					WHEN LFW.USER_ID IS NULL
						THEN '".$default_follow."'
					ELSE LFW.TYPE
				END",
				"TYPE" => "string",
				"FROM" => "LEFT JOIN b_sonet_log_follow LFW ON LFW.USER_ID = ".$USER->GetID()." AND LFW.REF_ID = L.ID AND LFW.CODE = ".$DB->Concat("'L'", "L.ID")
			);

			if (!in_array("FOLLOW", $arSelectFields))
			{
				$arSelectFields[] = "FOLLOW";
			}
		}

		if (
			!isset($arFilter["INACTIVE"])
			&& !isset($arFilter["!INACTIVE"])
			&& !isset($arFilter["=INACTIVE"])
			&& !isset($arFilter["!=INACTIVE"])
		)
		{
			$arFilter["!=INACTIVE"] = 'Y';
		}

		if (isset($arFilter['SITE_ID']))
		{
			$arFields["SITE_ID"] = Array(
				"FIELD" => "SLS.SITE_ID",
				"TYPE" => "string",
				"FROM" => "LEFT JOIN b_sonet_log_site SLS ON L.ID = SLS.LOG_ID"
			);

			if (is_array($arFilter["SITE_ID"]))
			{
				$site_cnt = 0;
				foreach ($arFilter["SITE_ID"] as $site_id_tmp)
				{
					if ($site_id_tmp)
					{
						$site_cnt++;
					}
				}

				$strDistinct = ($site_cnt > 1 ? " DISTINCT " : " ");
			}
			else
			{
				$strDistinct = " ";
			}

			foreach ($arSelectFields as $i => $strFieldTmp)
			{
				if ($strFieldTmp == "SITE_ID")
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
			$arFields["SITE_ID"] = Array("FIELD" => "L.SITE_ID", "TYPE" => "string");
			$strDistinct = " ";
		}

		if (
			isset($arFilter["USER_ID"])
			&& !isset($arFilter["ENTITY_TYPE"])
		)
		{
			$arCBFilterEntityType = array();
			foreach ($arSocNetAllowedSubscribeEntityTypesDesc as $entity_type_tmp => $arEntityTypeTmp)
			{
				if (
					isset($arEntityTypeTmp['USE_CB_FILTER'])
					&& $arEntityTypeTmp["USE_CB_FILTER"] == "Y"
				)
				{
					$arCBFilterEntityType[] = $entity_type_tmp;
				}
			}

			if (
				is_array($arCBFilterEntityType)
				&& count($arCBFilterEntityType) > 0
			)
			{
				$arFilter["ENTITY_TYPE"] = $arCBFilterEntityType;
			}
		}

		if (isset($arFilter["LOG_RIGHTS"]))
		{
			$Rights = array();
			if (is_array($arFilter["LOG_RIGHTS"]))
			{
				foreach ($arFilter["LOG_RIGHTS"] as $str)
				{
					if (trim($str))
					{
						$Rights[] = trim($str);
					}
				}
			}
			elseif (trim($arFilter["LOG_RIGHTS"]))
			{
				$Rights = trim($arFilter["LOG_RIGHTS"]);
			}

			unset($arFilter["LOG_RIGHTS"]);
			if (
				(
					is_array($Rights)
					&& !empty($Rights)
				)
				|| !is_array($Rights)
			)
			{
				$arFilter["LOG_RIGHTS"] = $Rights;
				$arFields["LOG_RIGHTS"] = Array(
					"FIELD" => "SLR0.GROUP_CODE",
					"TYPE" => "string",
					"FROM" => "INNER JOIN b_sonet_log_right SLR0 ON L.ID = SLR0.LOG_ID"
				);

				if (!empty($arFilter['>=LOG_UPDATE']))
				{
					$arFields["SLR_LOG_UPDATE"] = Array(
						"FIELD" => "SLR0.LOG_UPDATE",
						"TYPE" => "datetime",
						"FROM" => "INNER JOIN b_sonet_log_right SLR0 ON L.ID = SLR0.LOG_ID"
					);
					$arFilter['>=SLR_LOG_UPDATE'] = $arFilter['>=LOG_UPDATE'];
					unset($arFilter['>=LOG_UPDATE']);

					if (!empty($arOrder['LOG_UPDATE']))
					{
						$arOrder['SLR_LOG_UPDATE'] = $arOrder['LOG_UPDATE'];
						unset($arOrder['LOG_UPDATE']);
					}

					if (!empty($arOrder['ID']))
					{
						$arFields["SLR_LOG_ID"] = Array(
							"FIELD" => "SLR0.LOG_ID",
							"TYPE" => "int",
							"FROM" => "INNER JOIN b_sonet_log_right SLR0 ON L.ID = SLR0.LOG_ID"
						);
						$arOrder['SLR_LOG_ID'] = $arOrder['ID'];
						unset($arOrder['ID']);
					}
				}
			}

			if (
				is_array($Rights)
				&& count($Rights) > 1
			)
			{
				$strDistinct = " DISTINCT ";
			}
		}

		if (
			isset($arFilter['TAG'])
			|| isset($arFilter['=TAG'])
			|| isset($arFilter['@TAG'])
		)
		{
			$arFields["TAG"] = array(
				"FIELD" => "SLT.NAME",
				"TYPE" => "string",
				"FROM" => "INNER JOIN b_sonet_log_tag SLT ON L.ID = SLT.LOG_ID"
			);

			$strDistinct = " DISTINCT ";
		}
		if (
			isset($arFilter['USER_ID|COMMENT_USER_ID'])
			|| isset($arFilter['*CONTENT'])
			|| isset($arFilter['*%CONTENT'])
		)
		{
			$strDistinct = " DISTINCT ";
		}
		if (
			isset($arFilter["TMP_ID"])
			&& !isset($arFilter["ID"])
		)
		{
			$arFilter["ID"] = $arFilter["TMP_ID"];
			unset($arFilter["TMP_ID"]);
		}

		if (isset($arParams["IS_CRM"]) && $arParams["IS_CRM"] == "Y")
		{
			$events = GetModuleEvents("socialnetwork", "OnFillSocNetLogFields");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array(&$arFields));
			}
		}

		$arFields = array_merge($arFields1, $arFields);
		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields, $obUserFieldsSql);

		$listEvents = GetModuleEvents("socialnetwork", "OnBuildSocNetLogSql");
		while ($arEvent = $listEvents->Fetch())
		{
			ExecuteModuleEventEx($arEvent, array(&$arFields, &$arOrder, &$arFilter, &$arGroupBy, &$arSelectFields, &$arSqls));
		}

		$strSqlUFFilter = "";
		$r = $obUserFieldsSql->GetFilter();
		if ($r <> '')
		{
			$strSqlUFFilter = " (".$r.") ";
		}

		$arSqls["RIGHTS"] = "";
		$arSqls["VIEW"] = "";
		$arSqls["CRM_RIGHTS"] = "";

		if (
			!empty($arParams)
			&& (
				(
					isset($arParams['CHECK_RIGHTS'])
					&& $arParams["CHECK_RIGHTS"] == "Y"
				)
				|| (
					isset($arParams['CHECK_CRM_RIGHTS'])
					&& $arParams["CHECK_CRM_RIGHTS"] == "Y"
				)
				|| (
					isset($arParams['CHECK_VIEW'])
					&& $arParams["CHECK_VIEW"] == "Y"
				)
			)
			&& !isset($arParams['USER_ID'])
			&& is_object($USER)
		)
		{
			$arParams["USER_ID"] = $USER->GetID();
		}

		if (
			!empty($arParams)
			&& isset($arParams['USER_ID'])
			&& (($arParams['CHECK_CRM_RIGHTS'] ?? '') !== 'Y')
		)
		{
			$arParams["CHECK_RIGHTS"] = "Y";
		}

		if (
			!empty($arParams)
			&& (
				(
					isset($arParams["USE_SUBSCRIBE"])
					&& $arParams["USE_SUBSCRIBE"] === "Y"
				)
				|| ($arParams["USE_FOLLOW"] ?? '') == "Y"
			)
		)
		{
			if (!isset($arParams['SUBSCRIBE_USER_ID']))
			{
				if (
					isset($arParams['USER_ID'])
					&& (int)$arParams["USER_ID"] > 0
				)
				{
					$arParams["SUBSCRIBE_USER_ID"] = $arParams["USER_ID"];
				}
				elseif (is_object($USER))
				{
					$arParams["SUBSCRIBE_USER_ID"] = $USER->GetID();
				}
			}

			if (
				isset($arParams["USE_SUBSCRIBE"])
				&& $arParams["USE_SUBSCRIBE"] === "Y"
				&& !isset($arParams['MY_ENTITIES'])
			)
			{
				$arMyEntities = array();
				foreach ($arSocNetAllowedSubscribeEntityTypesDesc as $entity_type_tmp => $arEntityTypeTmp)
				{
					if (
						isset($arEntityTypeTmp['HAS_MY'], $arEntityTypeTmp['CLASS_MY'], $arEntityTypeTmp['METHOD_MY'])
						&& $arEntityTypeTmp["HAS_MY"] == "Y"
						&& (string)$arEntityTypeTmp['CLASS_MY'] !== ''
						&& (string)$arEntityTypeTmp['METHOD_MY'] !== ''
						&& method_exists($arEntityTypeTmp["CLASS_MY"], $arEntityTypeTmp["METHOD_MY"])
					)
					{
						$arMyEntities[$entity_type_tmp] = call_user_func(array($arEntityTypeTmp["CLASS_MY"], $arEntityTypeTmp["METHOD_MY"]));
					}
				}

				$arParams["MY_ENTITIES"] = $arMyEntities;
			}
		}

		if (
			!empty($arParams)
			&& isset($arParams['CHECK_RIGHTS'], $arParams['USER_ID'])
			&& $arParams["CHECK_RIGHTS"] == "Y"
			&& is_object($USER)
		)
		{
			$acc = new CAccess;
			$acc->UpdateCodes();

			$arSqls["RIGHTS"] = "EXISTS ( SELECT SLR.ID FROM b_sonet_log_right SLR
				LEFT JOIN b_user_access UA ON (UA.ACCESS_CODE = SLR.GROUP_CODE AND UA.USER_ID = " . (int)$USER->GetID() . ")
				WHERE L.ID = SLR.LOG_ID ".
					(
						(
							$USER->IsAuthorized()
							&& isset($arParams["MY_GROUPS_ONLY"])
							&& $arParams["MY_GROUPS_ONLY"] === "Y"
						)
						?
							" AND (
								(SLR.GROUP_CODE LIKE 'SG%' AND (UA.ACCESS_CODE = SLR.GROUP_CODE AND UA.USER_ID = " . (int)$USER->GetID() . ")) 
								OR SLR.GROUP_CODE = 'U" . (int)$USER->GetID() . "'
							)"
						:
							" AND (
								0=1 ".
								(is_object($USER) && CSocNetUser::IsCurrentUserModuleAdmin() ? " OR SLR.GROUP_CODE = 'SA'" : "").
								(is_object($USER) && $USER->IsAuthorized() ? " OR (SLR.GROUP_CODE = 'AU')" : "").
								" OR (SLR.GROUP_CODE = 'G2')".
								(
									isset($arParams['CHECK_RIGHTS_OSG'])
									&& $arParams['CHECK_RIGHTS_OSG'] === 'Y'
									&& is_object($USER)
									&& $USER->IsAuthorized()
										? " OR (SLR.GROUP_CODE LIKE 'OSG%')"
										: ''
								).
								(!empty($arFilter['LOG_RIGHTS_SG']) && !is_array($arFilter['LOG_RIGHTS_SG']) ? " OR (SLR.GROUP_CODE = '".$DB->ForSQL($arFilter['LOG_RIGHTS_SG'])."')" : "").
								(is_object($USER) && $USER->IsAuthorized() ? " OR (UA.ACCESS_CODE = SLR.GROUP_CODE AND UA.USER_ID = ". (int)$USER->GetID() . ")" : "")."
							)"
					).")";
		}

		if (
			!empty($arParams)
			&& isset($arParams["CHECK_VIEW"], $arParams["USER_ID"])
			&& $arParams["CHECK_VIEW"] == "Y"
			&& (int)$arParams["USER_ID"] > 0
		)
		{
			$arSqls["VIEW"] = "NOT EXISTS ( SELECT SLV.USER_ID FROM b_sonet_log_view SLV
				WHERE
					L.EVENT_ID = SLV.EVENT_ID
					AND SLV.USER_ID = ". (int)$arParams["USER_ID"] . "
					AND SLV.TYPE = 'N'
				)";
		}

		if (
			!empty($arParams)
			&& isset($arParams['CHECK_CRM_RIGHTS'], $arParams['USER_ID'])
			&& $arParams["CHECK_CRM_RIGHTS"] == "Y"
			&& is_object($USER)
		)
		{
			$permParams = array(
				'ALIAS_PREFIX' => 'L',
				'PERM_TYPE' => 'READ',
				'FILTER_PARAMS' => ($arParams['CUSTOM_FILTER_PARAMS'] ?? []),
				'OPTIONS' => array(
					'ENTITY_TYPE_COLUMN' => 'ENTITY_TYPE',
					'IDENTITY_COLUMN' => 'ENTITY_ID'
				)
			);

			$altPerms = array();
			$events = GetModuleEvents("socialnetwork", "OnBuildSocNetLogPerms");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array(&$altPerms, $permParams));
			}

			if (!empty($altPerms))
			{
				foreach ($altPerms as $permSql)
				{
					if ($permSql === false)
					{
						//Access denied
						$dbRes = new CDBResult();
						$dbRes->InitFromArray(array());
						return $dbRes;
					}

					if (is_string($permSql) && $permSql !== '')
					{
						if ($arSqls['CRM_RIGHTS'] !== '')
						{
							$arSqls['CRM_RIGHTS'] .= ' AND ';
						}

						$arSqls['CRM_RIGHTS'] = $permSql;
					}
				}
			}
		}

		if (
			isset($arParams["USE_SUBSCRIBE"])
			&& $arParams["USE_SUBSCRIBE"] == "Y"
			&& (int)$arParams["SUBSCRIBE_USER_ID"] > 0
		)
		{
			$arSqls["SUBSCRIBE"] = CSocNetLogEvents::GetSQL(
				$arParams["SUBSCRIBE_USER_ID"],
				(is_array($arParams["MY_ENTITIES"]) ? $arParams["MY_ENTITIES"] : array()),
				$arParams["TRANSPORT"],
				$arParams["VISIBLE"]
			);
			$arParams["MIN_ID_JOIN"] = true;
		}

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", $strDistinct, $arSqls["SELECT"]);
		$strMinIDJoin = "";

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				$obUserFieldsSql->GetSelect()." ".
				"FROM b_sonet_log L ".
				$strMinIDJoin.
				"	".$arSqls["FROM"]." ".
				$obUserFieldsSql->GetJoin("L.ID")." ";

			$bWhereStarted = false;

			if (($arSqls["WHERE"] ?? '') <> '')
			{
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
				$bWhereStarted = true;
			}

			if ($strSqlUFFilter <> '')
			{
				$strSql .= ($bWhereStarted ? " AND " : " WHERE ").$strSqlUFFilter." ";
				$bWhereStarted = true;
			}

			if (($arSqls["RIGHTS"] ?? '') <> '')
			{
				$strSql .= ($bWhereStarted ? " AND " : " WHERE ").$arSqls["RIGHTS"]." ";
				$bWhereStarted = true;
			}

			if (($arSqls["VIEW"] ?? '') <> '')
			{
				$strSql .= ($bWhereStarted ? " AND " : " WHERE ").$arSqls["VIEW"]." ";
				$bWhereStarted = true;
			}

			if (($arSqls["CRM_RIGHTS"] ?? '') <> '')
			{
				$strSql .= ($bWhereStarted ? " AND " : " WHERE ").$arSqls["CRM_RIGHTS"]." ";
				$bWhereStarted = true;
			}

			if (($arSqls["SUBSCRIBE"] ?? '') <> '')
			{
				$strSql .= ($bWhereStarted ? " AND " : " WHERE ")."(".$arSqls["SUBSCRIBE"].") ";
			}
			if (($arSqls["GROUPBY"] ?? '') <> '')
			{
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
			}

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
			{
				return $arRes["CNT"];
			}

			return false;
		}

		if (
			!empty($arParams["FILTER_BY_CONTENT"])
			&& is_array($arParams["FILTER_BY_CONTENT"])
		)
		{
			$tmpFields = array(
				"CONTENT" => array("FIELD" => "CONTENT", "TYPE" => "string"),
				"DATE_CREATE" => array("FIELD" => "LI.DATE_CREATE", "TYPE" => "datetime"),
			);
			$tmpFilter = $arParams["FILTER_BY_CONTENT"];
			$tmpJoin = array();

			$strMinIDJoin = "INNER JOIN (
				SELECT LOG_ID, MAX(ITEM_TYPE) as ITEM_TYPE, MAX(ITEM_ID) as ITEM_ID
				FROM b_sonet_log_index TLI
				WHERE 
					".CSqlUtil::PrepareWhere($tmpFields, $tmpFilter, $tmpJoin)."
				GROUP BY LOG_ID
			) as TLI ON (TLI.LOG_ID = L.ID)
			INNER JOIN b_sonet_log_index LI ON (LI.ITEM_TYPE = TLI.ITEM_TYPE AND LI.ITEM_ID = TLI.ITEM_ID)";

			$arSqls["SELECT"] .= ',LI.ITEM_TYPE as CONTENT_ITEM_TYPE';
			$arSqls["SELECT"] .= ',LI.ITEM_ID as CONTENT_ITEM_ID';
			$arSqls["SELECT"] .= ',LI.LOG_UPDATE as CONTENT_LOG_UPDATE_X1';
			$arSqls["SELECT"] .= ','.$DB->DateToCharFunction('LI.LOG_UPDATE').' as CONTENT_LOG_UPDATE';
			$arSqls["SELECT"] .= ',LI.DATE_CREATE as CONTENT_DATE_CREATE_X1';
			$arSqls["SELECT"] .= ','.$DB->DateToCharFunction('LI.DATE_CREATE').' as CONTENT_DATE_CREATE';

			if (!empty($arParams["FILTER_BY_CONTENT_DATE"]))
			{
				$tmpFilter = $arParams["FILTER_BY_CONTENT_DATE"];
				$arSqls["WHERE"] .= (!empty($arSqls["WHERE"]) ? " AND " : "").CSqlUtil::PrepareWhere($tmpFields, $tmpFilter, $tmpJoin);
			}

			if (empty($arSqls["ORDERBY"]))
			{
				$arSqls["ORDERBY"] = " CONTENT_LOG_UPDATE_X1 DESC ";
			}
			else
			{
				$arSqls["ORDERBY"] = " CONTENT_LOG_UPDATE_X1 DESC, ".$arSqls["ORDERBY"];
			}
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			$obUserFieldsSql->GetSelect()." ".
			"FROM b_sonet_log L ".
			$strMinIDJoin.
			"	".$arSqls["FROM"]." ".
			$obUserFieldsSql->GetJoin("L.ID")." ";

		$bWhereStarted = false;

		if ($arSqls["WHERE"] <> '')
		{
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			$bWhereStarted = true;
		}

		if ($strSqlUFFilter <> '')
		{
			$strSql .= ($bWhereStarted ? " AND " : " WHERE ").$strSqlUFFilter." ";
			$bWhereStarted = true;
		}

		if ($arSqls["RIGHTS"] <> '')
		{
			$strSql .= ($bWhereStarted ? " AND " : " WHERE ").$arSqls["RIGHTS"]." ";
			$bWhereStarted = true;
		}

		if ($arSqls["VIEW"] <> '')
		{
			$strSql .= ($bWhereStarted ? " AND " : " WHERE ").$arSqls["VIEW"]." ";
			$bWhereStarted = true;
		}

		if (
			isset($arSqls["CRM_RIGHTS"])
			&& $arSqls["CRM_RIGHTS"] <> ''
		)
		{
			$strSql .= ($bWhereStarted ? " AND " : " WHERE ").$arSqls["CRM_RIGHTS"]." ";
			$bWhereStarted = true;
		}

		if (isset($arSqls["SUBSCRIBE"]) && $arSqls["SUBSCRIBE"] <> '')
		{
			$strSql .= ($bWhereStarted ? " AND " : " WHERE ")."(".$arSqls["SUBSCRIBE"].") ";
		}

		if ($arSqls["GROUPBY"] <> '')
		{
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		}

		if ($arSqls["ORDERBY"] <> '')
		{
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";
		}

		if (
			is_array($arNavStartParams)
			&& (int) ($arNavStartParams["nTopCount"] ?? 0) <= 0
		)
		{
			if (
				isset($arNavStartParams["nRecordCount"])
				&& (int)$arNavStartParams["nRecordCount"] > 0
			)
			{
				$cnt = (int)$arNavStartParams["nRecordCount"];
			}
			else
			{
				$strSql_tmp =
					"SELECT COUNT('x') as CNT ".
					$obUserFieldsSql->GetSelect()." ".
					"FROM b_sonet_log L ".
					$strMinIDJoin.
					"	".$arSqls["FROM"]." ".
					$obUserFieldsSql->GetJoin("L.ID")." ";

				$bWhereStarted = false;

				if ($arSqls["WHERE"] <> '')
				{
					$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
					$bWhereStarted = true;
				}

				if ($strSqlUFFilter <> '')
				{
					$strSql_tmp .= ($bWhereStarted ? " AND " : " WHERE ").$strSqlUFFilter." ";
					$bWhereStarted = true;
				}

				if (($arSqls["RIGHTS"] ?? '') <> '')
				{
					$strSql_tmp .= ($bWhereStarted ? " AND " : " WHERE ").$arSqls["RIGHTS"]." ";
					$bWhereStarted = true;
				}

				if (($arSqls["CRM_RIGHTS"] ?? '') <> '')
				{
					$strSql_tmp .= ($bWhereStarted ? " AND " : " WHERE ").$arSqls["CRM_RIGHTS"]." ";
					$bWhereStarted = true;
				}

				if (($arSqls["SUBSCRIBE"] ?? '') <> '')
				{
					$strSql_tmp .= ($bWhereStarted ? " AND " : " WHERE ")."(".$arSqls["SUBSCRIBE"].") ";
				}
				if (($arSqls["GROUPBY"] ?? '') <> '')
				{
					$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";
				}

				//echo "!2.1!=".htmlspecialcharsbx($strSql_tmp)."<br>";

				$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
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
					// ÒÎËÜÊÎ ÄËß MYSQL!!! ÄËß ORACLE ÄÐÓÃÎÉ ÊÎÄ
					$cnt = $dbRes->SelectedRowsCount();
				}

				// for empty 2nd page show
				if (
					($arNavStartParams["bSkipPageReset"] ?? null)
					&& $arNavStartParams["nPageSize"] >= $cnt
				)
				{
					$cnt = $arNavStartParams["nPageSize"] + $cnt;
				}
			}

			$dbRes = new CDBResult();
			//echo "!2.2!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes->SetUserFields($USER_FIELD_MANAGER->GetUserFields("SONET_LOG"));
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && (int)$arNavStartParams["nTopCount"] > 0)
			{
				$strSql .= "LIMIT ". (int)$arNavStartParams["nTopCount"];
			}
			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$dbRes->SetUserFields($USER_FIELD_MANAGER->GetUserFields("SONET_LOG"));
		}

		return $dbRes;
	}

	public static function DeleteSystemEventsByGroupID($group_id = false)
	{
		global $DB;

		$group_id = (int)$group_id;
		if ($group_id <= 0)
		{
			return false;
		}

		$DB->Query("DELETE LC FROM b_sonet_log_comment LC INNER JOIN (SELECT L.TMP_ID FROM b_sonet_log L WHERE L.ENTITY_TYPE = '".SONET_ENTITY_USER."' AND EVENT_ID = 'system_groups' AND MESSAGE = '".$group_id."') L1 ON LC.LOG_ID = L1.TMP_ID", true);
		$DB->Query("DELETE LS FROM b_sonet_log_site LS INNER JOIN (SELECT L.ID FROM b_sonet_log L WHERE L.ENTITY_TYPE = '".SONET_ENTITY_USER."' AND EVENT_ID = 'system_groups' AND MESSAGE = '".$group_id."') L1 ON LS.LOG_ID = L1.ID", true);
		$DB->Query("DELETE LR FROM b_sonet_log_right LR INNER JOIN (SELECT L.ID FROM b_sonet_log L WHERE L.ENTITY_TYPE = '".SONET_ENTITY_USER."' AND EVENT_ID = 'system_groups' AND MESSAGE = '".$group_id."') L1 ON LR.LOG_ID = L1.ID", true);
		$DB->Query("DELETE LF FROM b_sonet_log_favorites LF INNER JOIN (SELECT L.ID FROM b_sonet_log L WHERE L.ENTITY_TYPE = '".SONET_ENTITY_USER."' AND EVENT_ID = 'system_groups' AND MESSAGE = '".$group_id."') L1 ON LF.LOG_ID = L1.ID", true);

		return $DB->Query("DELETE FROM b_sonet_log WHERE ENTITY_TYPE = '".SONET_ENTITY_USER."' AND EVENT_ID = 'system_groups' AND MESSAGE = '".$group_id."'", true);
	}

	public static function Delete($ID)
	{
		global $DB, $APPLICATION, $USER_FIELD_MANAGER, $CACHE_MANAGER;

		$ID = (int)$ID;
		if ($ID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GL_WRONG_PARAMETER_ID"), "ERROR_NO_ID");
			return false;
		}

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetLogDelete");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				return false;
			}
		}

		$res = \Bitrix\Socialnetwork\LogCommentTable::getList([
			'filter' => [
				'=LOG_ID' => $ID
			],
			'select' => [ 'RATING_TYPE_ID', 'RATING_ENTITY_ID' ]
		]);
		while ($logCommentFields = $res->fetch())
		{
			CRatings::deleteRatingVoting([
				'ENTITY_TYPE_ID' => $logCommentFields['RATING_TYPE_ID'],
				'ENTITY_ID' => $logCommentFields['RATING_ENTITY_ID']
			]);
		}

		$DB->Query("DELETE LC FROM b_sonet_log_comment LC INNER JOIN (SELECT L.TMP_ID FROM b_sonet_log L WHERE L.ID = ".$ID.") L1 ON LC.LOG_ID = L1.TMP_ID", true);
		$DB->Query("DELETE FROM b_sonet_log_right WHERE LOG_ID = ".$ID, true);
		$DB->Query("DELETE FROM b_sonet_log_site WHERE LOG_ID = ".$ID, true);
		$DB->Query("DELETE FROM b_sonet_log_favorites WHERE LOG_ID = ".$ID, true);
		$DB->Query("DELETE FROM b_sonet_log_tag WHERE LOG_ID = ".$ID, true);

		$logFields = array();
		$res = LogTable::getList(array(
			'filter' => array(
				'ID' => $ID
			),
			'select' => array('EVENT_ID', 'SOURCE_ID', 'RATING_TYPE_ID', 'RATING_ENTITY_ID')
		));
		if ($fields = $res->fetch())
		{
			$logFields = $fields;
		}

		$bSuccess = $DB->Query("DELETE FROM b_sonet_log WHERE ID = ".$ID, true);

		if (
			$bSuccess
			&& !empty($logFields)
		)
		{
			$DB->Query("DELETE FROM ".UserContentViewTable::getTableName()." WHERE RATING_TYPE_ID = '".$DB->ForSQL($logFields['RATING_TYPE_ID'])."' AND RATING_ENTITY_ID = " . (int)$logFields['RATING_ENTITY_ID'], true);

			$USER_FIELD_MANAGER->Delete("SONET_LOG", $ID);

			$db_events = GetModuleEvents("socialnetwork", "OnSocNetLogDelete");
			while ($arEvent = $db_events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array($ID, $logFields));
			}

			LogIndex::deleteIndex(array(
				'itemType' => LogIndexTable::ITEM_TYPE_LOG,
				'itemId' => $ID
			));

			CRatings::deleteRatingVoting([
				'ENTITY_TYPE_ID' => $logFields['RATING_TYPE_ID'],
				'ENTITY_ID' => $logFields['RATING_ENTITY_ID']
			]);

			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->ClearByTag("SONET_LOG_".$ID);
			}

			$cache = new CPHPCache;
			$cache->CleanDir("/sonet/log/" . (int)($ID / 1000) . "/" . $ID . "/comments/");
		}

		return $bSuccess;
	}

	public static function DeleteNoDemand($userID): bool
	{
		global $DB;

		$userID = (int)$userID;
		if ($userID <= 0)
		{
			return false;
		}

		$DB->Query("DELETE LC FROM b_sonet_log_comment LC INNER JOIN (SELECT L.TMP_ID FROM b_sonet_log L WHERE L.ENTITY_TYPE = '".SONET_ENTITY_USER."' AND L.ENTITY_ID = ".$userID.") L1 ON LC.LOG_ID = L1.TMP_ID", true);
		$DB->Query("DELETE LS FROM b_sonet_log_site LS INNER JOIN (SELECT L.ID FROM b_sonet_log L WHERE L.ENTITY_TYPE = '".SONET_ENTITY_USER."' AND L.ENTITY_ID = ".$userID.") L1 ON LS.LOG_ID = L1.ID", true);
		$DB->Query("DELETE LR FROM b_sonet_log_right LR INNER JOIN (SELECT L.ID FROM b_sonet_log L WHERE L.ENTITY_TYPE = '".SONET_ENTITY_USER."' AND L.ENTITY_ID = ".$userID.") L1 ON LR.LOG_ID = L1.ID", true);
		$DB->Query("DELETE LF FROM b_sonet_log_favorites LF INNER JOIN (SELECT L.ID FROM b_sonet_log L WHERE L.ENTITY_TYPE = '".SONET_ENTITY_USER."' AND L.ENTITY_ID = ".$userID.") L1 ON LF.LOG_ID = L1.ID", true);
		$DB->Query("DELETE FROM b_sonet_log_favorites WHERE USER_ID = ".$userID, true);
		$DB->Query("DELETE FROM b_sonet_log_follow WHERE USER_ID = ".$userID, true);
		$DB->Query("DELETE FROM b_sonet_log_view WHERE USER_ID = ".$userID, true);

		$DB->Query("DELETE FROM b_sonet_log WHERE ENTITY_TYPE = '".SONET_ENTITY_USER."' AND ENTITY_ID = ".$userID, true);

		return true;
	}

	public static function OnBlogDelete($blog_id)
	{
		global $DB;
		return $DB->Query("DELETE SL FROM b_sonet_log SL INNER JOIN b_blog_post BP ON SL.SOURCE_ID = BP.ID AND BP.BLOG_ID = " . (int)$blog_id . " WHERE SL.EVENT_ID = 'blog_post_micro' OR SL.EVENT_ID = 'blog_post'", true);
	}
}
