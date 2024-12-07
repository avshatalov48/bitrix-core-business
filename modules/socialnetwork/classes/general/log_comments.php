<?php

IncludeModuleLangFile(__FILE__);

use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Item\LogIndex;
use Bitrix\Socialnetwork\LogTable;
use Bitrix\Socialnetwork\LogCommentTable;
use Bitrix\Socialnetwork\LogIndexTable;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Socialnetwork\LogTagTable;

class CAllSocNetLogComments
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		static $arSiteWorkgroupsPage;

		global $DB, $USER_FIELD_MANAGER, $APPLICATION;

		if (
			!$arSiteWorkgroupsPage
			&& IsModuleInstalled("extranet")
			&& ($arFields["ENTITY_TYPE"] ?? null) == SONET_ENTITY_GROUP
		)
		{
			$rsSite = CSite::GetList("sort", "desc", Array("ACTIVE" => "Y"));
			while($arSite = $rsSite->Fetch())
			{
				$arSiteWorkgroupsPage[$arSite["ID"]] = COption::GetOptionString("socialnetwork", "workgroups_page", $arSite["DIR"]."workgroups/", $arSite["ID"]);
			}
		}

		if ($ACTION != "ADD" && intval($ID) <= 0)
		{
			$APPLICATION->ThrowException("System error 870164", "ERROR");
			return false;
		}

		$newEntityType = "";

		if ((is_set($arFields, "ENTITY_TYPE") || $ACTION=="ADD") && $arFields["ENTITY_TYPE"] == '')
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GLC_EMPTY_ENTITY_TYPE"), "EMPTY_ENTITY_TYPE");
			return false;
		}
		elseif (is_set($arFields, "ENTITY_TYPE"))
		{
			if (!in_array($arFields["ENTITY_TYPE"], CSocNetAllowed::GetAllowedEntityTypes()))
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GLC_ERROR_NO_ENTITY_TYPE"), "ERROR_NO_ENTITY_TYPE");
				return false;
			}

			$newEntityType = $arFields["ENTITY_TYPE"];
		}

		if ((is_set($arFields, "ENTITY_ID") || $ACTION=="ADD") && intval($arFields["ENTITY_ID"]) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GLC_EMPTY_ENTITY_ID"), "EMPTY_ENTITY_ID");
			return false;
		}
		elseif (is_set($arFields, "ENTITY_ID"))
		{
			if ($newEntityType == '' && $ID > 0)
			{
				$arRe = CAllSocNetLog::GetByID($ID);
				if ($arRe)
				{
					$newEntityType = $arRe["ENTITY_TYPE"];
				}
			}
			if ($newEntityType == '')
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GL_ERROR_CALC_ENTITY_TYPE"), "ERROR_CALC_ENTITY_TYPE");
				return false;
			}

			if ($newEntityType == SONET_ENTITY_GROUP)
			{
				$arResult = CSocNetGroup::GetByID($arFields["ENTITY_ID"]);
				if ($arResult == false)
				{
					$APPLICATION->ThrowException(GetMessage("SONET_GLC_ERROR_NO_ENTITY_ID"), "ERROR_NO_ENTITY_ID");
					return false;
				}
			}
			elseif ($newEntityType == SONET_ENTITY_USER)
			{
				$dbResult = CUser::GetByID($arFields["ENTITY_ID"]);
				if (!$dbResult->Fetch())
				{
					$APPLICATION->ThrowException(GetMessage("SONET_GLC_ERROR_NO_ENTITY_ID"), "ERROR_NO_ENTITY_ID");
					return false;
				}
			}
		}

		if ((is_set($arFields, "LOG_ID") || $ACTION=="ADD") && intval($arFields["LOG_ID"]) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage(\Bitrix\Main\ModuleManager::isModuleInstalled('intranet') ? "SONET_GLC_EMPTY_LOG_ID2" : "SONET_GLC_EMPTY_LOG_ID"), "EMPTY_LOG_ID");
			return false;
		}

		if ((is_set($arFields, "EVENT_ID") || $ACTION=="ADD") && $arFields["EVENT_ID"] == '')
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GLC_EMPTY_EVENT_ID"), "EMPTY_EVENT_ID");
			return false;
		}
		elseif (is_set($arFields, "EVENT_ID"))
		{
			$arFields["EVENT_ID"] = mb_strtolower($arFields["EVENT_ID"]);
			$arEvent = CSocNetLogTools::FindLogCommentEventByID($arFields["EVENT_ID"]);
			if (!$arEvent)
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GLC_ERROR_NO_FEATURE_ID"), "ERROR_NO_FEATURE");
				return false;
			}
		}

		if (is_set($arFields, "USER_ID"))
		{
			$dbResult = CUser::GetByID($arFields["USER_ID"]);
			if (!$dbResult->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GLC_ERROR_NO_USER_ID"), "ERROR_NO_USER_ID");
				return false;
			}
		}

		if (is_set($arFields, "LOG_DATE") && (!$DB->IsDate($arFields["LOG_DATE"], false, LANG, "FULL")))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GLC_EMPTY_DATE_CREATE"), "EMPTY_LOG_DATE");
			return false;
		}

		if (isset($arFields["URL"]) && is_array($arSiteWorkgroupsPage))
			foreach($arSiteWorkgroupsPage as $groups_page)
				if (mb_strpos($arFields["URL"], $groups_page) === 0)
					$arFields["URL"] = "#GROUPS_PATH#".mb_substr($arFields["URL"], mb_strlen($groups_page), mb_strlen($arFields["URL"]) - mb_strlen($groups_page));

		if (!$USER_FIELD_MANAGER->CheckFields("SONET_COMMENT", $ID, $arFields, (isset($arFields["USER_ID"]) && intval($arFields["USER_ID"]) > 0 ? intval($arFields["USER_ID"]) : false)))
			return false;

		if (!empty($arFields['TEXT_MESSAGE']))
		{
			$arFields["TEXT_MESSAGE"] = \Bitrix\Main\Text\Emoji::encode($arFields["TEXT_MESSAGE"]);
		}

		if (!empty($arFields['MESSAGE']))
		{
			$arFields["MESSAGE"] = \Bitrix\Main\Text\Emoji::encode($arFields["MESSAGE"]);
		}

		return True;
	}

	public static function Delete($ID, $bSetSource = false)
	{
		global $DB, $APPLICATION, $USER_FIELD_MANAGER;

		$ID = intval($ID);
		if ($ID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GLC_WRONG_PARAMETER_ID"), "ERROR_NO_ID");
			return false;
		}

		$bSuccess = false;

		if ($arComment = CSocNetLogComments::GetByID($ID))
		{
			if ($bSetSource)
			{
				if ($arComment["EVENT_ID"] <> '')
				{
					$arCommentEvent = CSocNetLogTools::FindLogCommentEventByID($arComment["EVENT_ID"]);
					if (
						!$arCommentEvent
						|| !array_key_exists("DELETE_CALLBACK", $arCommentEvent)
						|| !is_callable($arCommentEvent["DELETE_CALLBACK"])
					)
					{
						$bSetSource = false;
					}
				}
			}

			$bSuccess = true;

			if ($bSetSource)
			{
				$arSource = CSocNetLogComments::SetSource($arComment, "DELETE");
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
				if ($bSuccess)
				{
					$bSuccess = $DB->Query("DELETE FROM b_sonet_log_comment WHERE ID = ".$ID, true);
				}

				if ($bSuccess)
				{
					$USER_FIELD_MANAGER->Delete("SONET_COMMENT", $ID);

					$db_events = GetModuleEvents("socialnetwork", "OnSocNetLogCommentDelete");
					while ($arEvent = $db_events->Fetch())
					{
						ExecuteModuleEventEx($arEvent, array($ID));
					}

					LogIndex::deleteIndex(array(
						'itemType' => LogIndexTable::ITEM_TYPE_COMMENT,
						'itemId' => $ID
					));

					LogTagTable::deleteByItem(array(
						'itemType' => LogTagTable::ITEM_TYPE_COMMENT,
						'itemId' => $ID
					));

					\CRatings::deleteRatingVoting([
						'ENTITY_TYPE_ID' => $arComment['RATING_TYPE_ID'],
						'ENTITY_ID' => $arComment['RATING_ENTITY_ID']
					]);

					if ((int)$arComment["LOG_ID"] > 0)
					{
						\Bitrix\Socialnetwork\Internals\EventService\Service::addEvent(
							\Bitrix\Socialnetwork\Internals\EventService\EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_DEL,
							[
								'SONET_LOG_ID' => (int)$arComment['LOG_ID'],
								'SONET_LOG_COMMENT_ID' => (int)$ID,
							]
						);

						CSocNetLogComments::UpdateLogData($arComment["LOG_ID"], false, true);

						$cache = new CPHPCache;
						$cacheSubFolder = (int)((int)$arComment["LOG_ID"] / 1000);
						$cache->CleanDir("/sonet/log/".$cacheSubFolder."/".$arComment["LOG_ID"]."/entry/");
						$cache->CleanDir("/sonet/log/".$cacheSubFolder."/".$arComment["LOG_ID"]."/comments/");
					}
				}
			}
			elseif (
				is_array($arSource)
				&& isset($arSource["ERROR"])
				&& is_string($arSource["ERROR"])
				&& !empty($arSource["ERROR"])
			)
			{
				$APPLICATION->ThrowException($arSource["ERROR"], "ERROR_DELETE_SOURCE");
				$bSuccess = false;
			}
		}

		return $bSuccess;
	}

	public static function DeleteNoDemand($userID)
	{
		global $DB;

		$userID = intval($userID);
		if ($userID <= 0)
			return false;

		$DB->Query("DELETE FROM b_sonet_log_comment WHERE ENTITY_TYPE = 'U' AND ENTITY_ID = ".$userID."", true);

		return true;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetByID($ID)
	{
		global $APPLICATION;

		$ID = intval($ID);
		if ($ID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GLC_WRONG_PARAMETER_ID"), "ERROR_NO_ID");
			return false;
		}

		$dbResult = CSocNetLogComments::GetList(array(), array("ID" => $ID));
		if ($arResult = $dbResult->GetNext())
			return $arResult;

		return false;
	}

	/***************************************/
	/**********  SEND EVENTS  **************/
	/***************************************/

	public static function SendEvent($ID, $mailTemplate = "SONET_NEW_EVENT", $bTransport = false)
	{
		global $DB;

		$arSocNetAllowedSubscribeEntityTypesDesc = CSocNetAllowed::GetAllowedEntityTypesDesc();

		$ID = intval($ID);
		if ($ID <= 0)
		{
			return false;
		}

		$arFilter = array("ID" => $ID);

		$dbLogComments = CSocNetLogComments::GetList(
			array(),
			$arFilter,
			false,
			false,
			array("ID", "LOG_ID", "ENTITY_TYPE", "ENTITY_ID", "USER_ID", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "EVENT_ID", "LOG_DATE", "MESSAGE", "TEXT_MESSAGE", "URL", "MODULE_ID", "GROUP_NAME", "CREATED_BY_NAME", "CREATED_BY_SECOND_NAME", "CREATED_BY_LAST_NAME", "CREATED_BY_LOGIN", "LOG_SITE_ID", "SOURCE_ID", "LOG_SOURCE_ID")
		);
		$arLogComment = $dbLogComments->Fetch();
		if (!$arLogComment)
			return false;

		$arLog = array();
		if (intval($arLogComment["LOG_ID"]) > 0)
		{
			$dbLog = CSocNetLog::GetList(
				array(),
				array("ID" => $arLogComment["LOG_ID"])
			);
			$arLog = $dbLog->Fetch();
			if (!$arLog)
				$arLog = array();
		}

		$arEvent = CSocNetLogTools::FindLogCommentEventByID($arLogComment["EVENT_ID"]);

		if (
			$arEvent
			&& array_key_exists("CLASS_FORMAT", $arEvent)
			&& array_key_exists("METHOD_FORMAT", $arEvent)
			&& $arEvent["CLASS_FORMAT"] <> ''
			&& $arEvent["METHOD_FORMAT"] <> ''
		)
		{
			$dbSiteCurrent = CSite::GetByID(SITE_ID);
			if ($arSiteCurrent = $dbSiteCurrent->Fetch())
				if ($arSiteCurrent["LANGUAGE_ID"] != LANGUAGE_ID)
					$arLogComment["MAIL_LANGUAGE_ID"] = $arSiteCurrent["LANGUAGE_ID"];

			$arLogComment["FIELDS_FORMATTED"] = call_user_func(array($arEvent["CLASS_FORMAT"], $arEvent["METHOD_FORMAT"]), $arLogComment, array(), true, $arLog);
		}

		if (
			array_key_exists($arLogComment["ENTITY_TYPE"], $arSocNetAllowedSubscribeEntityTypesDesc)
			&& array_key_exists("HAS_MY", $arSocNetAllowedSubscribeEntityTypesDesc[$arLogComment["ENTITY_TYPE"]])
			&& $arSocNetAllowedSubscribeEntityTypesDesc[$arLogComment["ENTITY_TYPE"]]["HAS_MY"] == "Y"
			&& array_key_exists("CLASS_OF", $arSocNetAllowedSubscribeEntityTypesDesc[$arLogComment["ENTITY_TYPE"]])
			&& array_key_exists("METHOD_OF", $arSocNetAllowedSubscribeEntityTypesDesc[$arLogComment["ENTITY_TYPE"]])
			&& $arSocNetAllowedSubscribeEntityTypesDesc[$arLogComment["ENTITY_TYPE"]]["CLASS_OF"] <> ''
			&& $arSocNetAllowedSubscribeEntityTypesDesc[$arLogComment["ENTITY_TYPE"]]["METHOD_OF"] <> ''
			&& method_exists($arSocNetAllowedSubscribeEntityTypesDesc[$arLogComment["ENTITY_TYPE"]]["CLASS_OF"], $arSocNetAllowedSubscribeEntityTypesDesc[$arLogComment["ENTITY_TYPE"]]["METHOD_OF"])
		)
		{
			$arOfEntities = call_user_func(array($arSocNetAllowedSubscribeEntityTypesDesc[$arLogComment["ENTITY_TYPE"]]["CLASS_OF"], $arSocNetAllowedSubscribeEntityTypesDesc[$arLogComment["ENTITY_TYPE"]]["METHOD_OF"]), $arLogComment["ENTITY_ID"]);
		}

		if ($bTransport)
		{
			$arListParams = array(
				"USE_SUBSCRIBE" => "Y",
				"ENTITY_TYPE" => $arLogComment["ENTITY_TYPE"],
				"ENTITY_ID" => $arLogComment["ENTITY_ID"],
				"EVENT_ID" => $arLogComment["EVENT_ID"],
				"USER_ID" => $arLogComment["USER_ID"],
				"OF_ENTITIES" => $arOfEntities,
				"TRANSPORT" => array("M", "X")
			);

			$arLogSites = array();
			$rsLogSite = CSocNetLog::GetSite($arLog["ID"]);
			while($arLogSite = $rsLogSite->Fetch())
			{
				$arLogSites[] = $arLogSite["LID"];
			}

			if (CModule::IncludeModule("extranet"))
			{
				if ($arLogComment["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
				{
					$arSites = array();
					$dbSite = CSite::GetList("sort", "desc", array("ACTIVE" => "Y"));
					while($arSite = $dbSite->Fetch())
					{
						$arSites[$arSite["ID"]] = array(
							"DIR" => (trim($arSite["DIR"]) <> '' ? $arSite["DIR"] : "/"),
							"SERVER_NAME" => (trim($arSite["SERVER_NAME"]) <> '' ? $arSite["SERVER_NAME"] : COption::GetOptionString("main", "server_name", $_SERVER["HTTP_HOST"]))
						);
					}

					$intranet_site_id = CSite::GetDefSite();
				}
				$arIntranetUsers = CExtranet::GetIntranetUsers();
				$extranet_site_id = CExtranet::GetExtranetSiteID();
			}

			$dbSubscribers = CSocNetLogEvents::GetList(
				array(
					"TRANSPORT" => "DESC"
				),
				array(
					"USER_ACTIVE" => "Y",
					"SITE_ID" => array_merge($arLogSites, array(false))
				),
				false,
				false,
				array("USER_ID", "ENTITY_TYPE", "ENTITY_ID", "ENTITY_CB", "ENTITY_MY", "USER_NAME", "USER_LAST_NAME", "USER_LOGIN", "USER_LID", "USER_EMAIL", "TRANSPORT"),
				$arListParams
			);

			$arListParams = array(
				"USE_SUBSCRIBE" => "Y",
				"ENTITY_TYPE" => $arLogComment["ENTITY_TYPE"],
				"ENTITY_ID" => $arLogComment["ENTITY_ID"],
				"EVENT_ID" => $arLogComment["EVENT_ID"],
				"USER_ID" => $arLogComment["USER_ID"],
				"OF_ENTITIES" => $arOfEntities,
				"TRANSPORT" => "N"
			);

			$dbUnSubscribers = CSocNetLogEvents::GetList(
				array(
					"TRANSPORT" => "DESC"
				),
				array(
					"USER_ACTIVE" => "Y",
					"SITE_ID" => array_merge($arLogSites, array(false))
				),
				false,
				false,
				array("USER_ID", "SITE_ID", "ENTITY_TYPE", "ENTITY_ID", "ENTITY_CB", "ENTITY_MY", "TRANSPORT", "EVENT_ID"),
				$arListParams
			);

			$arUnSubscribers = array();
			while ($arUnSubscriber = $dbUnSubscribers->Fetch())
			{
				$arUnSubscribers[] = $arUnSubscriber["USER_ID"]."_".$arUnSubscriber["ENTITY_TYPE"]."_".$arUnSubscriber["ENTITY_ID"]."_".$arUnSubscriber["ENTITY_MY"]."_".$arUnSubscriber["ENTITY_CB"]."_".$arUnSubscriber["EVENT_ID"];
			}

			$bHasAccessAll = CSocNetLogRights::CheckForUserAll(($arLog["ID"] ? $arLog["ID"] : $arLogComment["LOG_ID"]));

			$arSentUserID = array("M" => array(), "X" => array());
			while ($arSubscriber = $dbSubscribers->Fetch())
			{
				if (
					is_array($arIntranetUsers)
					&& !in_array($arSubscriber["USER_ID"], $arIntranetUsers)
					&& !in_array($extranet_site_id, $arLogSites)
				)
				{
					continue;
				}

				if (
					array_key_exists($arSubscriber["TRANSPORT"], $arSentUserID)
					&& in_array($arSubscriber["USER_ID"], $arSentUserID[$arSubscriber["TRANSPORT"]])
				)
				{
					continue;
				}

				if (
					intval($arSubscriber["ENTITY_ID"]) != 0
					&& $arSubscriber["EVENT_ID"] == "all"
					&&
					(
						in_array($arSubscriber["USER_ID"]."_".$arSubscriber["ENTITY_TYPE"]."_".$arSubscriber["ENTITY_ID"]."_N_".$arSubscriber["ENTITY_CB"]."_".$arLogComment["EVENT_ID"], $arUnSubscribers)
						|| in_array($arSubscriber["USER_ID"]."_".$arSubscriber["ENTITY_TYPE"]."_".$arSubscriber["ENTITY_ID"]."_Y_".$arSubscriber["ENTITY_CB"]."_".$arLogComment["EVENT_ID"], $arUnSubscribers)
					)
				)
				{
					continue;
				}
				elseif (
					intval($arSubscriber["ENTITY_ID"]) == 0
					&& $arSubscriber["ENTITY_CB"] == "N"
					&& $arSubscriber["EVENT_ID"] != "all"
					&&
					(
						in_array($arSubscriber["USER_ID"]."_".$arSubscriber["ENTITY_TYPE"]."_".$arLogComment["ENTITY_ID"]."_Y_N_all", $arUnSubscribers)
						|| in_array($arSubscriber["USER_ID"]."_".$arSubscriber["ENTITY_TYPE"]."_".$arLogComment["ENTITY_ID"]."_N_N_all", $arUnSubscribers)
						|| in_array($arSubscriber["USER_ID"]."_".$arSubscriber["ENTITY_TYPE"]."_".$arLogComment["ENTITY_ID"]."_Y_N_".$arLogComment["EVENT_ID"], $arUnSubscribers)
						|| in_array($arSubscriber["USER_ID"]."_".$arSubscriber["ENTITY_TYPE"]."_".$arLogComment["ENTITY_ID"]."_N_N_".$arLogComment["EVENT_ID"], $arUnSubscribers)
					)
				)
				{
					continue;
				}

				$arSentUserID[$arSubscriber["TRANSPORT"]][] = $arSubscriber["USER_ID"];

				if (!$bHasAccessAll)
				{
					$bHasAccess = CSocNetLogRights::CheckForUserOnly(($arLog["ID"] ? $arLog["ID"] : $arLogComment["LOG_ID"]), $arSubscriber["USER_ID"]);
					if (!$bHasAccess)
					{
						continue;
					}
				}

				if (
					$arLogComment["ENTITY_TYPE"] == SONET_ENTITY_GROUP
					&& is_array($arIntranetUsers)
					&& CModule::IncludeModule("extranet")
				)
				{
					$server_name = $arSites[((!in_array($arSubscriber["USER_ID"], $arIntranetUsers) && $extranet_site_id) ? $extranet_site_id : $intranet_site_id)]["SERVER_NAME"];
					$arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["URL_TO_SEND"] = str_replace(
						array("#SERVER_NAME#", "#GROUPS_PATH#"),
						array(
							$server_name,
							COption::GetOptionString("socialnetwork", "workgroups_page", false, ((!in_array($arSubscriber["USER_ID"], $arIntranetUsers) && $extranet_site_id) ? $extranet_site_id : $intranet_site_id))
						),
						$arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["URL"]
					);
				}
				else
				{
					$arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["URL_TO_SEND"] = $arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["URL"];
				}

				switch ($arSubscriber["TRANSPORT"])
				{
					case "X":
						$link = (
							array_key_exists("URL_TO_SEND", $arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
							&& $arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["URL_TO_SEND"] <> ''
								? GetMessage("SONET_GLC_SEND_EVENT_LINK").$arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["URL_TO_SEND"]
								: ""
						);

						$arMessageFields = array(
							"FROM_USER_ID" => ((int)$arLogComment["USER_ID"] > 0 ? $arLogComment["USER_ID"] : 1),
							"TO_USER_ID" => $arSubscriber["USER_ID"],
							"MESSAGE" => $arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["TITLE"]." #BR# ".$arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["MESSAGE"].($link <> '' ? "#BR# ".$link : ""),
							"=DATE_CREATE" => $DB->CurrentTimeFunction(),
							"MESSAGE_TYPE" => SONET_MESSAGE_SYSTEM,
							"IS_LOG" => "Y"
						);
						CSocNetMessages::Add($arMessageFields);
						break;
					case "M":
						$arFields["SUBSCRIBER_ID"] = $arSubscriber["USER_ID"];
						$arFields["SUBSCRIBER_NAME"] = $arSubscriber["USER_NAME"];
						$arFields["SUBSCRIBER_LAST_NAME"] = $arSubscriber["USER_LAST_NAME"];
						$arFields["SUBSCRIBER_LOGIN"] = $arSubscriber["USER_LOGIN"];
						$arFields["SUBSCRIBER_EMAIL"] = $arSubscriber["USER_EMAIL"];
						$arFields["EMAIL_TO"] = $arSubscriber["USER_EMAIL"];
						$arFields["TITLE"] = str_replace("#BR#", "\n", $arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["TITLE"]);
						$arFields["MESSAGE"] = str_replace("#BR#", "\n", $arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["MESSAGE"]);
						$arFields["ENTITY"] = $arLogComment["FIELDS_FORMATTED"]["ENTITY"]["FORMATTED"];
						$arFields["ENTITY_TYPE"] = $arLogComment["FIELDS_FORMATTED"]["ENTITY"]["TYPE_MAIL"];

						$arFields["URL"] = (
							array_key_exists("URL_TO_SEND", $arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
							&& $arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["URL_TO_SEND"] <> ''
								? $arLogComment["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["URL_TO_SEND"]
								: $arLogComment["URL"]
						);

						if (CModule::IncludeModule("extranet"))
						{
							$arUserGroup = CUser::GetUserGroup($arSubscriber["USER_ID"]);
						}

						foreach ($arLogSites as $site_id_tmp)
						{
							if (IsModuleInstalled("extranet"))
							{
								if (
									(
										CExtranet::IsExtranetSite($site_id_tmp)
										&& in_array(CExtranet::GetExtranetUserGroupID(), $arUserGroup)
									)
									||
									(
										!CExtranet::IsExtranetSite($site_id_tmp)
										&& !in_array(CExtranet::GetExtranetUserGroupID(), $arUserGroup)
									)
								)
								{
									$siteID = $site_id_tmp;
									break;
								}
								else
								{
									continue;
								}
							}
							else
							{
								$siteID = $site_id_tmp;
								break;
							}
						}

						if (!$siteID)
							$siteID = (defined("SITE_ID") ? SITE_ID : $arSubscriber["SITE_ID"]);

						if ($siteID == '')
							$siteID = $arSubscriber["USER_LID"];
						if ($siteID == '')
							break;

						$event = new CEvent;
						$event->Send($mailTemplate, $siteID, $arFields, "N");
						break;
					default:
				}
			}
		}

		if (!(
			$arLogComment["EVENT_ID"] === "tasks_comment"
			&& !\Bitrix\Socialnetwork\ComponentHelper::checkLivefeedTasksAllowed()
		))
		{
			if (!$bHasAccessAll)
			{
				CUserCounter::IncrementWithSelect(
					CSocNetLogCounter::GetSubSelect2(
						$arLogComment["ID"],
						array(
							"TYPE" => "LC",
							"FOR_ALL_ACCESS" => $bHasAccessAll
						)
					),
					true,
					[
						'SET_ENTITY' => 'Y',
						'SET_ENTRY' => 'Y',
					]
				);
			}
			else // for all, mysql only
			{
				$tag = time();
				CUserCounter::IncrementWithSelect(
					CSocNetLogCounter::GetSubSelect2(
						$arLogComment["ID"],
						array(
							"TYPE" => "LC",
							"FOR_ALL_ACCESS_ONLY" => true,
							"TAG_SET" => $tag
						)
					),
					false, // sendpull
					array(
						"TAG_SET" => $tag,
						'SET_ENTITY' => 'Y',
						'SET_ENTRY' => 'Y',
					)
				);

				CUserCounter::IncrementWithSelect(
					CSocNetLogCounter::GetSubSelect2(
						$arLogComment["ID"],
						array(
							"TYPE" => "LC",
							"FOR_ALL_ACCESS_ONLY" => false
						)
					),
					true, // sendpull
					array(
						"TAG_CHECK" => $tag,
						'SET_ENTITY' => 'Y',
						'SET_ENTRY' => 'Y',
					)
				);
			}
		}

		return true;
	}

	public static function UpdateLogData($log_id, $bSetDate = true, $bSetDateByLastComment = false)
	{
		$dbResult = CSocNetLogComments::GetList(array(), array("LOG_ID" => $log_id), array());
		$comments_count = $dbResult;

		$res = LogTable::getList(array(
			'filter' => array(
				"ID" => $log_id
			),
			'select' => array("ID", "LOG_DATE")
		));
		while ($logFields = $res->fetch())
		{
			$arFields = array("COMMENTS_COUNT" => $comments_count);
			if ($bSetDateByLastComment)
			{
				$resComments = LogCommentTable::getList(array(
					'order' => array(
						"LOG_DATE" => "DESC"
					),
					'filter' => array(
						"LOG_ID" => $log_id
					),
					'select' => array("ID", "LOG_DATE")
				));
				$arFields["LOG_UPDATE"] = (
					($commentFields = $resComments->fetch())
						? $commentFields["LOG_DATE"]
						: $logFields["LOG_DATE"]
				);
			}
			elseif ($bSetDate)
			{
				$connection = \Bitrix\Main\Application::getConnection();
				$helper = $connection->getSqlHelper();
				$arFields["LOG_UPDATE"] = new SqlExpression($helper->getCurrentDateTimeFunction());
			}

			LogTable::update($logFields["ID"], $arFields);

			CSocNetLogFollow::DeleteByLogID($log_id, "Y", true); // not only delete but update to NULL for existing records
		}
	}

	public static function SetSource($arFields, $action = false)
	{
		$arCallback = false;

		if (!$action)
		{
			$action = "ADD";
		}

		if (!in_array($action, array("ADD", "UPDATE", "DELETE")))
		{
			return false;
		}

		$arEvent = CSocNetLogTools::FindLogCommentEventByID($arFields["EVENT_ID"]);
		if ($arEvent)
		{
			$arCallback = $arEvent[$action."_CALLBACK"];
		}

		if (
			$arCallback
			&& is_callable($arCallback)
		)
		{
			$arSource = call_user_func_array($arCallback, array($arFields));
		}

		return $arSource;
	}

	public static function SendMentionNotification($arCommentFields)
	{
		if (!CModule::IncludeModule("im"))
		{
			return false;
		}

		if ($arCommentFields["EVENT_ID"] === 'forum')
		{
			$arTitleRes = self::OnSendMentionGetEntityFields_Forum($arCommentFields);
		}
		else
		{
			$db_events = GetModuleEvents("socialnetwork", "OnSendMentionGetEntityFields");
			while ($arEvent = $db_events->Fetch())
			{
				$arTitleRes = ExecuteModuleEventEx($arEvent, array($arCommentFields));
				if ($arTitleRes)
				{
					break;
				}
			}
		}

		if (
			!empty($arTitleRes)
			&& is_array($arTitleRes)
			&& !empty($arTitleRes["NOTIFY_MESSAGE"])
		)
		{
			$arMessageFields = array(
				"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
				"FROM_USER_ID" => $arCommentFields["USER_ID"],
				"NOTIFY_TYPE" => IM_NOTIFY_FROM,
				"NOTIFY_MODULE" => (!empty($arTitleRes["NOTIFY_MODULE"]) ? $arTitleRes["NOTIFY_MODULE"] : "socialnetwork"),
				"NOTIFY_EVENT" => "mention",
				"NOTIFY_TAG" => (!empty($arTitleRes["NOTIFY_TAG"]) ? $arTitleRes["NOTIFY_TAG"] : "LOG_COMMENT|COMMENT_MENTION|".$arCommentFields["ID"])
			);

			$arMention = \Bitrix\Socialnetwork\Helper\Mention::getUserIds($arCommentFields['MESSAGE']);

			if(!empty($arMention))
			{
				$arExcludeUsers = array($arCommentFields["USER_ID"]);

				if (!empty($arCommentFields["LOG_ID"]))
				{
					$rsUnFollower = CSocNetLogFollow::GetList(
						array(
							"CODE" => "L".$arCommentFields["LOG_ID"],
							"TYPE" => "N"
						),
						array("USER_ID")
					);

					while ($arUnFollower = $rsUnFollower->Fetch())
					{
						$arExcludeUsers[] = $arUnFollower["USER_ID"];
					}
				}

				$arSourceURL = array(
					"URL" => $arTitleRes["URL"]
				);
				if (!empty($arTitleRes["CRM_URL"]))
				{
					$arSourceURL["CRM_URL"] = $arTitleRes["CRM_URL"];
				}

				foreach ($arMention as $mentionUserID)
				{
					$bHaveRights = (
						($arTitleRes["IS_CRM"] ?? null) !== "Y"
						|| COption::GetOptionString("crm", "enable_livefeed_merge", "N") === "Y"
							? CSocNetLogRights::CheckForUserOnly($arCommentFields["LOG_ID"], $mentionUserID)
							: false
					);

					if (
						$bHaveRights
						&& $arTitleRes["IS_CRM"] === "Y"
					) // user has 'normal' rights to the log entry but it's crm
					{
						$dbLog = CSocNetLog::getList(
							array(),
							array(
								"ID" => $arCommentFields["LOG_ID"],
							),
							false,
							false,
							array("ID", "MODULE_ID")
						);
						if (
							!($arLog = $dbLog->fetch())
							|| $arLog["MODULE_ID"] !== "crm_shared"
						)
						{
							$bHaveRights = false;
						}
					}

					$bHaveCrmRights = false;

					if (
						!$bHaveRights
						&& ($arTitleRes["IS_CRM"] ?? null) === "Y"
					)
					{
						$dbLog = CSocNetLog::GetList(
							array(),
							array(
								"ID" => $arCommentFields["LOG_ID"],
								"ENTITY_TYPE" => $arCommentFields["ENTITY_TYPE"],
							),
							false,
							false,
							array("ID"),
							array(
								"IS_CRM" => "Y",
								"CHECK_CRM_RIGHTS" => "Y",
								"USER_ID" => $mentionUserID,
								"USE_SUBSCRIBE" => "N"
							)
						);
						if ($arLog = $dbLog->fetch())
						{
							$bHaveCrmRights = true;
						}
					}

					if (
						in_array($mentionUserID, $arExcludeUsers)
						|| (!$bHaveRights && !$bHaveCrmRights)
					)
					{
						continue;
					}

					$url = false;
					$serverName = false;

					if (
						!empty($arSourceURL["URL"])
						|| !empty($arSourceURL["CRM_URL"])
					)
					{
						$arTmp = CSocNetLogTools::ProcessPath(
							$arSourceURL,
							$mentionUserID
						);

						if (
							$arTitleRes["IS_CRM"] === "Y"
							&& $bHaveCrmRights
							&& !empty($arTmp["URLS"]["CRM_URL"])
						)
						{
							$url = $arTmp["URLS"]["CRM_URL"];
						}
						else
						{
							$url = $arTmp["URLS"]["URL"];
						}
						$serverName = (str_starts_with($url, "http://") || str_starts_with($url, "https://") ? "" : $arTmp["SERVER_NAME"]);
					}

					$arMessageFields["TO_USER_ID"] = $mentionUserID;

					if (is_callable($arTitleRes['NOTIFY_MESSAGE']))
					{
						$messageClosure = $arTitleRes['NOTIFY_MESSAGE'];
						$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => str_replace(
							array("#url#", "#server_name#"),
							array($url, $serverName),
							$messageClosure($languageId)
						);
					}
					else
					{
						$arMessageFields["NOTIFY_MESSAGE"] = str_replace(
							array("#url#", "#server_name#"),
							array($url, $serverName),
							$arTitleRes["NOTIFY_MESSAGE"]
						);
					}

					if (!empty($arTitleRes["NOTIFY_MESSAGE_OUT"]))
					{
						if (is_callable($arTitleRes["NOTIFY_MESSAGE_OUT"]))
						{
							$messageOutClosure = $arTitleRes["NOTIFY_MESSAGE_OUT"];
							$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => str_replace(
								array("#url#", "#server_name#"),
								array($url, $serverName),
								$messageOutClosure($languageId)
							);
						}
						else
						{
							$arMessageFields["NOTIFY_MESSAGE_OUT"] = str_replace(
								array("#url#", "#server_name#"),
								array($url, $serverName),
								$arTitleRes["NOTIFY_MESSAGE_OUT"]
							);
						}
					}
					else
					{
						$arMessageFields["NOTIFY_MESSAGE_OUT"] = '';
					}

					CIMNotify::Add($arMessageFields);
				}

				$arMentionedDestCode = array();
				foreach($arMention as $val)
				{
					$arMentionedDestCode[] = "U".$val;
				}

				\Bitrix\Main\FinderDestTable::merge(array(
					"CONTEXT" => "mention",
					"CODE" => array_unique($arMentionedDestCode)
				));
			}
		}
	}

	public static function OnSendMentionGetEntityFields_Forum($arCommentFields)
	{
		if ($arCommentFields["EVENT_ID"] !== "forum")
		{
			return false;
		}

		$dbLog = CSocNetLog::GetList(
			array(),
			array(
				"ID" => $arCommentFields["LOG_ID"],
				"EVENT_ID" => "forum"
			),
			false,
			false,
			array("ID", "TITLE")
		);

		if ($arLog = $dbLog->Fetch())
		{
			$genderSuffix = "";
			$dbUsers = CUser::GetList("ID", "desc", array("ID" => $arCommentFields["USER_ID"]), array("PERSONAL_GENDER", "LOGIN", "NAME", "LAST_NAME", "SECOND_NAME"));
			if ($arUser = $dbUsers->Fetch())
			{
				$genderSuffix = $arUser["PERSONAL_GENDER"];
			}

			$strPathToLogEntry = str_replace("#log_id#", $arLog["ID"], COption::GetOptionString("socialnetwork", "log_entry_page", "/company/personal/log/#log_id#/", SITE_ID));
			$strPathToLogEntryComment = $strPathToLogEntry.(mb_strpos($strPathToLogEntry, "?") !== false ? "&" : "?")."commentID=".$arCommentFields["ID"];

			$title = str_replace(Array("\r\n", "\n"), " ", $arLog["TITLE"]);
			$title = TruncateText($title, 100);
			$title_out = TruncateText($title, 255);

			return [
				"URL" => $strPathToLogEntryComment,
				"NOTIFY_TAG" => "FORUM|COMMENT_MENTION|".$arCommentFields["ID"],
				"NOTIFY_MESSAGE" => fn (?string $languageId = null) => Loc::getMessage(
					"SONET_GLC_FORUM_MENTION".($genderSuffix <> '' ? "_".$genderSuffix : ""),
					[
						"#title#" => "<a href=\"#url#\" class=\"bx-notifier-item-action\">".$title."</a>"
					],
					$languageId
				),
				"NOTIFY_MESSAGE_OUT" => fn (?string $languageId = null) => Loc::getMessage(
					"SONET_GLC_FORUM_MENTION".($genderSuffix <> '' ? "_".$genderSuffix : ""),
					[
						"#title#" => $title_out
					],
					$languageId
				)." ("."#server_name##url#)"
			];
		}

		return false;
	}

	public static function BatchUpdateLogId($oldLogId, $newLogId)
	{
		global $DB;

		$strUpdate = "UPDATE b_sonet_log_comment SET ".$DB->PrepareUpdate("b_sonet_log_comment", array("LOG_ID" => $newLogId))." WHERE LOG_ID=".intval($oldLogId);
		$res = $DB->Query($strUpdate);

		return $res;
	}
}
