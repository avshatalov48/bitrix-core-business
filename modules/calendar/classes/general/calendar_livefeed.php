<?
IncludeModuleLangFile(__FILE__);

class CCalendarLiveFeed
{
	public static function AddEvent(&$arSocNetFeaturesSettings)
	{
		$arSocNetFeaturesSettings['calendar']['subscribe_events'] = array(
			'calendar' => array(
				'ENTITIES' => array(
					SONET_SUBSCRIBE_ENTITY_USER => array()
				),
				"FORUM_COMMENT_ENTITY" => "EV",
				'OPERATION' => 'view',
				'CLASS_FORMAT' => 'CCalendarLiveFeed',
				'METHOD_FORMAT' => 'FormatEvent',
				'HAS_CB' => 'Y',
				'FULL_SET' => array("calendar", "calendar_comment"),
				"COMMENT_EVENT" => array(
					"MODULE_ID" => "calendar",
					"EVENT_ID" => "calendar_comment",
					"OPERATION" => "view",
					"OPERATION_ADD" => "log_rights",
					"ADD_CALLBACK" => array("CCalendarLiveFeed", "AddComment_Calendar"),
					"UPDATE_CALLBACK" => array("CSocNetLogTools", "UpdateComment_Forum"),
					"DELETE_CALLBACK" => array("CSocNetLogTools", "DeleteComment_Forum"),
					"CLASS_FORMAT" => "CSocNetLogTools",
					"METHOD_FORMAT" => "FormatComment_Forum",
					"METHOD_GET_URL" => array("CCalendarLiveFeed", "GetCommentUrl"),
					"RATING_TYPE_ID" => "FORUM_POST"
				)
			)
		);
	}

	public static function FormatEvent($arFields, $arParams, $bMail = false)
	{
		global $APPLICATION, $CACHE_MANAGER;

		$arResult = array(
			"EVENT" => $arFields
		);

		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$CACHE_MANAGER->RegisterTag("CALENDAR_EVENT_".intval($arFields["SOURCE_ID"]));
			$CACHE_MANAGER->RegisterTag("CALENDAR_EVENT_LIST");
		}

		if ($arFields['~PARAMS'] != "")
		{
			$arFields['~PARAMS'] = unserialize($arFields['~PARAMS']);
			if (!is_array($arFields['~PARAMS']))
				$arFields['~PARAMS'] = array();
		}

		$eventViewResult = $APPLICATION->IncludeComponent('bitrix:calendar.livefeed.view', '', array(
			"EVENT_ID" => $arFields["SOURCE_ID"],
			"USER_ID" => $arFields["USER_ID"],
			"PATH_TO_USER" => $arParams["PATH_TO_USER"],
			"MOBILE" => $arParams["MOBILE"],
			"LIVEFEED_ENTRY_PARAMS" => $arFields['~PARAMS']
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);

		$arResult["EVENT_FORMATTED"] = Array(
			"TITLE" => GetMessage("EC_EDEV_EVENT"),
			"TITLE_24" => GetMessage("EC_EDEV_EVENT"),
			"MESSAGE" => $eventViewResult['MESSAGE'],
			"FOOTER_MESSAGE" => $eventViewResult['FOOTER_MESSAGE'],
			"IS_IMPORTANT" => false,
			"STYLE" => "calendar-confirm"
		);

		$eventId = $arFields["SOURCE_ID"];
		if (!$eventId)
			$eventId = 0;

		$calendarUrl = CCalendar::GetPath('user', $arFields["USER_ID"]);

		$arResult["EVENT_FORMATTED"]["URL"] = $calendarUrl.((strpos($calendarUrl, "?") === false) ? '?' : '&').'EVENT_ID='.$eventId;

		$arRights = array();
		$dbRight = CSocNetLogRights::GetList(array(), array("LOG_ID" => $arFields["ID"]));
		while ($arRight = $dbRight->Fetch())
			$arRights[] = $arRight["GROUP_CODE"];

		$arResult["EVENT_FORMATTED"]["DESTINATION"] = CSocNetLogTools::FormatDestinationFromRights($arRights, array_merge($arParams, array("CREATED_BY" => $arFields["USER_ID"])));

		if (isset($eventViewResult['CACHED_JS_PATH']))
			$arResult['CACHED_JS_PATH'] = $eventViewResult['CACHED_JS_PATH'];

		$arResult['ENTITY']['FORMATTED']["NAME"] = "ENTITY FORMATTED NAME";
		$arResult['ENTITY']['FORMATTED']["URL"] = $arResult["EVENT_FORMATTED"]["URL"];

		$arResult['AVATAR_SRC'] = CSocNetLog::FormatEvent_CreateAvatar($arFields, $arParams, 'CREATED_BY');
		$arFieldsTooltip = array(
			'ID' => $arFields['USER_ID'],
			'NAME' => $arFields['~CREATED_BY_NAME'],
			'LAST_NAME' => $arFields['~CREATED_BY_LAST_NAME'],
			'SECOND_NAME' => $arFields['~CREATED_BY_SECOND_NAME'],
			'LOGIN' => $arFields['~CREATED_BY_LOGIN'],
		);
		$arResult['CREATED_BY']['TOOLTIP_FIELDS'] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);

