<?
/** var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);

class CCalendarNotify
{
	const PUSH_MESSAGE_MAX_LENGTH = 255;

	public static function Send($params)
	{
		if (!\Bitrix\Main\Loader::includeModule("im"))
			return false;

		$params['rrule'] = CCalendarEvent::GetRRULEDescription($params['fields'], false, false);

		if ($params['rrule'])
		{
			$params['from_to_html'] = CCalendar::GetFromToHtml(
				CCalendar::Timestamp($params['fields']['DATE_FROM']),
				CCalendar::Timestamp($params['fields']['DATE_TO']),
				$params['fields']['DT_SKIP_TIME'] == 'Y',
				$params['fields']['DT_LENGTH'],
				true
			);
		}

		$params["eventId"] = intVal($params["eventId"]);
		$mode = $params['mode'];
		$fromUser = intVal($params["userId"]);
		$toUser = intVal($params["guestId"]);
		if (!$fromUser || !$toUser || ($toUser == $fromUser && $mode !== 'status_accept' && $mode !== 'status_decline'))
			return false;

		if ($params['fields']['DT_SKIP_TIME'] == 'Y')
		{
			$params["from"] = CCalendar::Date(CCalendar::Timestamp($params["from"]), false);
		}
		else
		{
			$params["from"] = CCalendar::Date(CCalendar::Timestamp($params["from"]), true, true, true);
		}

		$notifyFields = array(
			'EMAIL_TEMPLATE' => "CALENDAR_INVITATION",
			'NOTIFY_MODULE' => "calendar",
		);

		if ($mode == 'accept' || $mode == 'decline')
		{
			$notifyFields['FROM_USER_ID'] = $toUser;
			$notifyFields['TO_USER_ID'] = $fromUser;
		}
		else
		{
			$notifyFields['FROM_USER_ID'] = $fromUser;
			$notifyFields['TO_USER_ID'] = $toUser;
		}

		$rs = CUser::GetList(($by="id"), ($order="asc"), array("ID_EQUAL_EXACT"=>$toUser, "ACTIVE" => "Y"));
		if (!$rs->Fetch())
			return false;

		$eventId = intVal($params["eventId"]);
		$params["pathToCalendar"] = CCalendar::GetPathForCalendarEx($notifyFields['TO_USER_ID']);
		if ($params["pathToCalendar"] && $eventId)
		{
			$params["pathToCalendar"] = CHTTP::urlDeleteParams($params["pathToCalendar"], array("action", "sessid", "bx_event_calendar_request", "EVENT_ID"));
			$params["pathToEvent"] = CHTTP::urlAddParams($params["pathToCalendar"], array('EVENT_ID' => $eventId));
		}

		$notifyFields = array(
			'FROM_USER_ID' => $fromUser,
			'TO_USER_ID' => $toUser,
			'EMAIL_TEMPLATE' => "CALENDAR_INVITATION",
			'NOTIFY_MODULE' => "calendar",
		);

		switch($mode)
		{
			case 'invite':
				$notifyFields = self::Invite($notifyFields, $params);
				break;
			case 'change':
				$notifyFields = self::Change($notifyFields, $params);
				break;
			case 'change_notify':
				$notifyFields = self::ChangeNotify($notifyFields, $params);
				break;
			case 'cancel':
				$notifyFields = self::Cancel($notifyFields, $params);
				break;
			case 'cancel_this':
				$notifyFields = self::CancelInstance($notifyFields, $params);
				break;
			case 'cancel_all':
				$notifyFields = self::CancelAllReccurent($notifyFields, $params);
				break;
			case 'accept':
			case 'decline':
				$notifyFields = self::MeetingStatus($notifyFields, $params);
				break;
			case 'status_accept':
			case 'status_decline':
				$notifyFields = self::MeetingStatusInfo($notifyFields, $params);
				break;
		}

		$messageId = CIMNotify::Add($notifyFields);
		if ($params['markRead'] && $messageId > 0)
		{
			$CIMNotify = new CIMNotify(intVal($params["userId"]));
			$CIMNotify->MarkNotifyRead($messageId);
		}

		foreach(GetModuleEvents("calendar", "OnSendInvitationMessage", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($params));
	}


	public static function Invite($fields = array(), $params)
	{
		$fields['NOTIFY_EVENT'] = "invite";
		$fields['NOTIFY_TYPE'] = IM_NOTIFY_CONFIRM;
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID'];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		if ($params['rrule'])
		{
			$fields['MESSAGE'] = GetMessage('EC_MESS_REC_INVITE_SITE',
				array(
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => $params['from_to_html'],
					'#RRULE#' => $params["rrule"]
				)
			);
			$fields['MESSAGE_OUT'] = GetMessage('EC_MESS_REC_INVITE', array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params['from_to_html'],
				'#RRULE#' => $params["rrule"]
			));
		}
		else
		{
			$fields['MESSAGE'] = GetMessage('EC_MESS_INVITE_SITE',
				array(
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => $params["from"])
			);

			$fields['MESSAGE_OUT'] = GetMessage('EC_MESS_INVITE', array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			));
		}

		if ($params['location'] != "")
		{
			$fields['MESSAGE'] .= "\n\n".GetMessage('EC_LOCATION').': '.$params['location'];
			$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_LOCATION').': '.$params['location'];
		}

		if ($params["meetingText"] != "")
		{
			$fields['MESSAGE'] .= "\n\n".GetMessage('EC_MESS_MEETING_TEXT', array(
					'#MEETING_TEXT#' => $params["meetingText"]
				));
			$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_MEETING_TEXT', array(
					'#MEETING_TEXT#' => $params["meetingText"]
				));
		}
		$fields['PUSH_MESSAGE'] = str_replace(
				array('[B]', '[/B]'),
				array('', ''),
				$fields['MESSAGE']
			);

		$fields['MESSAGE'] .= "\n\n".GetMessage('EC_MESS_INVITE_DETAILS_SITE', array('#LINK#' => $params["pathToEvent"]));

		$fields['NOTIFY_BUTTONS'] = Array(
			Array('TITLE' => GetMessage('EC_MESS_INVITE_CONF_Y_SITE'), 'VALUE' => 'Y', 'TYPE' => 'accept'),
			Array('TITLE' => GetMessage('EC_MESS_INVITE_CONF_N_SITE'), 'VALUE' => 'N', 'TYPE' => 'cancel')
		);

		$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_INVITE_CONF_Y', array('#LINK#' => $params["pathToEvent"].'&CONFIRM=Y'));
		$fields['MESSAGE_OUT'] .= "\n".GetMessage('EC_MESS_INVITE_CONF_N', array('#LINK#' => $params["pathToEvent"].'&CONFIRM=N'));
		$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_INVITE_DETAILS', array('#LINK#' => $params["pathToEvent"]));

		$fields['TITLE'] = GetMessage('EC_MESS_INVITE_TITLE',
			array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"]
			)
		);

		return $fields;
	}

	public static function Change($fields = array(), $params)
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TYPE'] = IM_NOTIFY_CONFIRM;
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID'];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = GetMessage('EC_MESS_INVITE_CHANGED_SITE',
			array(
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);

		$fields['MESSAGE_OUT'] = GetMessage('EC_MESS_INVITE_CHANGED',
			array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);

		if ($params["meetingText"] != "")
		{
			$fields['MESSAGE'] .= "\n\n".GetMessage('EC_MESS_MEETING_TEXT', array(
					'#MEETING_TEXT#' => $params["meetingText"]
				));
			$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_MEETING_TEXT', array(
					'#MEETING_TEXT#' => $params["meetingText"]
				));
		}

		$fields['MESSAGE'] .= "\n\n".GetMessage('EC_MESS_INVITE_DETAILS_SITE', array('#LINK#' => $params["pathToEvent"]));
		$fields['NOTIFY_BUTTONS'] = Array(
			Array('TITLE' => GetMessage('EC_MESS_INVITE_CONF_Y_SITE'), 'VALUE' => 'Y', 'TYPE' => 'accept'),
			Array('TITLE' => GetMessage('EC_MESS_INVITE_CONF_N_SITE'), 'VALUE' => 'N', 'TYPE' => 'cancel')
		);

		$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_INVITE_CONF_Y', array('#LINK#' => $params["pathToEvent"].'&CONFIRM=Y'));
		$fields['MESSAGE_OUT'] .= "\n".GetMessage('EC_MESS_INVITE_CONF_N', array('#LINK#' => $params["pathToEvent"].'&CONFIRM=N'));
		$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_INVITE_DETAILS', array('#LINK#' => $params["pathToEvent"]));

		$fields['TITLE'] = GetMessage('EC_MESS_INVITE_CHANGED_TITLE',array('#TITLE#' => $params["name"]));

		return $fields;
	}
	public static function ChangeNotify($fields = array(), $params)
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID'];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = GetMessage('EC_MESS_INVITE_CHANGED_SITE',
			array(
				'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
				'#ACTIVE_FROM#' => $params["from"]
			)
		);

		$fields['MESSAGE_OUT'] = GetMessage('EC_MESS_INVITE_CHANGED',
			array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);

		if ($params["meetingText"] != "")
		{
			$fields['MESSAGE'] .= "\n\n".GetMessage('EC_MESS_MEETING_TEXT', array(
					'#MEETING_TEXT#' => $params["meetingText"]
				));
			$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_MEETING_TEXT', array(
					'#MEETING_TEXT#' => $params["meetingText"]
				));
		}

		$fields['MESSAGE'] .= "\n\n".GetMessage('EC_MESS_INVITE_DETAILS_SITE', array('#LINK#' => $params["pathToEvent"]));
		$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_INVITE_DETAILS', array('#LINK#' => $params["pathToEvent"]));

		$fields['TITLE'] = GetMessage('EC_MESS_INVITE_CHANGED_TITLE',array('#TITLE#' => $params["name"]));
		return $fields;
	}
	public static function Cancel($fields = array(), $params)
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID']."|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = GetMessage('EC_MESS_INVITE_CANCEL_SITE', array(
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);
		$fields['MESSAGE_OUT'] = GetMessage('EC_MESS_INVITE_CANCEL', array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);
		$fields['MESSAGE'] .= "\n\n".GetMessage('EC_MESS_VIEW_OWN_CALENDAR', array('#LINK#' => $params["pathToCalendar"]));
		$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_VIEW_OWN_CALENDAR_OUT', array('#LINK#' => $params["pathToCalendar"]));
		$fields['TITLE'] = GetMessage('EC_MESS_INVITE_CANCEL_TITLE', array('#TITLE#' => $params["name"]));
		return $fields;
	}

	public static function CancelInstance($fields = array(), $params)
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$params["from"]."|".$fields['TO_USER_ID']."|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = GetMessage('EC_MESS_REC_THIS_CANCEL_SITE', array(
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);
		$fields['MESSAGE_OUT'] = GetMessage('EC_MESS_REC_THIS_CANCEL', array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);
		$fields['MESSAGE'] .= "\n\n".GetMessage('EC_MESS_VIEW_OWN_CALENDAR', array('#LINK#' => $params["pathToCalendar"]));
		$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_VIEW_OWN_CALENDAR_OUT', array('#LINK#' => $params["pathToCalendar"]));
		$fields['TITLE'] = GetMessage('EC_MESS_INVITE_CANCEL_TITLE', array('#TITLE#' => $params["name"]));
		return $fields;
	}

	public static function CancelAllReccurent($fields = array(), $params)
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID']."|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = GetMessage('EC_MESS_REC_ALL_CANCEL_SITE', array(
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);
		$fields['MESSAGE_OUT'] = GetMessage('EC_MESS_REC_ALL_CANCEL', array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);
		$fields['MESSAGE'] .= "\n\n".GetMessage('EC_MESS_VIEW_OWN_CALENDAR', array('#LINK#' => $params["pathToCalendar"]));
		$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_VIEW_OWN_CALENDAR_OUT', array('#LINK#' => $params["pathToCalendar"]));
		$fields['TITLE'] = GetMessage('EC_MESS_INVITE_CANCEL_TITLE', array('#TITLE#' => $params["name"]));

		return $fields;
	}

	public static function MeetingStatus($fields = array(), $params)
	{
		$fields['NOTIFY_EVENT'] = "info";
		$fields['FROM_USER_ID'] = intVal($params["guestId"]);
		$fields['TO_USER_ID'] = intVal($params["userId"]);
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$params['mode'];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = GetMessage($params['mode'] == 'accept' ? 'EC_MESS_INVITE_ACCEPTED_SITE' : 'EC_MESS_INVITE_DECLINED_SITE',
			array(
				'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
				'#ACTIVE_FROM#' => $params["from"]
			)
		);

		$fields['MESSAGE_OUT'] = GetMessage($params['mode']=='accept' ? 'EC_MESS_INVITE_ACCEPTED' : 'EC_MESS_INVITE_DECLINED',
			array(
				'#GUEST_NAME#' => CCalendar::GetUserName($params['guestId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);

		if ($params["comment"] != "")
		{
			$fields['MESSAGE'] .= "\n\n".GetMessage('EC_MESS_INVITE_ACC_COMMENT', array(
					'#COMMENT#' => $params["comment"]
				));
			$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_INVITE_ACC_COMMENT', array(
					'#COMMENT#' => $params["comment"]
				));
		}
		$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_INVITE_DETAILS', array('#LINK#' => $params["pathToEvent"]));

		return $fields;
	}
	public static function MeetingStatusInfo($fields = array(), $params)
	{
		$fields['NOTIFY_EVENT'] = "info";
		$fields['FROM_USER_ID'] = intVal($params["guestId"]);
		$fields['TO_USER_ID'] = intVal($params["userId"]);
		$fields['NOTIFY_TAG'] = "CALENDAR|STATUS|".$params['eventId']."|".intVal($params["userId"]);
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|STATUS|".$params['eventId'];

		$fields['MESSAGE'] = GetMessage($params['mode'] =='status_accept' ? 'EC_MESS_STATUS_NOTIFY_Y_SITE' : 'EC_MESS_STATUS_NOTIFY_N_SITE',
			array(
				'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
				'#ACTIVE_FROM#' => $params["from"]
			)
		);

		$fields['MESSAGE_OUT'] = GetMessage($params['mode'] == 'status_accept' ? 'EC_MESS_STATUS_NOTIFY_Y' : 'EC_MESS_STATUS_NOTIFY_N',
			array(
				'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
				'#ACTIVE_FROM#' => $params["from"]
			)
		);

		$fields['MESSAGE_OUT'] .= "\n\n".GetMessage('EC_MESS_INVITE_DETAILS', array('#LINK#' => $params["pathToEvent"]));

		return $fields;
	}

	public static function NotifyComment($eventId, $params)
	{
		if (!\Bitrix\Main\Loader::includeModule("im") || intval($eventId) <= 0)
		{
			return;
		}

		$userId = intval($params["USER_ID"]);
		if ($event = CCalendarEvent::GetById($eventId))
		{
			$instanceDate = false;

			if (
				!isset($params['LOG'])
				&& \Bitrix\Main\Loader::includeModule('socialnetwork')
			)
			{
				$dbResult = CSocNetLog::GetList(
					array(),
					array("ID" => $params["LOG_ID"]),
					false,
					false,
					array("ID", "SOURCE_ID", "PARAMS")
				);
				$arLog = $dbResult->Fetch();
			}
			else
			{
				$arLog = $params['LOG'];
			}

			if ($arLog)
			{
				if ($arLog['PARAMS'] != "")
				{
					$arLog['PARAMS'] = unserialize($arLog['PARAMS']);
					if (!is_array($arLog['PARAMS']))
						$arLog['PARAMS'] = array();
				}

				if (isset($arLog['PARAMS']['COMMENT_XML_ID']) && $arLog['PARAMS']['COMMENT_XML_ID'])
				{
					$instanceDate = CCalendarEvent::ExtractDateFromCommentXmlId($arLog['PARAMS']['COMMENT_XML_ID']);
				}
			}

			$rsUser = CUser::GetList(
				$by = 'id',
				$order = 'asc',
				array('ID_EQUAL_EXACT' => $userId),
				array('FIELDS' => array('PERSONAL_GENDER'))
			);

			$strMsgAddComment = GetMessage('EC_LF_COMMENT_MESSAGE_ADD');
			$strMsgAddComment_Q = GetMessage('EC_LF_COMMENT_MESSAGE_ADD_Q');
			if ($arUser = $rsUser->fetch())
			{
				switch ($arUser['PERSONAL_GENDER'])
				{
					case "F":
					case "M":
						$strMsgAddComment = GetMessage('EC_LF_COMMENT_MESSAGE_ADD_'.$arUser['PERSONAL_GENDER']);
						$strMsgAddComment_Q = GetMessage('EC_LF_COMMENT_MESSAGE_ADD_Q_'.$arUser['PERSONAL_GENDER']);
						break;
					default:
						break;
				}
			}

			$imMessageFields = array(
				"FROM_USER_ID" => $userId,
				"NOTIFY_TYPE" => IM_NOTIFY_FROM,
				"NOTIFY_MODULE" => "calendar",
				"NOTIFY_EVENT" => "event_comment"
			);

			$aId = isset($event['PARENT_ID']) ? $event['PARENT_ID'] : $event['ID'];
			$attendees = CCalendarEvent::GetAttendees($aId);
			if (is_array($attendees) && is_array($attendees[$aId]))
			{
				if (!$instanceDate)
				{
					$instanceDate = CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false);
				}

				$attendees = $attendees[$aId];

				$excludeUserIdList = array();

				if (
					$arLog
					&& \Bitrix\Main\Loader::includeModule('socialnetwork')
				)
				{
					$res = \Bitrix\Socialnetwork\LogFollowTable::getList(array(
						'filter' => array(
							"=CODE" => "L".$arLog['ID'],
							"=TYPE" => "N"
						),
						'select' => array('USER_ID')
					));

					while ($unFollower = $res->fetch())
					{
						$excludeUserIdList[] = $unFollower["USER_ID"];
					}
				}

				foreach($attendees as $attendee)
				{
					if (in_array($attendee["USER_ID"], $excludeUserIdList))
					{
						continue;
					}

					$url = CCalendar::GetPathForCalendarEx($attendee["USER_ID"]);
					$url = $url.((strpos($url, "?") === false) ? '?' : '&').'EVENT_ID='.$eventId.'&EVENT_DATE='.$instanceDate;
					if ($attendee["USER_ID"] != $userId && $attendee["STATUS"] != 'N')
					{
						$imMessageFields = array_merge($imMessageFields, array("TO_USER_ID" => $attendee["USER_ID"]));

						if ($attendee["STATUS"] == 'Q')
						{
							$imMessageFields["NOTIFY_MESSAGE"] = str_replace(
								array("#EVENT_TITLE#"),
								array(strlen($url) > 0 ? "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".$event["NAME"]."</a>" : $event["NAME"]),
								$strMsgAddComment_Q
							);
							$imMessageFields["NOTIFY_MESSAGE_OUT"] = str_replace(
									array("#EVENT_TITLE#"),
									array($event["NAME"]),
									$strMsgAddComment_Q
								).(strlen($url) > 0 ? " (".$url.")" : "")."#BR##BR#".$params["MESSAGE"];
						}
						else
						{
							$imMessageFields["NOTIFY_MESSAGE"] = str_replace(
								array("#EVENT_TITLE#"),
								array(strlen($url) > 0 ? "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".$event["NAME"]."</a>" : $event["NAME"]),
								$strMsgAddComment
							);
							$imMessageFields["NOTIFY_MESSAGE_OUT"] = str_replace(
									array("#EVENT_TITLE#"),
									array($event["NAME"]),
									$strMsgAddComment
								).(strlen($url) > 0 ? " (".$url.")" : "")."#BR##BR#".$params["MESSAGE"];
						}

						$imMessageFields["NOTIFY_TAG"] = "CALENDAR|COMMENT|".$aId."|".$instanceDate;
						CIMNotify::Add($imMessageFields);
					}
				}
			}
		}
	}

	public static function ClearNotifications($eventId = false, $userId = false)
	{
		if (\Bitrix\Main\Loader::includeModule("im"))
		{
			if ($eventId && $eventId)
			{
				CIMNotify::DeleteByTag("CALENDAR|INVITE|".$eventId."|".$userId);
				CIMNotify::DeleteByTag("CALENDAR|STATUS|".$eventId."|".$userId);
			}
			elseif($eventId)
			{
				CIMNotify::DeleteBySubTag("CALENDAR|INVITE|".$eventId);
				CIMNotify::DeleteBySubTag("CALENDAR|STATUS|".$eventId);
			}
		}
	}
}
?>