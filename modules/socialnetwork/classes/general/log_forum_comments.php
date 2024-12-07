<?php
IncludeModuleLangFile(__FILE__);

class CSocNetForumComments
{
	public static function findLogEventIDByForumEntityID($forumEntityType)
	{
		$event_id = false;
		$arSocNetLogEvents = CSocNetAllowed::GetAllowedLogEvents();

		foreach ($arSocNetLogEvents as $event_id_tmp => $arEventTmp)
		{
			if (
				array_key_exists("FORUM_COMMENT_ENTITY", $arEventTmp)
				&& $arEventTmp["FORUM_COMMENT_ENTITY"] == $forumEntityType
			)
			{
				$event_id = $event_id_tmp;
				break;
			}
		}

		$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();
		foreach ($arSocNetFeaturesSettings as $feature_tmp => $arFeature)
		{
			if (array_key_exists("subscribe_events", $arFeature))
			{
				foreach ($arFeature["subscribe_events"] as $event_id_tmp => $arEventTmp)
				{
					if (
						array_key_exists("FORUM_COMMENT_ENTITY", $arEventTmp)
						&& $arEventTmp["FORUM_COMMENT_ENTITY"] == $forumEntityType
					)
					{
						$event_id = $event_id_tmp;
						break;
					}
				}
			}
		}

		return $event_id;
	}

