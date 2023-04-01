<?php

use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Main\Loader;

IncludeModuleLangFile(__FILE__);

class CCalendarLiveFeed
{
	public static function AddEvent(&$arSocNetFeaturesSettings): void
	{
		$arSocNetFeaturesSettings['calendar']['subscribe_events'] = array(
			'calendar' => array(
				'ENTITIES' => array(
					SONET_SUBSCRIBE_ENTITY_USER => []
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

	public static function FormatEvent($arFields, $arParams): array
	{
		global $APPLICATION, $CACHE_MANAGER;

		$arResult = array(
			"EVENT" => $arFields
		);

		if (defined("BX_COMP_MANAGED_CACHE"))
		{
			$CACHE_MANAGER->RegisterTag("CALENDAR_EVENT_" . (int)$arFields["SOURCE_ID"]);
			$CACHE_MANAGER->RegisterTag("CALENDAR_EVENT_LIST");
		}

		if ((string)$arFields['~PARAMS'] !== "")
		{
			$arFields['~PARAMS'] = unserialize($arFields['~PARAMS'], ['allowed_classes' => false]);
			if (!is_array($arFields['~PARAMS']))
			{
				$arFields['~PARAMS'] = [];
			}
		}

		$eventViewResult = $APPLICATION->IncludeComponent('bitrix:calendar.livefeed.view', '', array(
			"EVENT_ID" => $arFields["SOURCE_ID"],
			"USER_ID" => $arFields["USER_ID"],
			"PATH_TO_USER" => $arParams["PATH_TO_USER"],
			"MOBILE" => ($arParams["MOBILE"] ?? null),
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
		{
			$eventId = 0;
		}

		$calendarUrl = CCalendar::GetPath('user', $arFields["USER_ID"]);

		$arResult["EVENT_FORMATTED"]["URL"] = $calendarUrl.((mb_strpos($calendarUrl, "?") === false) ? '?' : '&').'EVENT_ID='.$eventId;

		$arRights = [];
		$dbRight = CSocNetLogRights::GetList([], array("LOG_ID" => $arFields["ID"]));
		while ($arRight = $dbRight->Fetch())
		{
			$arRights[] = $arRight["GROUP_CODE"];
		}

		$arResult["EVENT_FORMATTED"]["DESTINATION"] = CSocNetLogTools::FormatDestinationFromRights($arRights, array_merge($arParams, array("CREATED_BY" => $arFields["USER_ID"])));

		if (isset($eventViewResult['CACHED_JS_PATH']))
		{
			$arResult['CACHED_JS_PATH'] = $eventViewResult['CACHED_JS_PATH'];
		}

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
			&& $arLogEvent["FIELDS_FORMATTED"]["EVENT"]["EVENT_ID"] === "calendar"
		)
		{
			global $USER;

			if ((int)$USER->GetId() === (int)$arLogEvent["FIELDS_FORMATTED"]["EVENT"]['USER_ID'])
			{
				$eventId = $arLogEvent["FIELDS_FORMATTED"]["EVENT"]["SOURCE_ID"];
				$editUrl = CCalendar::GetPath('user', $arLogEvent["FIELDS_FORMATTED"]["EVENT"]['USER_ID']);
				$editUrl .= ((mb_strpos($editUrl, "?") === false) ? '?' : '&') . 'EVENT_ID=EDIT' . $eventId;

				return array(
					array(
						'text' => GetMessage("EC_T_EDIT"),
						'href' => $editUrl
					),
					array(
						'text' => GetMessage("EC_T_DELETE"),
						'onclick' => 'if (window.oViewEventManager[\''.$eventId.'\']){window.oViewEventManager[\''.$eventId.'\'].DeleteEvent();};'
					)
				);
			}

			return false;
		}

		return false;
	}

	// Sync comments from lifefeed to calendar event
	public static function AddComment_Calendar($arFields)
	{
		if (!Loader::includeModule('forum'))
		{
			return false;
		}

		$messageID = null;
		$arFieldsMessage = null;
		$sError = null;
		$ufFileID = [];
		$ufDocID = [];

		$dbResult = CSocNetLog::GetList(
			[],
			['ID' => $arFields['LOG_ID']],
			false,
			false,
			[
				'ID',
				'SOURCE_ID',
				'PARAMS',
			]
		);

		if ($arLog = $dbResult->Fetch())
		{
			if ((string)$arLog['PARAMS'] !== '')
			{
				$arLog['PARAMS'] = unserialize($arLog['PARAMS'], ['allowed_classes' => false]);
				if (!is_array($arLog['PARAMS']))
				{
					$arLog['PARAMS'] = [];
				}
			}

			$calendarEvent = CCalendarEvent::GetList([
				'arFilter' => [
					'ID' => $arLog['SOURCE_ID'],
					'DELETED' => 'N'
				],
				'parseRecursion' => true,
				'maxInstanceCount' => 1,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'setDefaultLimit' => false
			]);

			if ($calendarEvent && is_array($calendarEvent[0]))
			{
				$calendarEvent = $calendarEvent[0];
				$calendarSettings = CCalendar::GetSettings();
				$forumID = $calendarSettings['forum_id'];

				if (isset($arLog['PARAMS']['COMMENT_XML_ID']) && $arLog['PARAMS']['COMMENT_XML_ID'])
				{
					$commentXmlId = $arLog['PARAMS']['COMMENT_XML_ID'];
				}
				else
				{
					$commentXmlId = CCalendarEvent::GetEventCommentXmlId($calendarEvent);

					if (!$arLog['PARAMS'])
					{
						$arLog['PARAMS'] = [];
					}
					$arLog['PARAMS']['COMMENT_XML_ID'] = $commentXmlId;
					CSocNetLog::Update($arFields['LOG_ID'], ['PARAMS' => serialize($arLog['PARAMS'])]);
				}

				if ($forumID)
				{
					$dbTopic = CForumTopic::GetList(null, [
						'FORUM_ID' => $forumID,
						'XML_ID' => $commentXmlId
					]);

					if ($dbTopic && ($arTopic = $dbTopic->Fetch()))
					{
						$topicID = $arTopic['ID'];
					}
					else
					{
						$topicID = 0;
					}

					$currentUserId = CCalendar::GetCurUserId();
					$strPermission = ($currentUserId === (int)$calendarEvent['OWNER_ID'] ? 'Y' : 'M');

					$arFieldsMessage = [
						'POST_MESSAGE' => $arFields['TEXT_MESSAGE'],
						'USE_SMILES' => 'Y',
						'PERMISSION_EXTERNAL' => 'Q',
						'PERMISSION' => $strPermission,
						'APPROVED' => 'Y'
					];

					if ($topicID === 0)
					{
						$arFieldsMessage['TITLE'] = 'EVENT_'.$arLog['SOURCE_ID'];
						$arFieldsMessage['TOPIC_XML_ID'] = 'EVENT_'.$arLog['SOURCE_ID'];
					}

					$arTmp = false;
					$GLOBALS['USER_FIELD_MANAGER']->EditFormAddFields('SONET_COMMENT', $arTmp);
					if (is_array($arTmp))
					{
						if (array_key_exists('UF_SONET_COM_DOC', $arTmp))
						{
							$GLOBALS['UF_FORUM_MESSAGE_DOC'] = $arTmp['UF_SONET_COM_DOC'];
						}
						else if (array_key_exists('UF_SONET_COM_FILE', $arTmp))
						{
							$arFieldsMessage['FILES'] = [];
							foreach ($arTmp['UF_SONET_COM_FILE'] as $file_id)
							{
								$arFieldsMessage['FILES'][] = ['FILE_ID' => $file_id];
							}
						}
					}

					$messageID = ForumAddMessage(($topicID > 0 ? 'REPLY' : 'NEW'), $forumID, $topicID, 0, $arFieldsMessage, $sError, $sNote);

					// get UF DOC value and FILE_ID there
					if ($messageID > 0)
					{
						$messageUrl = self::GetCommentUrl([
							'ENTRY_ID' => $calendarEvent['ID'],
							'ENTRY_USER_ID' => $calendarEvent['OWNER_ID'],
							'COMMENT_ID' => $messageID
						]);

						$dbAddedMessageFiles = CForumFiles::GetList(
							['ID' => 'ASC'],
							['MESSAGE_ID' => $messageID]
						);
						while ($arAddedMessageFiles = $dbAddedMessageFiles->Fetch())
						{
							$ufFileID[] = $arAddedMessageFiles['FILE_ID'];
						}

						$ufDocID = $GLOBALS['USER_FIELD_MANAGER']->GetUserFieldValue('FORUM_MESSAGE', 'UF_FORUM_MESSAGE_DOC', $messageID, LANGUAGE_ID);
					}
				}
			}
		}

		if (!$messageID)
		{
			$sError = GetMessage('EC_LF_ADD_COMMENT_SOURCE_ERROR');
		}

		return [
			'SOURCE_ID' => $messageID,
			'MESSAGE' => ($arFieldsMessage ? $arFieldsMessage['POST_MESSAGE'] : false),
			'RATING_TYPE_ID' => 'FORUM_POST',
			'RATING_ENTITY_ID' => $messageID,
			'ERROR' => $sError,
			'NOTES' => $sNote,
			'UF' => [
				'FILE' => $ufFileID,
				'DOC' => $ufDocID
			],
			'URL' => $messageUrl ?? null
		];
	}

	public static function GetCommentUrl($arFields = [])
	{
		$messageUrl = '';

		if (
			is_array($arFields)
			&& !empty($arFields["ENTRY_ID"])
			&& !empty($arFields["ENTRY_USER_ID"])
		)
		{
			$messageUrl = CCalendar::GetPath("user", $arFields["ENTRY_USER_ID"]);
			$messageUrl .= ((mb_strpos($messageUrl, "?") === false) ? "?" : "&") . "EVENT_ID=" . $arFields["ENTRY_ID"] . "&MID=#ID#";

			if (!empty($arFields["COMMENT_ID"]))
			{
				$messageUrl = str_replace('#ID#', (int)$arFields["COMMENT_ID"], $messageUrl);
			}
		}

		return $messageUrl;
	}

	public static function OnAfterSonetLogEntryAddComment($arSonetLogComment): void
	{
		if ($arSonetLogComment["EVENT_ID"] !== "calendar_comment")
		{
			return;
		}

		$dbLog = CSocNetLog::GetList(
			[],
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
			&& ((int)$arLog["SOURCE_ID"] > 0)
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

	public static function OnForumCommentIMNotify($entityType, $eventId, $comment): void
	{
		if (
			$entityType !== "EV"
			|| !Loader::includeModule("im")
		)
		{
			return;
		}

		if (
			isset($comment["MESSAGE_ID"])
			&& (int)$comment["MESSAGE_ID"] > 0
			&& ($calendarEvent = CCalendarEvent::GetById($eventId))
		)
		{
			$comment["URL"] = CCalendar::GetPath("user", $calendarEvent["OWNER_ID"], true);
			$comment["URL"] .= ((mb_strpos($comment["URL"], "?") === false) ? "?" : "&") . "EVENT_ID=".$calendarEvent["ID"] . "&MID=" . (int)$comment["MESSAGE_ID"];
		}

		CCalendarNotify::NotifyComment($eventId, $comment);
	}

	public static function OnAfterCommentAddBefore($entityType, $eventId, $arData)
	{
		if ($entityType !== "EV")
		{
			return;
		}

		$res = [];
		$logId = false;
		$commentXmlId = $arData['PARAMS']['XML_ID'];
		$parentRes = false;

		// Simple events have simple id's like "EVENT_".$eventId, for them
		// we don't want to create second socnet log entry (mantis: 82011)
		if ($commentXmlId !== "EVENT_".$eventId)
		{
			$dbRes = CSocNetLog::GetList(array("ID" => "DESC"), array("EVENT_ID" => "calendar", "SOURCE_ID" => $eventId), false, false, array("ID", "ENTITY_ID", "USER_ID", "TITLE", "MESSAGE", "SOURCE_ID", "PARAMS"));

			$createNewSocnetLogEntry = true;
			while ($arRes = $dbRes->Fetch())
			{
				if ((string)$arRes['PARAMS'] !== "")
				{
					$arRes['PARAMS'] = unserialize($arRes['PARAMS'], ['allowed_classes' => false]);
					if (!is_array($arRes['PARAMS']))
					{
						$arRes['PARAMS'] = [];
					}
				}

				if (isset($arRes['PARAMS']['COMMENT_XML_ID']) && $arRes['PARAMS']['COMMENT_XML_ID'] === $commentXmlId)
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
				$arSoFields = [
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
					"=LOG_DATE" => CDatabase::CurrentTimeFunction(),
					"PARAMS" => serialize([
						"COMMENT_XML_ID" => $commentXmlId
					])
				];
				$logId = CSocNetLog::Add($arSoFields, false);

				$arCodes = [];
				$rsRights = CSocNetLogRights::GetList([], array("LOG_ID" => $parentRes["ID"]));

				while ($arRights = $rsRights->Fetch())
				{
					$arCodes[] = $arRights['GROUP_CODE'];
				}
				CSocNetLogRights::Add($logId, $arCodes);
			}
		}

		if ($logId)
		{
			$res['LOG_ENTRY_ID'] = $logId;
		}

		return $res;
	}

	public static function OnAfterCommentAddAfter($entityType, $eventID, $arData, $logID = false): void
	{
		if ($entityType !== "EV")
		{
			return;
		}

		if ((int)$logID <= 0)
		{
			return;
		}

		self::SetCommentFileRights($arData, $logID);
	}

	public static function OnAfterCommentUpdateAfter($entityType, $eventID, $arData, $logID = false): void
	{
		if ($entityType !== "EV")
		{
			return;
		}

		if ((int)$logID <= 0)
		{
			return;
		}

		if (
			!is_array($arData)
			|| !array_key_exists("ACTION", $arData)
			|| $arData["ACTION"] !== "EDIT"
		)
		{
			return;
		}

		self::SetCommentFileRights($arData, $logID);
	}

	public static function SetCommentFileRights($arData, $logID): void
	{
		if ((int)$logID <= 0)
		{
			return;
		}

		$arAccessCodes = [];
		$dbRight = CSocNetLogRights::GetList([], array("LOG_ID" => $logID));
		while ($arRight = $dbRight->Fetch())
		{
			$arAccessCodes[] = $arRight["GROUP_CODE"];
		}

		$arFilesIds = $arData["PARAMS"]["UF_FORUM_MESSAGE_DOC"];
		$UF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("FORUM_MESSAGE", $arData["MESSAGE_ID"], LANGUAGE_ID);
		CCalendar::UpdateUFRights($arFilesIds, $arAccessCodes, $UF["UF_FORUM_MESSAGE_DOC"]);
	}

	public static function EditCalendarEventEntry($entryFields = [], $userFieldData = [], $accessCodes = [], $params = []): void
	{
		if (!$entryFields['SKIP_TIME'])
		{
			$entryFields['DATE_FROM'] .= ' '.$entryFields['TIME_FROM'];
			$entryFields['DATE_TO'] .= ' '.$entryFields['TIME_TO'];
		}

		// Timezone
		if (!$entryFields['TZ_FROM'] && isset($entryFields['DEFAULT_TZ']))
		{
			$entryFields['TZ_FROM'] = $entryFields['DEFAULT_TZ'];
		}
		if (!$entryFields['TZ_TO'] && isset($entryFields['DEFAULT_TZ']))
		{
			$entryFields['TZ_TO'] = $entryFields['DEFAULT_TZ'];
		}

		if (isset($entryFields['DEFAULT_TZ']) && (string)$entryFields['DEFAULT_TZ'] !== '')
		{
			CCalendar::SaveUserTimezoneName($params["userId"], $entryFields['DEFAULT_TZ']);
		}

		if ($entryFields['SECTION'])
		{
			$entryFields['SECTIONS'] = array($entryFields['SECTION']);
		}

		$entryFields["OWNER_ID"] = $params["userId"];
		$entryFields["CAL_TYPE"] = $params["type"];

		// Add author for new event
		if (!$entryFields["ID"])
		{
			$accessCodes[] = 'U'.$params["userId"];
		}

		$accessCodes = array_unique($accessCodes);
		$attendeeList = CCalendar::GetDestinationUsers($accessCodes);

		if (trim($entryFields["NAME"]) === '')
		{
			$entryFields["NAME"] = GetMessage('EC_DEFAULT_EVENT_NAME_V2');
		}

		$entryFields['IS_MEETING'] = (!empty($attendeeList) && $attendeeList != array($params["userId"]));

		if (
			isset($entryFields['RRULE'])
			&& !empty($entryFields['RRULE'])
			&& is_array($entryFields['RRULE']['BYDAY'])
		)
		{
			$entryFields['RRULE']['BYDAY'] = implode(',', $entryFields['RRULE']['BYDAY']);
		}

		if ($entryFields['IS_MEETING'])
		{
			$entryFields['ATTENDEES_CODES'] = $accessCodes;
			$entryFields['ATTENDEES'] = $attendeeList;
			$entryFields['MEETING_HOST'] = $params["userId"];
			$entryFields['MEETING'] = array(
				'HOST_NAME' => CCalendar::GetUserName($params["userId"]),
				'TEXT' => '',
				'OPEN' => false,
				'NOTIFY' => true,
				'REINVITE' => false
			);
		}
		else
		{
			$entryFields['ATTENDEES'] = false;
		}

		$eventId = CCalendar::SaveEvent(
			array(
				'arFields' => $entryFields,
				'autoDetectSection' => true
			)
		);

		if ($eventId > 0)
		{
			if (count($userFieldData) > 0)
			{
				CCalendarEvent::UpdateUserFields($eventId, $userFieldData);
			}

			foreach ($accessCodes as $key => $value)
			{
				if ($value === "UA")
				{
					unset($accessCodes[$key]);
					$accessCodes[] = "G2";
					break;
				}
			}

			if ($entryFields['IS_MEETING'] && !empty($userFieldData['UF_WEBDAV_CAL_EVENT']))
			{
				$UF = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields("CALENDAR_EVENT", $eventId, LANGUAGE_ID);
				CCalendar::UpdateUFRights($userFieldData['UF_WEBDAV_CAL_EVENT'], $accessCodes, $UF['UF_WEBDAV_CAL_EVENT']);
			}

			$socnetLogFields = Array(
				"ENTITY_TYPE" => SONET_SUBSCRIBE_ENTITY_USER,
				"ENTITY_ID" => $params["userId"],
				"USER_ID" => $params["userId"],
				"=LOG_DATE" => CDatabase::CurrentTimeFunction(),
				"TITLE_TEMPLATE" => "#TITLE#",
				"TITLE" => $entryFields["NAME"],
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

			$codes = [];
			foreach ($accessCodes as $value)
			{
				if (mb_strpos($value, 'SG') === 0)
				{
					$codes[] = $value . '_K';
				}
				$codes[] = $value;
			}
			$codes = array_unique($codes);



			if ($arRes = $dbRes->Fetch())
			{
				CSocNetLog::Update($arRes["ID"], $socnetLogFields);
				CSocNetLogRights::DeleteByLogID($arRes["ID"]);
				CSocNetLogRights::Add($arRes["ID"], $codes);
			}
			else
			{
				$socnetLogFields = array_merge($socnetLogFields, array(
					"EVENT_ID" => "calendar",
					"SITE_ID" => SITE_ID,
					"SOURCE_ID" => $eventId,
					"ENABLE_COMMENTS" => "Y",
					"CALLBACK_FUNC" => false
				));

				$logId = CSocNetLog::Add($socnetLogFields, false);
				CSocNetLogRights::Add($logId, $codes);
			}
		}
	}

	// Called after creation or edition of calendar event
	public static function OnEditCalendarEventEntry($params): void
	{
		$eventId = (int)$params['eventId'];

		$currentEvent = CCalendarEvent::GetList(
			[
				'arFilter' => [
					"PARENT_ID" => $eventId,
					"DELETED" => "N"
				],
				'parseRecursion' => false,
				'fetchAttendees' => true,
				'fetchMeetings' => true,
				'checkPermissions' => false,
				'setDefaultLimit' => false
			]);

		if ($currentEvent && count($currentEvent) > 0)
		{
			$currentEvent = $currentEvent[0];
		}
		$arFields = $params['arFields'];
		$attendeesCodes = $params['attendeesCodes'];

		if (isset($attendeesCodes) && !is_array($attendeesCodes))
		{
			$attendeesCodes = explode(',', $attendeesCodes);
		}
		if (empty($attendeesCodes) && $arFields['CREATED_BY'])
		{
			$attendeesCodes[] = 'U' . (int)$arFields['CREATED_BY'];
		}
		if (!is_array($attendeesCodes))
		{
			$attendeesCodes = [];
		}

		$folowersList = [];
		$unfolowersList = [];

		if ($currentEvent['IS_MEETING'] && is_array($currentEvent['ATTENDEE_LIST']))
		{
			foreach ($currentEvent['ATTENDEE_LIST'] as $attendee)
			{
				if ($attendee['status'] !== 'N')
				{
					$folowersList[] = (int)$attendee['id'];
				}
				else
				{
					$unfolowersList[] = $attendee['id'];
				}
			}
		}
		else
		{
			$folowersList[] = (int)$arFields['CREATED_BY'];
		}

		$newlogId = false;

		if ($eventId > 0)
		{
			$arSoFields = Array(
				"ENTITY_ID" => $arFields["CREATED_BY"],
				"USER_ID" => $arFields["CREATED_BY"],
				"=LOG_DATE" => CDatabase::CurrentTimeFunction(),
				"TITLE_TEMPLATE" => "#TITLE#",
				"TITLE" => $arFields["NAME"],
				"MESSAGE" => "",
				"TEXT_MESSAGE" => ""
			);

			$arAccessCodes = [];
			foreach ($attendeesCodes as $value)
			{
				$arAccessCodes[] = ($value === "UA") ? "G2" : $value;
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

			$arCodes = [];
			foreach ($arAccessCodes as $value)
			{
				if (mb_strpos($value, 'U') === 0)
				{
					$attendeeId = (int)mb_substr($value, 1);
					if (in_array($attendeeId, $folowersList, true))
					{
						$arCodes[] = $value;
					}
				}
				else
				{
					if (mb_strpos($value, 'SG') === 0)
					{
						$arCodes[] = $value . '_K';
					}
					$arCodes[] = $value;
				}
			}

			if (
				$arFields['IS_MEETING']
				&& $arFields['MEETING_HOST']
				&& !in_array('U' . $arFields['MEETING_HOST'], $arCodes, true)
			)
			{
				$arCodes[] = 'U'.$arFields['MEETING_HOST'];
			}
			$arCodes = array_unique($arCodes);

			if ($arRes = $dbRes->Fetch())
			{
				if (
					isset($arRes["ID"])
					&& (int)$arRes["ID"] > 0
				)
				{
					CSocNetLog::Update($arRes["ID"], $arSoFields);
					CSocNetLogRights::DeleteByLogID($arRes["ID"]);
					CSocNetLogRights::Add($arRes["ID"], $arCodes);

					foreach ($unfolowersList as $value)
					{
						CSocNetLogFollow::Set((int)$value, "L" . $arRes["ID"], 'N');
					}
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
					"CALLBACK_FUNC" => false,
					"PARAMS" => $arFields['RELATIONS'] ?? '',
				));

				$newlogId = CSocNetLog::Add($arSoFields, false);
				CSocNetLogRights::Add($newlogId, $arCodes);

				// Increment counter in live feed (mantis:#108212)
				CSocNetLog::counterIncrement(array(
					"ENTITY_ID" => $newlogId,
					"EVENT_ID" => 'calendar',
					"TYPE" => "L",
					"FOR_ALL_ACCESS" => false,
					"SEND_TO_AUTHOR" => "N"
				));

				if (!empty($arFields['RELATIONS']) && Loader::includeModule('forum'))
				{
					$commentsXmlId = CCalendarEvent::GetEventCommentXmlId($arFields);
					$calendarSettings = CCalendar::GetSettings();
					$forumID = $calendarSettings['forum_id'] ?? null;

					CForumTopic::Add([
						'TITLE' => $commentsXmlId,
						'TAGS' => '',
						'MESSAGE' => $commentsXmlId,
						'AUTHOR_ID' => 0,
						'AUTHOR_NAME' => 'SYSTEM',
						'FORUM_ID' => $forumID,
						'USER_START_ID' => 0,
						'USER_START_NAME' => 'SYSTEM',
						'LAST_POSTER_NAME' => 'SYSTEM',
						'XML_ID' => $commentsXmlId,
						'APPROVED' => 'Y',
					]);
				}

				foreach ($unfolowersList as $value)
				{
					CSocNetLogFollow::Set((int)$value, "L" . $newlogId, 'N');
				}
			}

			// Find if we already have socialnetwork livefeed entry for this event
			if ($newlogId && ($arFields['RECURRENCE_ID'] ?? null) > 0)
			{
				$commentXmlId = false;
				if (!empty($arFields['RELATIONS']))
				{
					if (!isset($arFields['~RELATIONS']) || !is_array($arFields['~RELATIONS']))
					{
						$arFields['~RELATIONS'] = unserialize($arFields['RELATIONS'], ['allowed_classes' => false]);
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

				$rrule = CCalendarEvent::ParseRRULE($event['RRULE'] ?? null);
				$until = $rrule['~UNTIL'] ?? null;

				while ($arRes = $dbRes->Fetch())
				{
					if (isset($arRes['PARAMS']) && is_string($arRes['PARAMS']))
					{
						$arRes['PARAMS'] = unserialize($arRes['PARAMS'], ['allowed_classes' => false]);
						if (!is_array($arRes['PARAMS']))
						{
							$arRes['PARAMS'] = [];
						}
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
								"COMMENTS_COUNT" => (int)($arRes['COMMENTS_COUNT'] ?? 0),
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
	public static function OnDeleteCalendarEventEntry($eventId): void
	{
		if (Loader::includeModule("socialnetwork"))
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
		if (
			($arData['ENTITY_TYPE_ID'] ?? null) === 'FORUM_POST'
			&& (int)($arData['PARAM1'] ?? null) > 0
			&& in_array($arData["MODULE_ID"], array("forum", "FORUM"))
			&& preg_match('/^EVENT_(\d+)/', $arData["TITLE"], $match)
		)
		{
			$arCalendarSettings = CCalendar::GetSettings();
			$forumID = (int)$arCalendarSettings["forum_id"];
			$eventID = (int)$match[1];

			if (
				(int)$arData['PARAM1'] === $forumID
				&& $eventID > 0
				&& ($arCalendarEvent = CCalendarEvent::GetById($eventID))
				&& (string)$arCalendarEvent["CAL_TYPE"] !== ''
				&& !empty($arCalendarSettings["pathes"])
				&& (int)$arCalendarEvent["OWNER_ID"] > 0
				&& in_array($arCalendarEvent["CAL_TYPE"], array("user", "group"))
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
						if ($arCalendarEvent["CAL_TYPE"] === "user")
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
						elseif (
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

					$arData['LID'][$siteId] = ($messageUrl ? $messageUrl."?EVENT_ID=".$arCalendarEvent["ID"]."&MID=".$arData['ENTITY_ID']."#message".$arData['ENTITY_ID'] : "");
				}

				return $arData;
			}

			$arData['TITLE'] = '';
			$arData['BODY'] = '';

			return $arData;
		}
	}

	public static function OnChangeMeetingStatusEventEntry($params): void
	{
		$codesList = [];
		$unfolowersList = [];

		if (isset($params['event']))
		{
			if ($params['event']['IS_MEETING'])
			{
				if (
					isset($params['event']['MEETING_HOST'])
					&& (int)$params['event']['MEETING_HOST'] > 0
				)
				{
					$codesList[] = 'U' . (int)$params['event']['MEETING_HOST'];
				}

				if (isset($params['event']['ATTENDEE_LIST']) && is_array($params['event']['ATTENDEE_LIST']))
				{
					foreach ($params['event']['ATTENDEE_LIST'] as $attendee)
					{
						if (
							(
								(int)$attendee['id'] === (int)$params['userId']
								&& $params['status'] === 'N'
							)
							|| (
								(int)$attendee['id'] !== (int)$params['userId']
								&& $attendee['status'] === 'N'
							)
						)
						{
							$unfolowersList[] = (int)$attendee['id'];
						}
					}
				}
			}

			if (isset($params['event']['ATTENDEES_CODES']) && is_array($params['event']['ATTENDEES_CODES']))
			{
				foreach ($params['event']['ATTENDEES_CODES'] as $code)
				{
					if (mb_strpos($code, 'U') === 0)
					{
						$attendeeId = (int)mb_substr($code, 1);
						if (!in_array($attendeeId, $unfolowersList, true))
						{
							$codesList[] = $code;
						}
					}
					else
					{
						if (mb_strpos($code, 'SG') === 0)
						{
							$codesList[] = $code . '_K';
						}
						$codesList[] = $code;
					}
				}
			}
		}

		if (
			($params['status'] === 'N' || $params['status'] === 'Y')
			&& (int)$params['userId']
		)
		{
			$dbRes = CSocNetLog::GetList(array("ID" => "DESC"), array("EVENT_ID" => "calendar", "SOURCE_ID" => $params['eventId']), false, false, array("ID"));

			while ($logEntry = $dbRes->Fetch())
			{
				CSocNetLogRights::DeleteByLogID($logEntry['ID']);
				foreach ($unfolowersList as $value)
				{
					CSocNetLogFollow::Set((int)$value, "L" . $logEntry['ID'], 'N');
				}
				CSocNetLogFollow::Set((int)$params['userId'], "L" . $logEntry['ID'], $params['status']);

				if (
					$params['status'] === 'Y'
					&& method_exists(ComponentHelper::class, 'userLogSubscribe')
				)
				{
					ComponentHelper::userLogSubscribe(array(
						'logId' => $logEntry['ID'],
						'userId' => (int)$params['userId'],
						'typeList' => [
							'COUNTER_COMMENT_PUSH',
						]
					));
				}

				if (!empty($codesList))
				{
					$codesList = array_unique($codesList);
					CSocNetLogRights::Add($logEntry['ID'], $codesList);
				}
			}
		}
	}
}
