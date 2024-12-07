<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/log_comments.php");

use Bitrix\Socialnetwork\Item\LogIndex;
use Bitrix\Socialnetwork\LogIndexTable;
use Bitrix\Socialnetwork\LogTagTable;
use Bitrix\Socialnetwork\Item\LogFollow;
use Bitrix\Socialnetwork\Util;

class CSocNetLogComments extends CAllSocNetLogComments
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function Add($arFields, $bSetSource = false, $bSendEvent = true, $bSetLogUpDate = true)
	{
		global $DB, $APPLICATION, $CACHE_MANAGER, $USER_FIELD_MANAGER;

		if (is_array($bSetSource))
		{
			$params = $bSetSource;
			$bSetSource = (isset($params['SET_SOURCE']) ? $params['SET_SOURCE'] : false);
			$bSendEvent = (isset($params['SEND_EVENT']) ? $params['SEND_EVENT'] : true);
			$bSetLogUpDate = (isset($params['SET_LOG_UPDATE']) ? $params['SET_LOG_UPDATE'] : true);
			$subscribe = (isset($params['SUBSCRIBE']) ? $params['SUBSCRIBE'] : true);
		}
		else
		{
			$subscribe = true;
		}

		$arFields1 = Util::getEqualityFields($arFields);

		if (
			$bSetSource
			&& $arFields["EVENT_ID"] <> '')
		{
			$arCommentEvent = CSocNetLogTools::FindLogCommentEventByID($arFields["EVENT_ID"]);
			if (
				!is_array($arCommentEvent)
				|| !array_key_exists("ADD_CALLBACK", $arCommentEvent)
				|| !is_callable($arCommentEvent["ADD_CALLBACK"])
			)
			{
				$bSetSource = false;
			}
		}

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetLogCommentAdd");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
			{
				return false;
			}
		}

		if ($bSetSource)
		{
			$arSource = CSocNetLogComments::SetSource($arFields);
			if (
				($arSource["NO_SOURCE"] ?? null) === "Y"
				|| intval($arSource["SOURCE_ID"]) > 0
			)
			{
				if (($arSource["NO_SOURCE"] ?? null) === "Y")
				{
					$bSetSource = false;
				}
				else
				{
					$arFields["SOURCE_ID"] = $arSource["SOURCE_ID"];
				}

				if (
					array_key_exists("RATING_ENTITY_ID", $arSource)
					&& array_key_exists("RATING_TYPE_ID", $arSource)
					&& intval($arSource["RATING_ENTITY_ID"]) > 0
					&& $arSource["RATING_TYPE_ID"] <> ''
				)
				{
					$arFields["RATING_TYPE_ID"] = $arSource["RATING_TYPE_ID"];
					$arFields["RATING_ENTITY_ID"] = $arSource["RATING_ENTITY_ID"];
				}

				if (isset($arSource["MESSAGE"]) && $arSource["MESSAGE"] <> '')
					$arFields["MESSAGE"] = $arSource["MESSAGE"];

				if (isset($arSource["TEXT_MESSAGE"]) && $arSource["TEXT_MESSAGE"] <> '')
					$arFields["TEXT_MESSAGE"] = $arSource["TEXT_MESSAGE"];

				if (isset($arSource["URL"]) && $arSource["URL"] <> '')
					$arFields["URL"] = $arSource["URL"];

				if (
					isset($arSource["UF"])
					&& isset($arSource["UF"]["FILE"])
				)
				{
					if (!is_array($arSource["UF"]["FILE"]))
						$arSource["UF"]["FILE"] = array($arSource["UF"]["FILE"]);

					$arFields["UF_SONET_COM_FILE"] = $arSource["UF"]["FILE"];
				}

				if (
					isset($arSource["UF"])
					&& isset($arSource["UF"]["DOC"])
				)
				{
					if (!is_array($arSource["UF"]["DOC"]))
						$arSource["UF"]["DOC"] = array($arSource["UF"]["DOC"]);

					$arFields["UF_SONET_COM_DOC"] = $arSource["UF"]["DOC"];
				}
			}
			else
			{
				$strMessage =
				(
					array_key_exists("ERROR", $arSource) && $arSource["ERROR"] <> ''
						? $arSource["ERROR"] :
						(
							array_key_exists("NOTES", $arSource)  && $arSource["NOTES"] <> ''
								? $arSource["NOTES"]
								: ""
						)
				);
			}
		}

		if (!CSocNetLogComments::CheckFields("ADD", $arFields))
		{
			if ($e = $APPLICATION->GetException())
			{
				$errorMessage = $e->GetString();
			}
			if ($errorMessage == '')
			{
				$errorMessage = GetMessage("SONET_GLC_ERROR_CHECKFIELDS_FAILED");
			}

			return array(
				"ID" => false,
				"MESSAGE" => $errorMessage
			);

			return false;
		}

		if (
			!$bSetSource
			|| (
				is_array($arSource)
				&& array_key_exists("SOURCE_ID", $arFields)
				&& intval($arFields["SOURCE_ID"]) > 0
			)
		)
		{
			$arInsert = $DB->PrepareInsert("b_sonet_log_comment", $arFields);
			\Bitrix\Socialnetwork\Util::processEqualityFieldsToInsert($arFields1, $arInsert);

			$ID = false;
			if ($arInsert[0] <> '')
			{
				$strSql =
					"INSERT INTO b_sonet_log_comment(".$arInsert[0].") ".
					"VALUES(".$arInsert[1].")";
				$DB->Query($strSql);

				$ID = intval($DB->LastID());

				if ($ID > 0)
				{
					if (
						!array_key_exists("RATING_TYPE_ID", $arFields)
						|| empty($arFields["RATING_TYPE_ID"])
					)
					{
						CSocNetLogComments::Update($ID, array(
							"RATING_TYPE_ID" => "LOG_COMMENT",
							"RATING_ENTITY_ID" => $ID
						));
					}

					if ($subscribe)
					{
						\Bitrix\Socialnetwork\ComponentHelper::userLogSubscribe([
							'logId' => $arFields["LOG_ID"],
							'userId' => $arFields["USER_ID"],
							'typeList' => [
								'FOLLOW',
								'COUNTER_COMMENT_PUSH'
							],
							'followDate' => 'CURRENT'
						]);
					}

					// subscribe log entry owner
					$rsLog = CSocNetLog::GetList(
						array(),
						array("ID" => $arFields["LOG_ID"]),
						false,
						false,
						array("ID", "USER_ID")
					);
					if (
						($arLog = $rsLog->Fetch())
						&& (intval($arLog["USER_ID"]) > 0)
					)
					{
						$default_follow = CSocNetLogFollow::GetDefaultValue($arLog["USER_ID"]);
						if ($default_follow != "Y")
						{
							$rsLogFollow = CSocNetLogFollow::GetList(
								array(
									"USER_ID" => $arLog["USER_ID"],
									"CODE" => "L".$arFields["LOG_ID"]
								),
								array("TYPE")
							);

							$arLogFollow = $rsLogFollow->Fetch();
							if (!$arLogFollow)
							{
								\Bitrix\Socialnetwork\ComponentHelper::userLogSubscribe(array(
									'logId' => $arFields["LOG_ID"],
									'userId' => $arLog["USER_ID"],
									'typeList' => array(
										'FOLLOW',
									)
								));
							}
						}
					}

					LogFollow::checkDestinationsFollowStatus(array(
						'logId' => $arFields["LOG_ID"]
					));

					if ($bSendEvent)
					{
						CSocNetLogComments::SendEvent($ID, "SONET_NEW_EVENT");
					}

					CSocNetLogComments::UpdateLogData($arFields["LOG_ID"], $bSetLogUpDate);

					$db_events = GetModuleEvents("socialnetwork", "OnAfterSocNetLogCommentAdd");
					while ($arEvent = $db_events->Fetch())
					{
						ExecuteModuleEventEx($arEvent, array($ID, $arFields));
					}

					$USER_FIELD_MANAGER->Update("SONET_COMMENT", $ID, $arFields);

					LogIndex::setIndex(array(
						'itemType' => LogIndexTable::ITEM_TYPE_COMMENT,
						'itemId' => $ID,
						'fields' => $arFields
					));

					if (isset($arFields["TAG"]))
					{
						LogTagTable::set(array(
							'itemType' => LogTagTable::ITEM_TYPE_COMMENT,
							'itemId' => $ID,
							'tags' => $arFields["TAG"]
						));
					}

					if(defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->ClearByTag("SONET_LOG_".$arFields["LOG_ID"]);
					}

					$cache = new CPHPCache;
					$cache->CleanDir("/sonet/log/".intval(intval($arFields["LOG_ID"]) / 1000)."/".$arFields["LOG_ID"]."/comments/");

					CSocNetLogComments::SendMentionNotification(array_merge($arFields, array("ID" => $ID)));
				}
			}

			CSocNetLogTools::SetCacheLastLogID("comment", $ID);

			\Bitrix\Socialnetwork\Internals\EventService\Service::addEvent(
				\Bitrix\Socialnetwork\Internals\EventService\EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_ADD,
				[
					'SONET_LOG_COMMENT_ID' => $ID,
					'SONET_LOG_ID' => $arFields['LOG_ID'],
					'USER_ID' => $arFields['USER_ID'],
					'EVENT_ID' => $arFields['EVENT_ID'] ?? null,
				]
			);

			return $ID;
		}
		elseif ($bSetSource && $strMessage <> '')
			return array(
				"ID" => false,
				"MESSAGE" => $strMessage
			);
		else
			return false;
	}

	public static function Update($ID, $arFields, $bSetSource = false)
	{
		global $DB, $APPLICATION, $USER_FIELD_MANAGER;

		$ID = intval($ID);
		if ($ID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_LC_WRONG_PARAMETER_ID"), "ERROR_NO_ID");
			return false;
		}

		if (
			(
				$bSetSource
				&& !isset($arFields["SOURCE_ID"])
			)
			|| !isset($arFields["LOG_ID"])
		)
		{
			$rsRes = CSocNetLogComments::getList(
				array(),
				array("ID" => $ID),
				false,
				false,
				array("LOG_ID", "SOURCE_ID")
			);
			if ($arRes = $rsRes->fetch())
			{
				$arFields["SOURCE_ID"] = $arRes["SOURCE_ID"];
				$arFields["LOG_ID"] = $arRes["LOG_ID"];
			}

			if (!isset($arFields["SOURCE_ID"]))
			{
				$bSetSource = false;
			}
		}

		if ($bSetSource)
		{
			if ($arFields["EVENT_ID"] <> '')
			{
				$arCommentEvent = CSocNetLogTools::FindLogCommentEventByID($arFields["EVENT_ID"]);
				if (
					!is_array($arCommentEvent)
					|| !array_key_exists("UPDATE_CALLBACK", $arCommentEvent)
					|| !is_callable($arCommentEvent["UPDATE_CALLBACK"])
				)
				{
					$bSetSource = false;
				}
			}
		}

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		$arSource = false;
		if ($bSetSource)
		{
			$arSource = CSocNetLogComments::SetSource($arFields, "UPDATE");

			if (
				isset($arSource["NO_SOURCE"])
				&& $arSource["NO_SOURCE"] == "Y"
			)
			{
				$bSetSource = false;
			}
			elseif (
				is_array($arSource)
				&& (
					isset($arSource["ERROR"])
					&& !empty($arSource["ERROR"])
				)
			)
			{
				return array(
					"ID" => false,
					"MESSAGE" => $arSource["ERROR"]
				);
			}
			else
			{
				if (
					isset($arSource["MESSAGE"])
					&& $arSource["MESSAGE"] <> ''
				)
				{
					$arFields["MESSAGE"] = $arSource["MESSAGE"];
				}

				if (
					isset($arSource["TEXT_MESSAGE"])
					&& $arSource["TEXT_MESSAGE"] <> ''
				)
				{
					$arFields["TEXT_MESSAGE"] = $arSource["TEXT_MESSAGE"];
				}

				if (
					isset($arSource["UF"])
					&& isset($arSource["UF"]["FILE"])
				)
				{
					if (!is_array($arSource["UF"]["FILE"]))
					{
						$arSource["UF"]["FILE"] = array($arSource["UF"]["FILE"]);
					}

					$arFields["UF_SONET_COM_FILE"] = $arSource["UF"]["FILE"];
				}

				if (
					isset($arSource["UF"])
					&& isset($arSource["UF"]["DOC"])
				)
				{
					if (!is_array($arSource["UF"]["DOC"]))
					{
						$arSource["UF"]["DOC"] = array($arSource["UF"]["DOC"]);
					}

					$arFields["UF_SONET_COM_DOC"] = $arSource["UF"]["DOC"];
				}
			}
		}

		if (!CSocNetLogComments::CheckFields("UPDATE", $arFields, $ID))
		{
			return false;
		}

		if (
			!$bSetSource
			|| (
				is_array($arSource)
				&& (
					!isset($arSource["ERROR"])
					|| empty($arSource["ERROR"])
				)
			)
		)
		{
			$strUpdate = $DB->PrepareUpdate("b_sonet_log_comment", $arFields);
			\Bitrix\Socialnetwork\Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

			if ($strUpdate <> '')
			{
				$strSql =
					"UPDATE b_sonet_log_comment SET ".
					"	".$strUpdate." ".
					"WHERE ID = ".$ID." ";
				$DB->Query($strSql);

				$USER_FIELD_MANAGER->Update("SONET_COMMENT", $ID, $arFields);

				$cache = new CPHPCache;
				$cache->CleanDir("/sonet/log/".intval(intval($arFields["LOG_ID"]) / 1000)."/".$arFields["LOG_ID"]."/comments/");
			}
			elseif (!$USER_FIELD_MANAGER->Update("SONET_COMMENT", $ID, $arFields))
			{
				$ID = False;
			}

			if (intval($ID) > 0)
			{
				$events = GetModuleEvents("socialnetwork", "OnAfterSocNetLogCommentUpdate");
				while ($arEvent = $events->Fetch())
				{
					ExecuteModuleEventEx($arEvent, array($ID, $arFields));
				}

				if (!empty($arFields['MESSAGE']))
				{
					LogIndex::setIndex(array(
						'itemType' => LogIndexTable::ITEM_TYPE_COMMENT,
						'itemId' => $ID,
						'fields' => $arFields
					));
				}

				if (isset($arFields["TAG"]))
				{
					LogTagTable::set(array(
						'itemType' => LogTagTable::ITEM_TYPE_COMMENT,
						'itemId' => $ID,
						'tags' => $arFields["TAG"]
					));
				}
			}

			\Bitrix\Socialnetwork\Internals\EventService\Service::addEvent(
				\Bitrix\Socialnetwork\Internals\EventService\EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_UPD,
				[
					'SONET_LOG_COMMENT_ID' => $ID,
					'SONET_LOG_ID' => $arFields['LOG_ID'],
					'USER_ID' => $arFields['USER_ID'] ?? null,
				]
			);

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
	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array(), $arParams = array())
	{
		global $DB, $arSocNetAllowedEntityTypes, $USER, $USER_FIELD_MANAGER;

		$arSocNetAllowedSubscribeEntityTypesDesc = CSocNetAllowed::GetAllowedEntityTypesDesc();

		$obUserFieldsSql = new CUserTypeSQL;
		$obUserFieldsSql->SetEntity("SONET_COMMENT", "LC.ID");
		$obUserFieldsSql->SetSelect($arSelectFields);
		$obUserFieldsSql->SetFilter($arFilter);
		$obUserFieldsSql->SetOrder($arOrder);

		if (count($arSelectFields) <= 0)
			$arSelectFields = array(
				"ID", "LOG_ID", "SOURCE_ID", "ENTITY_TYPE", "ENTITY_ID", "USER_ID", "EVENT_ID", "LOG_DATE", "MESSAGE", "TEXT_MESSAGE", "URL", "MODULE_ID",
				"GROUP_NAME", "GROUP_OWNER_ID", "GROUP_VISIBLE", "GROUP_OPENED", "GROUP_IMAGE_ID",
				"USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER",
				"CREATED_BY_NAME", "CREATED_BY_LAST_NAME", "CREATED_BY_SECOND_NAME", "CREATED_BY_LOGIN", "CREATED_BY_PERSONAL_PHOTO", "CREATED_BY_PERSONAL_GENDER",
				"LOG_SITE_ID", "LOG_SOURCE_ID", "LOG_USER_ID",
				"RATING_TYPE_ID", "RATING_ENTITY_ID", "RATING_TOTAL_VALUE", "RATING_TOTAL_VOTES", "RATING_TOTAL_POSITIVE_VOTES", "RATING_TOTAL_NEGATIVE_VOTES", "RATING_USER_VOTE_VALUE"
			);

		static $arFields1 = array(
			"ID" => Array("FIELD" => "LC.ID", "TYPE" => "int"),
			"LOG_ID" => Array("FIELD" => "LC.LOG_ID", "TYPE" => "int"),
			"SOURCE_ID" => Array("FIELD" => "LC.SOURCE_ID", "TYPE" => "int"),
			"ENTITY_TYPE" => Array("FIELD" => "LC.ENTITY_TYPE", "TYPE" => "string"),
			"ENTITY_ID" => Array("FIELD" => "LC.ENTITY_ID", "TYPE" => "int"),
			"USER_ID" => Array("FIELD" => "LC.USER_ID", "TYPE" => "int"),
			"EVENT_ID" => Array("FIELD" => "LC.EVENT_ID", "TYPE" => "string"),
			"LOG_DATE" => Array("FIELD" => "LC.LOG_DATE", "TYPE" => "datetime"),
			"LOG_DATE_TS" => Array("FIELD" => "UNIX_TIMESTAMP(LC.LOG_DATE)", "TYPE" => "int"),
			"TITLE" => Array("FIELD" => "LC.TITLE", "TYPE" => "string"),
			"MESSAGE" => Array("FIELD" => "LC.MESSAGE", "TYPE" => "string"),
			"TEXT_MESSAGE" => Array("FIELD" => "LC.TEXT_MESSAGE", "TYPE" => "string"),
			"URL" => Array("FIELD" => "LC.URL", "TYPE" => "string"),
			"MODULE_ID" => Array("FIELD" => "LC.MODULE_ID", "TYPE" => "string"),
			"LOG_SOURCE_ID" => Array("FIELD" => "L.SOURCE_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sonet_log L ON (LC.LOG_ID = L.ID)"),
			"LOG_USER_ID" => Array("FIELD" => "L.USER_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sonet_log L ON (LC.LOG_ID = L.ID)"),
			"LOG_TITLE" => Array("FIELD" => "L1.TITLE", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_log L1 ON (LC.LOG_ID = L1.ID)"),
			"LOG_URL" => Array("FIELD" => "L1.URL", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_log L1 ON (LC.LOG_ID = L1.ID)"),
			"LOG_PARAMS" => Array("FIELD" => "L1.PARAMS", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_log L1 ON (LC.LOG_ID = L1.ID)"),
			"GROUP_NAME" => Array("FIELD" => "G.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_group G ON (LC.ENTITY_TYPE = 'G' AND LC.ENTITY_ID = G.ID)"),
			"GROUP_OWNER_ID" => Array("FIELD" => "G.OWNER_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sonet_group G ON (LC.ENTITY_TYPE = 'G' AND LC.ENTITY_ID = G.ID)"),
			"GROUP_VISIBLE" => Array("FIELD" => "G.VISIBLE", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_group G ON (LC.ENTITY_TYPE = 'G' AND LC.ENTITY_ID = G.ID)"),
			"GROUP_OPENED" => Array("FIELD" => "G.OPENED", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_group G ON (LC.ENTITY_TYPE = 'G' AND LC.ENTITY_ID = G.ID)"),
			"GROUP_IMAGE_ID" => Array("FIELD" => "G.IMAGE_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sonet_group G ON (LC.ENTITY_TYPE = 'G' AND LC.ENTITY_ID = G.ID)"),
			"USER_NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (LC.ENTITY_TYPE = 'U' AND LC.ENTITY_ID = U.ID)"),
			"USER_LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (LC.ENTITY_TYPE = 'U' AND LC.ENTITY_ID = U.ID)"),
			"USER_SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (LC.ENTITY_TYPE = 'U' AND LC.ENTITY_ID = U.ID)"),
			"USER_LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (LC.ENTITY_TYPE = 'U' AND LC.ENTITY_ID = U.ID)"),
			"USER_PERSONAL_PHOTO" => Array("FIELD" => "U.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "LEFT JOIN b_user U ON (LC.ENTITY_TYPE = 'U' AND LC.ENTITY_ID = U.ID)"),
			"USER_PERSONAL_GENDER" => Array("FIELD" => "U.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (LC.ENTITY_TYPE = 'U' AND LC.ENTITY_ID = U.ID)"),
			"CREATED_BY_NAME" => Array("FIELD" => "U1.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON LC.USER_ID = U1.ID"),
			"CREATED_BY_LAST_NAME" => Array("FIELD" => "U1.LAST_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON LC.USER_ID = U1.ID"),
			"CREATED_BY_SECOND_NAME" => Array("FIELD" => "U1.SECOND_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON LC.USER_ID = U1.ID"),
			"CREATED_BY_LOGIN" => Array("FIELD" => "U1.LOGIN", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON LC.USER_ID = U1.ID"),
			"CREATED_BY_PERSONAL_PHOTO" => Array("FIELD" => "U1.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "LEFT JOIN b_user U1 ON LC.USER_ID = U1.ID"),
			"CREATED_BY_PERSONAL_GENDER" => Array("FIELD" => "U1.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON LC.USER_ID = U1.ID"),
			"CREATED_BY_EXTERNAL_AUTH_ID" => Array("FIELD" => "U1.EXTERNAL_AUTH_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON LC.USER_ID = U1.ID"),
			"SHARE_DEST" => Array("FIELD" => "LC.SHARE_DEST", "TYPE" => "string"),
		);

		if (array_key_exists("LOG_SITE_ID", $arFilter))
		{
			$arFields["LOG_SITE_ID"] = Array("FIELD" => "SLS.SITE_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_log_site SLS ON LC.LOG_ID = SLS.LOG_ID");
			$strDistinct = " DISTINCT ";
			foreach ($arSelectFields as $i => $strFieldTmp)
				if ($strFieldTmp == "LOG_SITE_ID")
					unset($arSelectFields[$i]);

			foreach ($arOrder as $by => $order)
				if (!in_array($by, $arSelectFields))
					$arSelectFields[] = $by;
		}
		else
		{
			$arFields["LOG_SITE_ID"] = Array("FIELD" => "L.SITE_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_log L ON (LC.LOG_ID = L.ID)");
			$strDistinct = " ";
		}

		$arFields["RATING_TYPE_ID"] = Array("FIELD" => "LC.RATING_TYPE_ID", "TYPE" => "string");
		$arFields["RATING_ENTITY_ID"] = Array("FIELD" => "LC.RATING_ENTITY_ID", "TYPE" => "int");
		$arFields["RATING_TOTAL_VALUE"] = Array("FIELD" => $DB->IsNull('RG.TOTAL_VALUE', '0'), "TYPE" => "double", "FROM" => "LEFT JOIN b_rating_voting RG ON LC.RATING_TYPE_ID = RG.ENTITY_TYPE_ID AND LC.RATING_ENTITY_ID = RG.ENTITY_ID");
		$arFields["RATING_TOTAL_VOTES"] = Array("FIELD" => $DB->IsNull('RG.TOTAL_VOTES', '0'), "TYPE" => "double", "FROM" => "LEFT JOIN b_rating_voting RG ON LC.RATING_TYPE_ID = RG.ENTITY_TYPE_ID AND LC.RATING_ENTITY_ID = RG.ENTITY_ID");
		$arFields["RATING_TOTAL_POSITIVE_VOTES"] = Array("FIELD" => $DB->IsNull('RG.TOTAL_POSITIVE_VOTES', '0'), "TYPE" => "int", "FROM" => "LEFT JOIN b_rating_voting RG ON LC.RATING_TYPE_ID = RG.ENTITY_TYPE_ID AND LC.RATING_ENTITY_ID = RG.ENTITY_ID");
		$arFields["RATING_TOTAL_NEGATIVE_VOTES"] = Array("FIELD" => $DB->IsNull('RG.TOTAL_NEGATIVE_VOTES', '0'), "TYPE" => "int", "FROM" => "LEFT JOIN b_rating_voting RG ON LC.RATING_TYPE_ID = RG.ENTITY_TYPE_ID AND LC.RATING_ENTITY_ID = RG.ENTITY_ID");
		if (isset($USER) && is_object($USER))
			$arFields["RATING_USER_VOTE_VALUE"] = Array("FIELD" => $DB->IsNull('RV.VALUE', '0'), "TYPE" => "double", "FROM" => "LEFT JOIN b_rating_vote RV ON LC.RATING_TYPE_ID = RV.ENTITY_TYPE_ID AND LC.RATING_ENTITY_ID = RV.ENTITY_ID AND RV.USER_ID = ".intval($USER->GetId()));

		if (array_key_exists("LOG_RIGHTS", $arFilter))
		{
			$Rights = array();
			if(is_array($arFilter["LOG_RIGHTS"]))
			{
				foreach($arFilter["LOG_RIGHTS"] as $str)
					if(trim($str))
						$Rights[] = trim($str);
			}
			elseif(trim($arFilter["LOG_RIGHTS"]))
				$Rights = trim($arFilter["LOG_RIGHTS"]);

			unset($arFilter["LOG_RIGHTS"]);
			if((is_array($Rights) && !empty($Rights)) || !is_array($Rights))
			{
				$arFilter["LOG_RIGHTS"] = $Rights;
				$arFields["LOG_RIGHTS"] = Array("FIELD" => "SLR0.GROUP_CODE", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_log_right SLR0 ON LC.LOG_ID = SLR0.LOG_ID");
			}

			if(is_array($Rights) && count($Rights) > 1)
				$strDistinct = " DISTINCT ";
		}

		$arFields = array_merge($arFields1, $arFields);

		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields, $obUserFieldsSql);

		$strSqlUFFilter = '';
		$r = $obUserFieldsSql->GetFilter();
		if($r <> '')
			$strSqlUFFilter = " (".$r.") ";

		$arSqls["RIGHTS"] = "";
		$arSqls["CRM_RIGHTS"] = "";
		$arSqls["SUBSCRIBE"] = "";

		if (
			!empty($arParams)
			&& array_key_exists("CHECK_RIGHTS", $arParams)
			&& $arParams["CHECK_RIGHTS"] == "Y"
			&& !array_key_exists("USER_ID", $arParams)
			&& is_object($USER)
		)
			$arParams["USER_ID"] = $USER->GetID();

		if (
			!empty($arParams)
			&& array_key_exists("USER_ID", $arParams)
		)
			$arParams["CHECK_RIGHTS"] = "Y";

		if (
			!empty($arParams)
			&& array_key_exists("USE_SUBSCRIBE", $arParams)
			&& $arParams["USE_SUBSCRIBE"] == "Y"
		)
		{
			if (!array_key_exists("SUBSCRIBE_USER_ID", $arParams))
			{
				if (
					array_key_exists("USER_ID", $arParams)
					&& intval($arParams["USER_ID"]) > 0
				)
					$arParams["SUBSCRIBE_USER_ID"] = $arParams["USER_ID"];
				else
					$arParams["SUBSCRIBE_USER_ID"] = $USER->GetID();
			}

			if (!array_key_exists("MY_ENTITIES", $arParams))
			{
				foreach($arSocNetAllowedSubscribeEntityTypesDesc as $entity_type_tmp => $arEntityTypeTmp)
					if (
						array_key_exists("HAS_MY", $arEntityTypeTmp)
						&& $arEntityTypeTmp["HAS_MY"] == "Y"
						&& array_key_exists("CLASS_MY", $arEntityTypeTmp)
						&& array_key_exists("METHOD_MY", $arEntityTypeTmp)
						&& $arEntityTypeTmp["CLASS_MY"] <> ''
						&& $arEntityTypeTmp["METHOD_MY"] <> ''
						&& method_exists($arEntityTypeTmp["CLASS_MY"], $arEntityTypeTmp["METHOD_MY"])
					)
						$arMyEntities[$entity_type_tmp] = call_user_func(array($arEntityTypeTmp["CLASS_MY"], $arEntityTypeTmp["METHOD_MY"]));

				$arParams["MY_ENTITIES"] = $arMyEntities;
			}
		}

		if (
			!empty($arParams)
			&& array_key_exists("CHECK_RIGHTS", $arParams)
			&& $arParams["CHECK_RIGHTS"] == "Y"
			&& array_key_exists("USER_ID", $arParams)
		)
		{
			$acc = new CAccess;
			$acc->UpdateCodes();

			$arSqls["RIGHTS"] = "EXISTS ( SELECT SLR.ID FROM b_sonet_log_right SLR
				LEFT JOIN b_user_access UA ON (UA.ACCESS_CODE = SLR.GROUP_CODE AND UA.USER_ID = ".(is_object($USER)? intval($USER->GetID()): 0).")
				WHERE LC.LOG_ID = SLR.LOG_ID AND (0=1 ".
				(is_object($USER) && CSocNetUser::IsCurrentUserModuleAdmin() ? " OR SLR.GROUP_CODE = 'SA'" : "").
				(is_object($USER) && $USER->IsAuthorized() ? " OR (SLR.GROUP_CODE = 'AU')" : "").
				" OR (SLR.GROUP_CODE = 'G2')".
				(is_object($USER) && $USER->IsAuthorized() ? " OR (UA.ACCESS_CODE = SLR.GROUP_CODE AND UA.USER_ID = ".$USER->GetID().")" : "")."))";
		}

		if (
			!empty($arParams)
			&& array_key_exists("CHECK_CRM_RIGHTS", $arParams)
			&& $arParams["CHECK_CRM_RIGHTS"] == "Y"
			&& array_key_exists("USER_ID", $arParams)
		)
		{
			$arSqls["CRM_RIGHTS"] = "";
		}

		if (
			($arParams["USE_SUBSCRIBE"] ?? '') == "Y"
			&& intval($arParams["SUBSCRIBE_USER_ID"]) > 0
		)
		{
			$arSqls["SUBSCRIBE"] = CSocNetLogEvents::GetSQL(
				$arParams["SUBSCRIBE_USER_ID"],
				(is_array($arParams["MY_ENTITIES"]) ? $arParams["MY_ENTITIES"] : array()),
				$arParams["TRANSPORT"],
				$arParams["VISIBLE"],
				"LC"
			);
		}

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", $strDistinct, $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				$obUserFieldsSql->GetSelect()." ".
				"FROM b_sonet_log_comment LC ".
				"	".$arSqls["FROM"]." ".
				$obUserFieldsSql->GetJoin("LC.ID")." ";

			$bWhereStarted = false;

			if ($arSqls["WHERE"] <> '')
			{
				$strSql .= "WHERE ".$arSqls["WHERE"]." ".($arSqls["SUBSCRIBE"] <> '' ? "AND (".$arSqls["SUBSCRIBE"].") " : "");
				$bWhereStarted = true;
			}
			elseif ($arSqls["SUBSCRIBE"] <> '')
			{
				$strSql .= "WHERE (".$arSqls["SUBSCRIBE"].") ";
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

			if ($arSqls["CRM_RIGHTS"] <> '')
			{
				$strSql .= ($bWhereStarted ? " AND " : " WHERE ").$arSqls["CRM_RIGHTS"]." ";
				$bWhereStarted = true;
			}

			if ($arSqls["GROUPBY"] <> '')
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			$obUserFieldsSql->GetSelect()." ".
			"FROM b_sonet_log_comment LC ".
			"	".$arSqls["FROM"]." ".
			$obUserFieldsSql->GetJoin("LC.ID")." ";

		$bWhereStarted = false;

		if ($arSqls["WHERE"] <> '')
		{
			$strSql .= "WHERE ".$arSqls["WHERE"]." ".($arSqls["SUBSCRIBE"] <> '' ? "AND (".$arSqls["SUBSCRIBE"].") " : "");
			$bWhereStarted = true;
		}
		elseif ($arSqls["SUBSCRIBE"] <> '')
		{
			$strSql .= "WHERE (".$arSqls["SUBSCRIBE"].") ";
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

		if ($arSqls["CRM_RIGHTS"] <> '')
		{
			$strSql .= ($bWhereStarted ? " AND " : " WHERE ").$arSqls["CRM_RIGHTS"]." ";
			$bWhereStarted = true;
		}

		if ($arSqls["GROUPBY"] <> '')
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if ($arSqls["ORDERBY"] <> '')
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (
			is_array($arNavStartParams)
			&& (int) ($arNavStartParams["nTopCount"] ?? 0) <= 0
		)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sonet_log_comment LC ".
				"	".$arSqls["FROM"]." ".
				$obUserFieldsSql->GetJoin("LC.ID")." ";

			$bWhereStarted = false;

			if ($arSqls["WHERE"] <> '')
			{
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ".($arSqls["SUBSCRIBE"] <> '' ? "AND (".$arSqls["SUBSCRIBE"].") " : "");
				$bWhereStarted = true;
			}
			elseif ($arSqls["SUBSCRIBE"] <> '')
			{
				$strSql_tmp .= "WHERE (".$arSqls["SUBSCRIBE"].") ";
				$bWhereStarted = true;
			}

			if ($strSqlUFFilter <> '')
			{
				$strSql_tmp .= ($bWhereStarted ? " AND " : " WHERE ").$strSqlUFFilter." ";
				$bWhereStarted = true;
			}

			if ($arSqls["RIGHTS"] <> '')
			{
				$strSql_tmp .= ($bWhereStarted ? " AND " : " WHERE ").$arSqls["RIGHTS"]." ";
				$bWhereStarted = true;
			}

			if ($arSqls["CRM_RIGHTS"] <> '')
			{
				$strSql_tmp .= ($bWhereStarted ? " AND " : " WHERE ").$arSqls["CRM_RIGHTS"]." ";
				$bWhereStarted = true;
			}

			if ($arSqls["GROUPBY"] <> '')
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialcharsbx($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp);
			$cnt = 0;
			if ($arSqls["GROUPBY"] == '')
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				// MYSQL only, ORACLE has another code
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			//echo "!2.2!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes->SetUserFields($USER_FIELD_MANAGER->GetUserFields("SONET_COMMENT"));
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".intval($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";
			$dbRes = $DB->Query($strSql);
			$dbRes->SetUserFields($USER_FIELD_MANAGER->GetUserFields("SONET_COMMENT"));
		}

		return $dbRes;
	}

	public static function OnBlogDelete($blog_id)
	{
		global $DB;

		return $DB->Query("DELETE SLC FROM b_sonet_log_comment SLC INNER JOIN b_blog_comment BC ON SLC.SOURCE_ID = BC.ID AND BC.BLOG_ID = ".intval($blog_id)." WHERE SLC.EVENT_ID = 'blog_comment_micro' OR SLC.EVENT_ID = 'blog_comment'", true);
	}
}