	public static function onAfterCommentAdd($entityType, $entityId, $arData)
	{
		global $DB, $USER_FIELD_MANAGER;

		$log_event_id = \CSocNetForumComments::findLogEventIDByForumEntityID($entityType);
		if (
			!$log_event_id
			|| $log_event_id == 'tasks' // \Bitrix\Tasks\Integration\Forum\Task\Comment::onAfterAdd()
		)
		{
			return false;
		}

		$arLogCommentEvent = CSocNetLogTools::FindLogCommentEventByLogEventID($log_event_id);
		if (!$arLogCommentEvent)
		{
			return false;
		}

		$entityId = intval($entityId);
		if ($entityId <= 0)
		{
			return false;
		}

		$messageId = $arData['MESSAGE_ID'];
		if ($messageId <= 0)
		{
			return false;
		}

		$arMessage = CForumMessage::GetByID($messageId);
		if (!$arMessage)
		{
			return false;
		}

		$sText = (COption::GetOptionString("forum", "FILTER", "Y") == "Y" ? $arMessage["POST_MESSAGE_FILTER"] : $arMessage["POST_MESSAGE"]);

		$logFilter = array(
			"EVENT_ID" => $log_event_id,
			"SOURCE_ID" => $entityId
		);

		foreach (GetModuleEvents("socialnetwork", "onAfterCommentAddBefore", true) as $arModuleEvent)
		{
			$res = ExecuteModuleEventEx($arModuleEvent, array(
				$entityType,
				$entityId,
				$arData
			));

			if (isset($res) && is_array($res) && isset($res['LOG_ENTRY_ID']) && $res['LOG_ENTRY_ID'] > 1)
			{
				$logFilter = array(
					'ID' => $res['LOG_ENTRY_ID']
				);
			}
		}

		$log_id = null;

		$dbRes = CSocNetLog::GetList(
			array("ID" => "DESC"),
			$logFilter,
			false,
			false,
			array("ID", "ENTITY_TYPE", "ENTITY_ID", "SOURCE_ID", "USER_ID")
		);

		if ($arRes = $dbRes->Fetch())
		{
			$log_id = $arRes["ID"];
			$entity_type = $arRes["ENTITY_TYPE"];
			$entity_id = $arRes["ENTITY_ID"];
			$strURL = '';

			if (
				isset($arLogCommentEvent["METHOD_GET_URL"])
				&& is_callable($arLogCommentEvent["METHOD_GET_URL"])
			)
			{
				$strURL = call_user_func_array($arLogCommentEvent["METHOD_GET_URL"], array(array(
					"ENTRY_ID" => $arRes["SOURCE_ID"],
					"ENTRY_USER_ID" => $arRes["USER_ID"],
					"COMMENT_ID" => $messageId
				)));
			}

			$parser = new CTextParser();
			$parser->allow = array("HTML" => 'N',"ANCHOR" => 'Y',"BIU" => 'Y',"IMG" => "Y","VIDEO" => "Y","LIST" => 'N',"QUOTE" => 'Y',"CODE" => 'Y',"FONT" => 'Y',"SMILES" => "N","UPLOAD" => 'N',"NL2BR" => 'N',"TABLE" => "Y");

			$arFieldsForSocnet = array(
				"ENTITY_TYPE" => $entity_type,
				"ENTITY_ID" => $entity_id,
				"EVENT_ID" => $arLogCommentEvent["EVENT_ID"],
				"=LOG_DATE" => $DB->CurrentTimeFunction(),
				"USER_ID" => $arMessage["AUTHOR_ID"],
				"MESSAGE" => $sText,
				"TEXT_MESSAGE" => $parser->convert4mail($sText),
				"URL" => $strURL,
				"MODULE_ID" => (array_key_exists("MODULE_ID", $arLogCommentEvent) && $arLogCommentEvent["MODULE_ID"] <> '' ? $arLogCommentEvent["MODULE_ID"] : ""),
				"SOURCE_ID" => $messageId,
				"LOG_ID" => $log_id
			);

			if (
				!array_key_exists("RATING_TYPE_ID", $arLogCommentEvent)
				|| $arLogCommentEvent["RATING_TYPE_ID"] == "FORUM_POST"
			)
			{
				$arFieldsForSocnet["RATING_TYPE_ID"] = "FORUM_POST";
				$arFieldsForSocnet["RATING_ENTITY_ID"] = $messageId;
			}

			$ufFileID = array();
			$dbAddedMessageFiles = CForumFiles::GetList(array("ID" => "ASC"), array("MESSAGE_ID" => $messageId));
			while ($arAddedMessageFiles = $dbAddedMessageFiles->Fetch())
			{
				$ufFileID[] = $arAddedMessageFiles["FILE_ID"];
			}

			if (count($ufFileID) > 0)
			{
				$arFieldsForSocnet["UF_SONET_COM_FILE"] = $ufFileID;
			}

			$ufDocID = $USER_FIELD_MANAGER->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MESSAGE_DOC", $messageId, LANGUAGE_ID);
			if ($ufDocID)
			{
				$arFieldsForSocnet["UF_SONET_COM_DOC"] = $ufDocID;
			}

			$ufUrlPreview = $USER_FIELD_MANAGER->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MES_URL_PRV", $messageId, LANGUAGE_ID);
			if ($ufUrlPreview)
			{
				$arFieldsForSocnet["UF_SONET_COM_URL_PRV"] = $ufUrlPreview;
			}

			$comment_id = CSocNetLogComments::Add($arFieldsForSocnet, false, false);
			CSocNetLog::CounterIncrement(
				$comment_id,
				false,
				false,
				"LC",
				CSocNetLogRights::CheckForUserAll($log_id)
			);
		}

		foreach (GetModuleEvents("socialnetwork", "onAfterCommentAddAfter", true) as $arModuleEvent)
			ExecuteModuleEventEx($arModuleEvent, array(
				$entityType,
				$entityId,
				$arData,
				$log_id
			));

		foreach (GetModuleEvents("socialnetwork", "OnForumCommentIMNotify", true) as $arModuleEvent) // send notification
		{
			ExecuteModuleEventEx($arModuleEvent, array(
				$entityType,
				$entityId,
				array(
					"LOG_ID" => $log_id,
					"USER_ID" => $arMessage["AUTHOR_ID"],
					"MESSAGE_ID" => $messageId,
					"MESSAGE" => $sText,
					"URL" => $strURL ?? ''
				)
			));
		}

		return false;
	}