		return $arResult;
	}

	public static function OnSonetLogEntryMenuCreate($arLogEvent)
	{
		if (
			is_array($arLogEvent["FIELDS_FORMATTED"])
			&& is_array($arLogEvent["FIELDS_FORMATTED"]["EVENT"])
			&& array_key_exists("EVENT_ID", $arLogEvent["FIELDS_FORMATTED"]["EVENT"])
			&& $arLogEvent["FIELDS_FORMATTED"]["EVENT"]["EVENT_ID"] == "calendar"
		)
		{
			global $USER;

			if ($USER->GetId() == $arLogEvent["FIELDS_FORMATTED"]["EVENT"]['USER_ID'])
			{
				$eventId = $arLogEvent["FIELDS_FORMATTED"]["EVENT"]["SOURCE_ID"];
				$editUrl = CCalendar::GetPath('user', $arLogEvent["FIELDS_FORMATTED"]["EVENT"]['USER_ID']);
				$editUrl = $editUrl.((strpos($editUrl, "?") === false) ? '?' : '&').'EVENT_ID=EDIT'.$eventId;

				return array(
					array(
						'text' => GetMessage("EC_T_EDIT"),
						'href' => $editUrl
					),
					array(
						'text' => GetMessage("EC_T_DELETE"),
						'onclick' => 'if(window.oViewEventManager[\''.$eventId.'\']){window.oViewEventManager[\''.$eventId.'\'].DeleteEvent();};'
					)
				);
			}
			else
			{
				return false;
			}
		}
		else
			return false;
	}

	// Sync comments from lifefeed to calendar event
	public static function AddComment_Calendar($arFields)
	{
		if (!\Bitrix\Main\Loader::includeModule("forum"))
			return false;

		$ufFileID = array();
		$ufDocID = array();

		$dbResult = CSocNetLog::GetList(
			array(),
			array("ID" => $arFields["LOG_ID"]),
			false,
			false,
			array("ID", "SOURCE_ID", "PARAMS")
		);

		if ($arLog = $dbResult->Fetch())
		{
			if ($arLog['PARAMS'] != "")
			{
				$arLog['PARAMS'] = unserialize($arLog['PARAMS']);
				if (!is_array($arLog['PARAMS']))
					$arLog['PARAMS'] = array();
			}

			$calendarEvent = CCalendarEvent::GetById($arLog["SOURCE_ID"]);
			if ($calendarEvent)
			{
				$calendarSettings = CCalendar::GetSettings();
				$forumID = $calendarSettings["forum_id"];

				if (isset($arLog['PARAMS']['COMMENT_XML_ID']) && $arLog['PARAMS']['COMMENT_XML_ID'])
				{
					$commentXmlId = $arLog['PARAMS']['COMMENT_XML_ID'];
				}
				else
				{
					$commentXmlId = CCalendarEvent::GetEventCommentXmlId($calendarEvent);
					$arLog['PARAMS']['COMMENT_XML_ID'] = $commentXmlId;
					CSocNetLog::Update($arFields["LOG_ID"], array(
						"PARAMS" => serialize($arLog['PARAMS'])
					));
				}

				if ($forumID)
				{
					$dbTopic = CForumTopic::GetList(null, array(
						"FORUM_ID" => $forumID,
						"XML_ID" => $commentXmlId
					));

					if ($dbTopic && ($arTopic = $dbTopic->Fetch()))
						$topicID = $arTopic["ID"];
					else
						$topicID = 0;

					$currentUserId = CCalendar::GetCurUserId();
					$strPermission = ($currentUserId == $calendarEvent["OWNER_ID"] ? "Y" : "M");

					$arFieldsMessage = array(
						"POST_MESSAGE" => $arFields["TEXT_MESSAGE"],
						"USE_SMILES" => "Y",
						"PERMISSION_EXTERNAL" => "Q",
						"PERMISSION" => $strPermission,
						"APPROVED" => "Y"
					);

					if ($topicID === 0)
					{
						$arFieldsMessage["TITLE"] = "EVENT_".$arLog["SOURCE_ID"];
						$arFieldsMessage["TOPIC_XML_ID"] = "EVENT_".$arLog["SOURCE_ID"];
					}

					$arTmp = false;
					$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("SONET_COMMENT", $arTmp);
					if (is_array($arTmp))
					{
						if (array_key_exists("UF_SONET_COM_DOC", $arTmp))
							$GLOBALS["UF_FORUM_MESSAGE_DOC"] = $arTmp["UF_SONET_COM_DOC"];
						elseif (array_key_exists("UF_SONET_COM_FILE", $arTmp))
						{
							$arFieldsMessage["FILES"] = array();
							foreach($arTmp["UF_SONET_COM_FILE"] as $file_id)
								$arFieldsMessage["FILES"][] = array("FILE_ID" => $file_id);
						}
					}

					$messageID = ForumAddMessage(($topicID > 0 ? "REPLY" : "NEW"), $forumID, $topicID, 0, $arFieldsMessage, $sError, $sNote);

					// get UF DOC value and FILE_ID there
					if ($messageID > 0)
					{
						$messageUrl = self::GetCommentUrl(array(
							"ENTRY_ID" => $calendarEvent["ID"],
							"ENTRY_USER_ID" => $calendarEvent["OWNER_ID"],
							"COMMENT_ID" => $messageID
						));

						$dbAddedMessageFiles = CForumFiles::GetList(array("ID" => "ASC"), array("MESSAGE_ID" => $messageID));
						while ($arAddedMessageFiles = $dbAddedMessageFiles->Fetch())
							$ufFileID[] = $arAddedMessageFiles["FILE_ID"];

						$ufDocID = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MESSAGE_DOC", $messageID, LANGUAGE_ID);
					}
				}
			}
		}

		if (!$messageID)
			$sError = GetMessage("EC_LF_ADD_COMMENT_SOURCE_ERROR");

		return array(
			"SOURCE_ID" => $messageID,
			"MESSAGE" => ($arFieldsMessage ? $arFieldsMessage["POST_MESSAGE"] : false),
			"RATING_TYPE_ID" => "FORUM_POST",
			"RATING_ENTITY_ID" => $messageID,
			"ERROR" => $sError,
			"NOTES" => $sNote,
			"UF" => array(
				"FILE" => $ufFileID,
				"DOC" => $ufDocID
			),
			"URL" => $messageUrl
		);
	}

	public static function GetCommentUrl($arFields = array())
	{
		$messageUrl = '';

		if (
			is_array($arFields)
			&& !empty($arFields["ENTRY_ID"])
			&& !empty($arFields["ENTRY_USER_ID"])
		)
		{
			$messageUrl = CCalendar::GetPath("user", $arFields["ENTRY_USER_ID"]);
			$messageUrl = $messageUrl.((strpos($messageUrl, "?") === false) ? "?" : "&")."EVENT_ID=".$arFields["ENTRY_ID"]."&MID=#ID#";

			if (!empty($arFields["COMMENT_ID"]))
			{
				$messageUrl = str_replace('#ID#', intval($arFields["COMMENT_ID"]), $messageUrl);
			}
		}

		return $messageUrl;
	}

	public static function OnAfterSonetLogEntryAddComment($arSonetLogComment)
	{
		if ($arSonetLogComment["EVENT_ID"] != "calendar_comment")
			return;

		$dbLog = CSocNetLog::GetList(
			array(),
			array(
				"ID" => $arSonetLogComment["LOG_ID"],
				"EVENT_ID" => "calendar"
			),
			false,
			false,
			array("ID", "SOURCE_ID", "PARAMS")
		);

		if (
			($arLog = $dbLog->Fetch())
			&& (intval($arLog["SOURCE_ID"]) > 0)
		)
		{
			CCalendarNotify::NotifyComment(
				$arLog["SOURCE_ID"],
				array(
					"LOG" => $arLog,
					"LOG_ID" => $arLog["ID"],
					"USER_ID" => $arSonetLogComment["USER_ID"],
					"MESSAGE" => $arSonetLogComment["MESSAGE"],
					"URL" => $arSonetLogComment["URL"]
				)
			);
		}

	}

	public static function OnForumCommentIMNotify($entityType, $eventId, $comment)
	{
		if (
			$entityType != "EV"
			|| !\Bitrix\Main\Loader::includeModule("im")
		)
		{
			return;
		}

		if (
			isset($comment["MESSAGE_ID"])
			&& intval($comment["MESSAGE_ID"]) > 0
			&& ($calendarEvent = CCalendarEvent::GetById($eventId))
		)
		{
			$comment["URL"] = CCalendar::GetPath("user", $calendarEvent["OWNER_ID"], true);
			$comment["URL"] .= ((strpos($comment["URL"], "?") === false) ? "?" : "&")."EVENT_ID=".$calendarEvent["ID"]."&MID=".intval($comment["MESSAGE_ID"]);
		}

		CCalendarNotify::NotifyComment($eventId, $comment);
	}

	public static function OnAfterCommentAddBefore($entityType, $eventId, $arData)
	{
		global $DB;

		if ($entityType != "EV")
			return;


		$res = array();
		$logId = false;
		$commentXmlId = $arData['PARAMS']['XML_ID'];
		$parentRes = false;

		// Simple events have simple id's like "EVENT_".$eventId, for them
		// we don't want to create second socnet log entry (mantis: 82011)
		if ($commentXmlId !== "EVENT_".$eventId)
		{
			$dbRes = CSocNetLog::GetList(array("ID" => "DESC"), array("EVENT_ID" => "calendar", "SOURCE_ID" => $eventId), false, false, array("ID", "ENTITY_ID", "USER_ID", "TITLE", "MESSAGE", "SOURCE_ID", "PARAMS"));

			$createNewSocnetLogEntry = true;
			while($arRes = $dbRes->Fetch())
			{
				if($arRes['PARAMS'] != "")
				{
					$arRes['PARAMS'] = unserialize($arRes['PARAMS']);
					if(!is_array($arRes['PARAMS']))
						$arRes['PARAMS'] = array();
				}

				if(isset($arRes['PARAMS']['COMMENT_XML_ID']) && $arRes['PARAMS']['COMMENT_XML_ID'] === $commentXmlId)
				{
					$logId = $arRes['ID'];
					$createNewSocnetLogEntry = false;
				}
				else
				{
					$parentRes = $arRes;
				}
			}

			if ($createNewSocnetLogEntry && $parentRes)
			{
				$arSoFields = Array(
					"ENTITY_TYPE" => SONET_SUBSCRIBE_ENTITY_USER,
					"ENTITY_ID" => $parentRes["ENTITY_ID"],
					"EVENT_ID" => "calendar",
					"USER_ID" => $parentRes["USER_ID"],
					"SITE_ID" => SITE_ID,
					"TITLE_TEMPLATE" => "#TITLE#",
					"TITLE" => $parentRes["TITLE"],
					"MESSAGE" => $parentRes["MESSAGE"],
					"TEXT_MESSAGE" => '',
					"SOURCE_ID" => $parentRes["SOURCE_ID"],
					"ENABLE_COMMENTS" => "Y",
					"CALLBACK_FUNC" => false,
					"=LOG_DATE" =>$DB->CurrentTimeFunction(),
					"PARAMS" => serialize(array(
						"COMMENT_XML_ID" => $commentXmlId
					))
				);
				$logId = CSocNetLog::Add($arSoFields, false);

				$arCodes = array();
				$rsRights = CSocNetLogRights::GetList(array(), array("LOG_ID" => $parentRes["ID"]));

				while ($arRights = $rsRights->Fetch())
				{
					$arCodes[] = $arRights['GROUP_CODE'];
				}
				CSocNetLogRights::Add($logId, $arCodes);
			}
		}

		if ($logId)
			$res['LOG_ENTRY_ID'] = $logId;

		return $res;
	}

	public static function OnAfterCommentAddAfter($entityType, $eventID, $arData, $logID = false)
	{
		if ($entityType != "EV")
			return;

		if (intval($logID) <= 0)
			return;

		CCalendarLiveFeed::SetCommentFileRights($arData, $logID);
	}

	public static function OnAfterCommentUpdateAfter($entityType, $eventID, $arData, $logID = false)
	{
		if ($entityType != "EV")
			return;

		if (intval($logID) <= 0)
			return;

		if (
			!is_array($arData)
			|| !array_key_exists("ACTION", $arData)
			|| $arData["ACTION"] != "EDIT"
		)
			return;

		CCalendarLiveFeed::SetCommentFileRights($arData, $logID);
	}

	public static function SetCommentFileRights($arData, $logID)
	{
		if (intval($logID) <= 0)
			return;

		$arAccessCodes = array();
		$dbRight = CSocNetLogRights::GetList(array(), array("LOG_ID" => $logID));
		while ($arRight = $dbRight->Fetch())
			$arAccessCodes[] = $arRight["GROUP_CODE"];

		$arFilesIds = $arData["PARAMS"]["UF_FORUM_MESSAGE_DOC"];
		$UF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("FORUM_MESSAGE", $arData["MESSAGE_ID"], LANGUAGE_ID);
		CCalendar::UpdateUFRights($arFilesIds, $arAccessCodes, $UF["UF_FORUM_MESSAGE_DOC"]);
	}

	public static function EditCalendarEventEntry($arFields = array(), $arUFFields = array(), $arAccessCodes = array(), $params = array())
	{
		global $DB;

		if (!$arFields['SKIP_TIME'])
		{
			$arFields['DATE_FROM'] .= ' '.$arFields['TIME_FROM'];
			$arFields['DATE_TO'] .= ' '.$arFields['TIME_TO'];
		}

		// Timezone
		if (!$arFields['TZ_FROM'] && isset($arFields['DEFAULT_TZ']))
		{
			$arFields['TZ_FROM'] = $arFields['DEFAULT_TZ'];
		}
		if (!$arFields['TZ_TO'] && isset($arFields['DEFAULT_TZ']))
		{
			$arFields['TZ_TO'] = $arFields['DEFAULT_TZ'];
		}

		if (isset($arFields['DEFAULT_TZ']) && $arFields['DEFAULT_TZ'] != '')
		{
			CCalendar::SaveUserTimezoneName($params["userId"], $arFields['DEFAULT_TZ']);
		}

		if ($arFields['SECTION'])
			$arFields['SECTIONS'] = array($arFields['SECTION']);

		$arFields["OWNER_ID"] = $params["userId"];
		$arFields["CAL_TYPE"] = $params["type"];

		// Add author for new event
		if (!$arFields["ID"])
			$arAccessCodes[] = 'U'.$params["userId"];

		$arAccessCodes = array_unique($arAccessCodes);
		$arAttendees = CCalendar::GetDestinationUsers($arAccessCodes);

		if (trim($arFields["NAME"]) === '')
			$arFields["NAME"] = GetMessage('EC_DEFAULT_EVENT_NAME');

		$arFields['IS_MEETING'] = !empty($arAttendees) && $arAttendees != array($params["userId"]);

		if (isset($arFields['RRULE']) && !empty($arFields['RRULE']))
		{
			if (is_array($arFields['RRULE']['BYDAY']))
				$arFields['RRULE']['BYDAY'] = implode(',', $arFields['RRULE']['BYDAY']);
		}

		if ($arFields['IS_MEETING'])
		{
			$arFields['ATTENDEES_CODES'] = $arAccessCodes;
			$arFields['ATTENDEES'] = $arAttendees;
			$arFields['MEETING_HOST'] = $params["userId"];
			$arFields['MEETING'] = array(
				'HOST_NAME' => CCalendar::GetUserName($params["userId"]),
				'TEXT' => '',
				'OPEN' => false,
				'NOTIFY' => true,
				'REINVITE' => false
			);
		}
		else
		{
			$arFields['ATTENDEES'] = false;
		}

		$eventId = CCalendar::SaveEvent(
			array(
				'arFields' => $arFields,
				'autoDetectSection' => true
			)
		);

		if ($eventId > 0)
		{
			if (count($arUFFields) > 0)
				CCalendarEvent::UpdateUserFields($eventId, $arUFFields);

			foreach($arAccessCodes as $key => $value)
				if ($value == "UA")
				{
					unset($arAccessCodes[$key]);
					$arAccessCodes[] = "G2";
					break;
				}

			if ($arFields['IS_MEETING'] && !empty($arUFFields['UF_WEBDAV_CAL_EVENT']))
			{
				$UF = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields("CALENDAR_EVENT", $eventId, LANGUAGE_ID);
				CCalendar::UpdateUFRights($arUFFields['UF_WEBDAV_CAL_EVENT'], $arAccessCodes, $UF['UF_WEBDAV_CAL_EVENT']);
			}

			$arSoFields = Array(
				"ENTITY_TYPE" => SONET_SUBSCRIBE_ENTITY_USER,
				"ENTITY_ID" => $params["userId"],
				"USER_ID" => $params["userId"],
				"=LOG_DATE" => $DB->CurrentTimeFunction(),
				"TITLE_TEMPLATE" => "#TITLE#",
				"TITLE" => $arFields["NAME"],
				"MESSAGE" => '',
				"TEXT_MESSAGE" => ''
			);

			$dbRes = CSocNetLog::GetList(
				array("ID" => "DESC"),
				array(
					"EVENT_ID" => "calendar",
					"SOURCE_ID" => $eventId
				),
				false,
				false,
				array("ID")
			);

			$arCodes = array();
			foreach($arAccessCodes as $value)
			{
				if (substr($value, 0, 2) === 'SG')
					$arCodes[] = $value.'_K';
				$arCodes[] = $value;
			}
			$arCodes = array_unique($arCodes);

			if ($arRes = $dbRes->Fetch())
			{
				CSocNetLog::Update($arRes["ID"], $arSoFields);
				CSocNetLogRights::DeleteByLogID($arRes["ID"]);
				CSocNetLogRights::Add($arRes["ID"], $arCodes);
			}
			else
			{
				$arSoFields = array_merge($arSoFields, array(
					"EVENT_ID" => "calendar",
					"SITE_ID" => SITE_ID,
					"SOURCE_ID" => $eventId,
					"ENABLE_COMMENTS" => "Y",
					"CALLBACK_FUNC" => false
				));

				$logID = CSocNetLog::Add($arSoFields, false);
				CSocNetLogRights::Add($logID, $arCodes);
			}
		}
	}

	// Called after creation or edition of calendar event
	public static function OnEditCalendarEventEntry($params)
	{
		global $DB;

		$eventId = intval($params['eventId']);
		$arFields = $params['arFields'];
		$attendeesCodes = $params['attendeesCodes'];

		if (isset($attendeesCodes) && !is_array($attendeesCodes))
			$attendeesCodes = explode(',', $attendeesCodes);
		if (!is_array($attendeesCodes))
			$attendeesCodes = array();

		$newlogId = false;

		if ($eventId > 0)
		{
			$arSoFields = Array(
				"ENTITY_ID" => ($arFields["OWNER_ID"] > 0 ? $arFields["OWNER_ID"] : 1),
				"USER_ID" => $arFields["CREATED_BY"],
				"=LOG_DATE" =>$DB->CurrentTimeFunction(),
				"TITLE_TEMPLATE" => "#TITLE#",
				"TITLE" => $arFields["NAME"],
				"MESSAGE" => "",
				"TEXT_MESSAGE" => ""
			);

			$arAccessCodes = array();
			foreach($attendeesCodes as $value)
			{
				if ($value == "UA")
					$arAccessCodes[] = "G2";
				else
					$arAccessCodes[] = $value;
			}

			$dbRes = CSocNetLog::GetList(
				array("ID" => "DESC"),
				array(
					"EVENT_ID" => "calendar",
					"SOURCE_ID" => $eventId
				),
				false,
				false,
				array("ID")
			);

			$arCodes = array();
			foreach($arAccessCodes as $value)
			{
				if (substr($value, 0, 2) === 'SG')
					$arCodes[] = $value.'_K';
				$arCodes[] = $value;
			}

			if ($arFields['IS_MEETING'] && $arFields['MEETING_HOST'] && !in_array('U'.$arFields['MEETING_HOST'], $arCodes))
			{
				$arCodes[] = 'U'.$arFields['MEETING_HOST'];
			}
			$arCodes = array_unique($arCodes);

			if ($arRes = $dbRes->Fetch())
			{
				if (
					isset($arRes["ID"])
					&& intval($arRes["ID"]) > 0
				)
				{
					CSocNetLog::Update($arRes["ID"], $arSoFields);
					CSocNetLogRights::DeleteByLogID($arRes["ID"]);
					CSocNetLogRights::Add($arRes["ID"], $arCodes);
				}
			}
			else
			{
				$arSoFields = array_merge($arSoFields, array(
					"ENTITY_TYPE" => SONET_SUBSCRIBE_ENTITY_USER,
					"EVENT_ID" => "calendar",
					"SITE_ID" => SITE_ID,
					"SOURCE_ID" => $eventId,
					"ENABLE_COMMENTS" => "Y",
					"CALLBACK_FUNC" => false
				));

				$newlogId = CSocNetLog::Add($arSoFields, false);
				CSocNetLogRights::Add($newlogId, $arCodes);
			}

			// Find if we already have socialnetwork livefeed entry for this event
			if ($newlogId && $arFields['RECURRENCE_ID'] > 0)
			{
				$commentXmlId = false;
				if ($arFields['RELATIONS'])
				{
					if(!isset($arFields['~RELATIONS']) || !is_array($arFields['~RELATIONS']))
					{
						$arFields['~RELATIONS'] = unserialize($arFields['RELATIONS']);
					}
					if (is_array($arFields['~RELATIONS']) && array_key_exists('COMMENT_XML_ID', $arFields['~RELATIONS']) && $arFields['~RELATIONS']['COMMENT_XML_ID'])
					{
						$commentXmlId = $arFields['~RELATIONS']['COMMENT_XML_ID'];
					}
				}

				$dbRes = CSocNetLog::GetList(
					array("ID" => "DESC"),
					array(
						"EVENT_ID" => "calendar",
						"SOURCE_ID" => $arFields['RECURRENCE_ID']
					),
					false,
					false,
					array("ID", "SOURCE_ID", "PARAMS", "COMMENTS_COUNT")
				);


				$event = CCalendarEvent::GetById($arFields['RECURRENCE_ID']);

				$rrule = CCalendarEvent::ParseRRULE($event['RRULE']);
				$until = $rrule['~UNTIL'];

				while ($arRes = $dbRes->Fetch())
				{
					if ($arRes['PARAMS'] != "")
					{
						$arRes['PARAMS'] = unserialize($arRes['PARAMS']);
						if (!is_array($arRes['PARAMS']))
							$arRes['PARAMS'] = array();
					}

					if (isset($arRes['PARAMS']['COMMENT_XML_ID']))
					{
						if ($commentXmlId && $arRes['PARAMS']['COMMENT_XML_ID'] === $commentXmlId)
						{
							// Move comments from old entry to new one
							CSocNetLogComments::BatchUpdateLogId($arRes['ID'], $newlogId);

							// Delete old entry
							CSocNetLog::Delete($arRes['ID']);

							// Update comments count for new entry
							// And put COMMENT_XML_ID from old antry to preserve syncrinization
							CSocNetLog::Update($newlogId, array(
								"COMMENTS_COUNT" => intval($arRes['COMMENTS_COUNT']),
								"PARAMS" => serialize(array(
									"COMMENT_XML_ID" => $commentXmlId
								))
							));
						}
						else
						{
							$instanceDate = CCalendarEvent::ExtractDateFromCommentXmlId($arRes['PARAMS']['COMMENT_XML_ID']);
							if ($instanceDate && $until)
							{
								$untilTs = CCalendar::Timestamp($until);
								$instanceDateTs = CCalendar::Timestamp($instanceDate);
								if ($instanceDateTs >= $untilTs)
								{
									CSocNetLog::Update($arRes['ID'], array(
										"SOURCE_ID" => $eventId
									));
								}
							}
						}
					}
				}
			}
		}
	}

	// Do delete from socialnetwork live feed here
	public static function OnDeleteCalendarEventEntry($eventId)
	{
		if (\Bitrix\Main\Loader::includeModule("socialnetwork"))
		{
			$dbRes = CSocNetLog::GetList(
				array("ID" => "DESC"),
				array(
					"EVENT_ID" => "calendar",
					"SOURCE_ID" => $eventId
				),
				false,
				false,
				array("ID")
			);
			while ($arRes = $dbRes->Fetch())
			{
				CSocNetLog::Delete($arRes["ID"]);
			}
		}
	}

	public static function FixForumCommentURL($arData)
	{
		if(
			in_array($arData["MODULE_ID"], array("forum", "FORUM"))
			&& $arData['ENTITY_TYPE_ID'] === 'FORUM_POST'
			&& intval($arData['PARAM1']) > 0
			&& preg_match('/^EVENT_([0-9]+)/', $arData["TITLE"], $match)
		)
		{
			$arCalendarSettings = CCalendar::GetSettings();
			$forumID = $arCalendarSettings["forum_id"];
			$eventID = intval($match[1]);

			if (
				intval($arData['PARAM1']) == $forumID
				&& $eventID > 0
				&& !empty($arCalendarSettings["pathes"])
				&& ($arCalendarEvent = CCalendarEvent::GetById($eventID))
				&& strlen($arCalendarEvent["CAL_TYPE"]) > 0
				&& in_array($arCalendarEvent["CAL_TYPE"], array("user", "group"))
				&& intval($arCalendarEvent["OWNER_ID"]) > 0
			)
			{
				foreach ($arData['LID'] as $siteId => $value)
				{
					$messageUrl = false;

					if (
						array_key_exists($siteId, $arCalendarSettings["pathes"])
						&& is_array($arCalendarSettings["pathes"][$siteId])
						&& !empty($arCalendarSettings["pathes"][$siteId])
					)
					{
						if ($arCalendarEvent["CAL_TYPE"] == "user")
						{
							if (
								array_key_exists("path_to_user_calendar", $arCalendarSettings["pathes"][$siteId])
								&& !empty($arCalendarSettings["pathes"][$siteId]["path_to_user_calendar"])
							)
							{
								$messageUrl = CComponentEngine::MakePathFromTemplate(
									$arCalendarSettings["pathes"][$siteId]["path_to_user_calendar"],
									array(
										"user_id" => $arCalendarEvent['OWNER_ID'],
									)
								);
							}
						}
						else
						{
							if (
								array_key_exists("path_to_group_calendar", $arCalendarSettings["pathes"][$siteId])
								&& !empty($arCalendarSettings["pathes"][$siteId]["path_to_group_calendar"])
							)
							{
								$messageUrl = CComponentEngine::MakePathFromTemplate(
									$arCalendarSettings["pathes"][$siteId]["path_to_group_calendar"],
									array(
										"group_id" => $arCalendarEvent['OWNER_ID'],
									)
								);
							}
						}
					}

					$arData['LID'][$siteId] = ($messageUrl ? $messageUrl."?EVENT_ID=".$arCalendarEvent["ID"]."&MID=".$arData['ENTITY_ID']."#message".$arData['ENTITY_ID'] : "");
				}

				return $arData;
			}

			return array(
				"TITLE" => "",
				"BODY" => ""
			);
		}
	}

}
?>