	public static function onAfterCommentUpdate($entityType, $entityId, $arData)
	{
		global $APPLICATION, $DB, $USER_FIELD_MANAGER;

		$log_event_id = \CSocNetForumComments::findLogEventIDByForumEntityID($entityType);
		if (!$log_event_id)
		{
			return false;
		}

		$arLogCommentEvent = CSocNetLogTools::FindLogCommentEventByLogEventID($log_event_id);
		if (!$arLogCommentEvent)
		{
			return false;
		}

		$entityId = intval($entityId);
		if ($entityId <= 0)
		{
			return false;
		}

		if (empty($arData["MESSAGE_ID"]))
		{
			return false;
		}

		$log_id = null;

		$parser = new CTextParser();
		$parser->allow = array("HTML" => 'N',"ANCHOR" => 'Y',"BIU" => 'Y',"IMG" => "Y","VIDEO" => "Y","LIST" => 'N',"QUOTE" => 'Y',"CODE" => 'Y',"FONT" => 'Y',"SMILES" => "N","UPLOAD" => 'N',"NL2BR" => 'N',"TABLE" => "Y");

		switch ($arData["ACTION"])
		{
			case "DEL":
			case "HIDE":
				$dbLogComment = CSocNetLogComments::GetList(
					array("ID" => "DESC"),
					array(
						"EVENT_ID"	=> array($arLogCommentEvent["EVENT_ID"]),
						"SOURCE_ID" => intval($arData["MESSAGE_ID"])
					),
					false,
					false,
					array("ID")
				);
				while ($arLogComment = $dbLogComment->Fetch())
					CSocNetLogComments::Delete($arLogComment["ID"]);
				break;
			case "SHOW":
				$dbLogComment = CSocNetLogComments::GetList(
					array("ID" => "DESC"),
					array(
						"EVENT_ID"	=> array($arLogCommentEvent["EVENT_ID"]),
						"SOURCE_ID" => intval($arData["MESSAGE_ID"])
					),
					false,
					false,
					array("ID")
				);
				$arLogComment = $dbLogComment->Fetch();
				if (!$arLogComment)
				{
					$arMessage = CForumMessage::GetByID(intval($arData["MESSAGE_ID"]));
					if ($arMessage)
					{
						$dbLog = CSocNetLog::GetList(
							array("ID" => "DESC"),
							array(
								"EVENT_ID" => $log_event_id,
								"SOURCE_ID" => $entityId
							),
							false,
							false,
							array("ID", "ENTITY_TYPE", "ENTITY_ID")
						);

						if ($arLog = $dbLog->Fetch())
						{
							$log_id = $arLog["ID"];
							$entity_type = $arLog["ENTITY_TYPE"];
							$entity_id = $arLog["ENTITY_ID"];

							$sText = (COption::GetOptionString("forum", "FILTER", "Y") == "Y" ? $arMessage["POST_MESSAGE_FILTER"] : $arMessage["POST_MESSAGE"]);
							$strURL = $APPLICATION->GetCurPageParam("", array("IFRAME", "MID", BX_AJAX_PARAM_ID, "result"));
							$strURL = ForumAddPageParams(
								$strURL,
								array(
									"MID" => intval($arData["MESSAGE_ID"]),
									"result" => "reply"
								),
								false,
								false
							);

							$arFieldsForSocnet = array(
								"ENTITY_TYPE" => $entity_type,
								"ENTITY_ID" => $entity_id,
								"EVENT_ID" => $arLogCommentEvent["EVENT_ID"],
								"MESSAGE" => $sText,
								"TEXT_MESSAGE" => $parser->convert4mail($sText),
								"URL" => str_replace("?IFRAME=Y", "", str_replace("&IFRAME=Y", "", str_replace("IFRAME=Y&", "", $strURL))),
								"MODULE_ID" => (array_key_exists("MODULE_ID", $arLogCommentEvent) && $arLogCommentEvent["MODULE_ID"] <> '' ? $arLogCommentEvent["MODULE_ID"] : ""),
								"SOURCE_ID" => intval($arData["MESSAGE_ID"]),
								"LOG_ID" => $log_id,
								"RATING_TYPE_ID" => "FORUM_POST",
								"RATING_ENTITY_ID" => intval($arData["MESSAGE_ID"])
							);

							$arFieldsForSocnet["USER_ID"] = $arMessage["AUTHOR_ID"];
							$arFieldsForSocnet["=LOG_DATE"] = $DB->CurrentTimeFunction();

							$ufFileID = array();
							$dbAddedMessageFiles = CForumFiles::GetList(array("ID" => "ASC"), array("MESSAGE_ID" => intval($arData["MESSAGE_ID"])));
							while ($arAddedMessageFiles = $dbAddedMessageFiles->Fetch())
								$ufFileID[] = $arAddedMessageFiles["FILE_ID"];

							if (count($ufFileID) > 0)
								$arFieldsForSocnet["UF_SONET_COM_FILE"] = $ufFileID;

							$ufDocID = $USER_FIELD_MANAGER->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MESSAGE_DOC", intval($arData["MESSAGE_ID"]), LANGUAGE_ID);
							if ($ufDocID)
								$arFieldsForSocnet["UF_SONET_COM_DOC"] = $ufDocID;

							$ufUrlPreview = $USER_FIELD_MANAGER->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MES_URL_PRV", intval($arData["MESSAGE_ID"]), LANGUAGE_ID);
							if ($ufUrlPreview)
							{
								$arFieldsForSocnet["UF_SONET_COM_URL_PRV"] = $ufUrlPreview;
							}

							$comment_id = CSocNetLogComments::Add($arFieldsForSocnet, false, false);
							CSocNetLog::CounterIncrement(
								$comment_id,
								false,
								false,
								"LC",
								CSocNetLogRights::CheckForUserAll($log_id)
							);
						}
					}
				}
				break;
			case "EDIT":
				$arMessage = CForumMessage::GetByID(intval($arData["MESSAGE_ID"]));
				if ($arMessage)
				{
					$dbLogComment = CSocNetLogComments::GetList(
						array("ID" => "DESC"),
						array(
							"EVENT_ID"	=> array($arLogCommentEvent["EVENT_ID"]),
							"SOURCE_ID" => intval($arData["MESSAGE_ID"])
						),
						false,
						false,
						array("ID")
					);
					$arLogComment = $dbLogComment->Fetch();
					if ($arLogComment)
					{
						$sText = (COption::GetOptionString("forum", "FILTER", "Y") == "Y" ? $arMessage["POST_MESSAGE_FILTER"] : $arMessage["POST_MESSAGE"]);
						$arFieldsForSocnet = array(
							"MESSAGE" => $sText,
							"TEXT_MESSAGE" => $parser->convert4mail($sText),
						);

						$ufFileID = array();
						$dbAddedMessageFiles = CForumFiles::GetList(array("ID" => "ASC"), array("MESSAGE_ID" => intval($arData["MESSAGE_ID"])));
						while ($arAddedMessageFiles = $dbAddedMessageFiles->Fetch())
							$ufFileID[] = $arAddedMessageFiles["FILE_ID"];

						if (count($ufFileID) > 0)
							$arFieldsForSocnet["UF_SONET_COM_FILE"] = $ufFileID;

						$ufDocID = $USER_FIELD_MANAGER->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MESSAGE_DOC", intval($arData["MESSAGE_ID"]), LANGUAGE_ID);
						if ($ufDocID)
							$arFieldsForSocnet["UF_SONET_COM_DOC"] = $ufDocID;

						$ufUrlPreview = $USER_FIELD_MANAGER->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MES_URL_PRV", intval($arData["MESSAGE_ID"]), LANGUAGE_ID);
						if ($ufUrlPreview)
						{
							$arFieldsForSocnet["UF_SONET_COM_URL_PRV"] = $ufUrlPreview;
						}

						CSocNetLogComments::Update($arLogComment["ID"], $arFieldsForSocnet);
					}
				}
				break;
			default:
		}

		foreach (GetModuleEvents("socialnetwork", "onAfterCommentUpdateAfter", true) as $arModuleEvent)
		{
			ExecuteModuleEventEx($arModuleEvent, array(
				$entityType,
				$entityId,
				$arData,
				$log_id
			));
		}
	}
}
