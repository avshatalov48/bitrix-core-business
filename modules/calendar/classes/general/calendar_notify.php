<?
/** var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);

use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;

class CCalendarNotify
{
	const PUSH_MESSAGE_MAX_LENGTH = 255;

	public static function Send($params)
	{
		if (!Loader::includeModule("im"))
			return false;

		$params['rrule'] = CCalendarEvent::GetRRULEDescription($params['fields'], false, false);
		$params["eventId"] = intval($params["eventId"]);
		$mode = $params['mode'];
		$fromUser = intval($params["userId"]);
		$toUser = intval($params["guestId"]);
		if (!$fromUser || !$toUser || ($toUser == $fromUser && !in_array($mode, ['status_accept', 'status_decline', 'fail_ical_invite'])))
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

		$userOrm = \Bitrix\Main\UserTable::getList([
			'filter' => ['=ID' => $toUser, 'ACTIVE' => 'Y'],
			'select' => ['ID']
		]);
		if (!$userOrm->fetch())
		{
			return false;
		}

		$eventId = intval($params["eventId"]);
		$params["pathToCalendar"] = CCalendar::GetPathForCalendarEx($notifyFields['TO_USER_ID']);
		if ($params["pathToCalendar"] && $eventId)
		{
			$params["pathToCalendar"] = CHTTP::urlDeleteParams($params["pathToCalendar"], ["action", "sessid", "bx_event_calendar_request", "EVENT_ID"]);
			$params["pathToEvent"] = CHTTP::urlAddParams($params["pathToCalendar"], ['EVENT_ID' => $eventId]);
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
			case 'fail_ical_invite':
				$notifyFields = self::NotifyFailIcalInvite($notifyFields, $params);
				break;
		}

		$messageId = CIMNotify::Add($notifyFields);
		if ($params['markRead'] && $messageId > 0)
		{
			$CIMNotify = new CIMNotify(intval($params["userId"]));
			$CIMNotify->MarkNotifyRead($messageId);
		}

		foreach(GetModuleEvents("calendar", "OnSendInvitationMessage", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$params]);
		}
	}


	public static function Invite($fields = [], $params)
	{
		$fields['NOTIFY_EVENT'] = "invite";
		$fields['NOTIFY_TYPE'] = IM_NOTIFY_CONFIRM;
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID'];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		if ($params['rrule'])
		{
			$fromTs = CCalendar::Timestamp($params['fields']['DATE_FROM']);
			$toTs = CCalendar::Timestamp($params['fields']['DATE_TO']);

			if ($params['fields']['DT_SKIP_TIME'] == "Y")
			{
				$toTs += CCalendar::DAY_LENGTH;
			}
			else
			{
				$fromTs = $fromTs - CCalendar::GetTimezoneOffset($params['fields']['TZ_FROM']) + CCalendar::GetCurrentOffsetUTC($fields['TO_USER_ID']);
				$toTs = $toTs - CCalendar::GetTimezoneOffset($params['fields']['TZ_TO']) + CCalendar::GetCurrentOffsetUTC($fields['TO_USER_ID']);
			}

			$params['from_to_html'] = CCalendar::GetFromToHtml(
				$fromTs,
				$toTs,
				$params['fields']['DT_SKIP_TIME'] == 'Y',
				$params['fields']['DT_LENGTH'],
				true
			);

			$fields['MESSAGE'] = Loc::getMessage('EC_MESS_REC_INVITE_SITE',
				array(
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => $params['from_to_html'],
					'#RRULE#' => $params["rrule"]
				)
			);
			$fields['MESSAGE_OUT'] = Loc::getMessage('EC_MESS_REC_INVITE', array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params['from_to_html'],
				'#RRULE#' => $params["rrule"]
			));
		}
		else
		{
			$fields['MESSAGE'] = Loc::getMessage('EC_MESS_INVITE_SITE',
				array(
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => $params["from"]
				)
			);

			$fields['MESSAGE_OUT'] = Loc::getMessage('EC_MESS_INVITE', array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			));
		}

		if ($params['location'] != "")
		{
			$fields['MESSAGE'] .= "\n\n".Loc::getMessage('EC_LOCATION').': '.$params['location'];
			$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_LOCATION').': '.$params['location'];
		}

		$fields['PUSH_MESSAGE'] = str_replace(
				['[B]', '[/B]'],
				['', ''],
				$fields['MESSAGE']
			);

		$fields['MESSAGE'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_DETAILS_SITE', ['#LINK#' => $params["pathToEvent"]]);

		$fields['NOTIFY_BUTTONS'] = Array(
			['TITLE' => Loc::getMessage('EC_MESS_INVITE_CONF_Y_SITE'), 'VALUE' => 'Y', 'TYPE' => 'accept'],
			['TITLE' => Loc::getMessage('EC_MESS_INVITE_CONF_N_SITE'), 'VALUE' => 'N', 'TYPE' => 'cancel']
		);

		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_CONF_Y', ['#LINK#' => $params["pathToEvent"].'&CONFIRM=Y']);
		$fields['MESSAGE_OUT'] .= "\n".Loc::getMessage('EC_MESS_INVITE_CONF_N', ['#LINK#' => $params["pathToEvent"].'&CONFIRM=N']);
		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_DETAILS', ['#LINK#' => $params["pathToEvent"]]);

		$fields['TITLE'] = Loc::getMessage('EC_MESS_INVITE_TITLE',
			array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"]
			)
		);

		return $fields;
	}

	public static function ChangeNotify($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID'];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		// Was changed only one field in this case we could be more specific
		if (count($params['entryChanges']) == 1)
		{
			$change = $params['entryChanges'][0];
			switch($change['fieldKey'])
			{
				case 'NAME':
					$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_TITLE_CHANGED',
						array(
							'#OLD_TITLE#' => $change['oldValue'],
							'#NEW_TITLE#' => "[url=".$params["pathToEvent"]."]".$change['newValue']."[/url]",
							'#ACTIVE_FROM#' => $params["from"]
						)
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_TITLE_CHANGED',
						array(
							'#OLD_TITLE#' => $change['oldValue'],
							'#NEW_TITLE#' => $change['newValue'],
							'#ACTIVE_FROM#' => $params["from"]
						)
					);
					break;

				case 'DATE_FROM':
					if ($params['fields']['DT_SKIP_TIME'] === 'N')
					{
						$userOffset = \CCalendar::GetTimezoneOffset($params['fields']['TZ_FROM'])
										 - \CCalendar::GetCurrentOffsetUTC($params['guestId']);

						$change['oldValue'] = \CCalendar::Date(\CCalendar::Timestamp($change['oldValue'])
												   - $userOffset, true, true, true);
						$change['newValue'] = \CCalendar::Date(\CCalendar::Timestamp($change['newValue'])
												   - $userOffset, true, true, true);
					}

					$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_DATE_FROM_CHANGED',
						array(
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
							'#OLD_DATE_FROM#' => $change['oldValue'],
							'#NEW_DATE_FROM#' => $change['newValue']
						)
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_DATE_FROM_CHANGED',
						array(
							'#TITLE#' => $params["name"],
							'#OLD_DATE_FROM#' => $change['oldValue'],
							'#NEW_DATE_FROM#' => $change['newValue']
						)
					);
					break;

				case 'DATE_TO':
					if ($params['fields']['DT_SKIP_TIME'] === 'N')
					{
						$userOffset = \CCalendar::GetTimezoneOffset($params['fields']['TZ_TO'])
									  - \CCalendar::GetCurrentOffsetUTC($params['guestId']);

						$change['oldValue'] = \CCalendar::Date(\CCalendar::Timestamp($change['oldValue'])
															   - $userOffset, true, true, true);
						$change['newValue'] = \CCalendar::Date(\CCalendar::Timestamp($change['newValue'])
															   - $userOffset, true, true, true);
					}

					$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_DATE_TO_CHANGED',
						array(
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
							'#OLD_DATE_TO#' => $change['oldValue'],
							'#NEW_DATE_TO#' => $change['newValue']
						)
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_DATE_TO_CHANGED',
						array(
							'#TITLE#' => $params["name"],
							'#OLD_DATE_TO#' => $change['oldValue'],
							'#NEW_DATE_TO#' => $change['newValue']
						)
					);
					break;
				case 'LOCATION':
					$locationMessageCode = empty($change['newValue']) ? 'EC_NOTIFY_LOCATION_CHANGED_NONE' : 'EC_NOTIFY_LOCATION_CHANGED';
					$fields['MESSAGE'] = Loc::getMessage($locationMessageCode,
						array(
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
							'#ACTIVE_FROM#' => $params["from"],
							'#NEW_VALUE#' => $change['newValue']
						)
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage($locationMessageCode,
						array(
							'#TITLE#' => $params["name"],
							'#ACTIVE_FROM#' => $params["from"],
							'#NEW_VALUE#' => $change['newValue']
						)
					);
					break;
				case 'ATTENDEES':
					$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_ATTENDEES_CHANGED',
						array(
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
							'#ACTIVE_FROM#' => $params["from"]
						)
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_ATTENDEES_CHANGED',
						array(
							'#TITLE#' => $params["name"],
							'#ACTIVE_FROM#' => $params["from"]
						)
					);
					break;
				case 'DESCRIPTION':
					$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_DESCRIPTION_CHANGED',
						array(
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
							'#ACTIVE_FROM#' => $params["from"]
						)
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_DESCRIPTION_CHANGED',
						array(
							'#TITLE#' => $params["name"],
							'#ACTIVE_FROM#' => $params["from"]
						)
					);
					break;
				case 'RRULE':
				case 'EXDATE':
					$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_RRULE_CHANGED',
						array(
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]"
						)
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_RRULE_CHANGED',
						array(
							'#TITLE#' => $params["name"]
						)
					);
					break;
				case 'IMPORTANCE':
					$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_IMPORTANCE_CHANGED',
						array(
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
							'#ACTIVE_FROM#' => $params["from"]
						)
					);
					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_IMPORTANCE_CHANGED',
						array(
							'#TITLE#' => $params["name"],
							'#ACTIVE_FROM#' => $params["from"]
						)
					);
					break;
			}
		}
		else // Two or more changes
		{
			$changedFieldsList = [];
			foreach ($params['entryChanges'] as $change)
			{
				$key = $change['fieldKey'];
				$changedFieldsList[] = Loc::getMessage('EC_NOTIFY_FIELD_'.$key);
			}
			$changedFieldsListMessage = implode(', ', array_unique($changedFieldsList));

			$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_ENTRY_CHANGED',
				[
					'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
					'#ACTIVE_FROM#' => $params["from"],
					'#CHANGED_FIELDS_LIST#' => $changedFieldsListMessage
				]
			);

			$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_ENTRY_CHANGED',
				[
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => $params["from"],
					'#CHANGED_FIELDS_LIST#' => $changedFieldsListMessage
				]
			);
		}

		$fields['MESSAGE'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_DETAILS_SITE', ['#LINK#' => $params["pathToEvent"]]);
		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_DETAILS', ['#LINK#' => $params["pathToEvent"]]);

		$fields['TITLE'] = Loc::getMessage('EC_MESS_INVITE_CHANGED_TITLE',['#TITLE#' => $params["name"]]);
		return $fields;
	}


	public static function Cancel($fields = [], $params)
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID']."|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = Loc::getMessage('EC_MESS_INVITE_CANCEL_SITE', array(
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);
		$fields['MESSAGE_OUT'] = Loc::getMessage('EC_MESS_INVITE_CANCEL', array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);
		$fields['MESSAGE'] .= "\n\n".Loc::getMessage('EC_MESS_VIEW_OWN_CALENDAR', ['#LINK#' => $params["pathToCalendar"]]);
		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_VIEW_OWN_CALENDAR_OUT', ['#LINK#' => $params["pathToCalendar"]]);
		$fields['TITLE'] = Loc::getMessage('EC_MESS_INVITE_CANCEL_TITLE', ['#TITLE#' => $params["name"]]);
		return $fields;
	}

	public static function CancelInstance($fields = [], $params)
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$params["from"]."|".$fields['TO_USER_ID']."|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = Loc::getMessage('EC_MESS_REC_THIS_CANCEL_SITE', array(
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);
		$fields['MESSAGE_OUT'] = Loc::getMessage('EC_MESS_REC_THIS_CANCEL', array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);
		$fields['MESSAGE'] .= "\n\n".Loc::getMessage('EC_MESS_VIEW_OWN_CALENDAR', ['#LINK#' => $params["pathToCalendar"]]);
		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_VIEW_OWN_CALENDAR_OUT', ['#LINK#' => $params["pathToCalendar"]]);
		$fields['TITLE'] = Loc::getMessage('EC_MESS_INVITE_CANCEL_TITLE', ['#TITLE#' => $params["name"]]);
		return $fields;
	}

	public static function CancelAllReccurent($fields = [], $params)
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID']."|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = Loc::getMessage('EC_MESS_REC_ALL_CANCEL_SITE', array(
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);
		$fields['MESSAGE_OUT'] = Loc::getMessage('EC_MESS_REC_ALL_CANCEL', array(
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);
		$fields['MESSAGE'] .= "\n\n".Loc::getMessage('EC_MESS_VIEW_OWN_CALENDAR', ['#LINK#' => $params["pathToCalendar"]]);
		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_VIEW_OWN_CALENDAR_OUT', ['#LINK#' => $params["pathToCalendar"]]);
		$fields['TITLE'] = Loc::getMessage('EC_MESS_INVITE_CANCEL_TITLE', ['#TITLE#' => $params["name"]]);

		return $fields;
	}

	public static function MeetingStatus($fields = [], $params)
	{
		$fields['NOTIFY_EVENT'] = "info";
		$fields['FROM_USER_ID'] = intval($params["guestId"]);
		$fields['TO_USER_ID'] = intval($params["userId"]);
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$params['mode'];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = Loc::getMessage($params['mode'] == 'accept' ? 'EC_MESS_INVITE_ACCEPTED_SITE' : 'EC_MESS_INVITE_DECLINED_SITE',
			array(
				'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
				'#ACTIVE_FROM#' => $params["from"]
			)
		);

		$fields['MESSAGE_OUT'] = Loc::getMessage($params['mode']=='accept' ? 'EC_MESS_INVITE_ACCEPTED' : 'EC_MESS_INVITE_DECLINED',
			array(
				'#GUEST_NAME#' => CCalendar::GetUserName($params['guestId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from"]
			)
		);

		if ($params["comment"] != "")
		{
			$fields['MESSAGE'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_ACC_COMMENT', array(
					'#COMMENT#' => $params["comment"]
				));
			$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_ACC_COMMENT', array(
					'#COMMENT#' => $params["comment"]
				));
		}
		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_DETAILS', ['#LINK#' => $params["pathToEvent"]]);

		return $fields;
	}
	public static function MeetingStatusInfo($fields = [], $params)
	{
		$fields['NOTIFY_EVENT'] = "info";
		$fields['FROM_USER_ID'] = intval($params["guestId"]);
		$fields['TO_USER_ID'] = intval($params["userId"]);
		$fields['NOTIFY_TAG'] = "CALENDAR|STATUS|".$params['eventId']."|".intval($params["userId"]);
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|STATUS|".$params['eventId'];

		$fields['MESSAGE'] = Loc::getMessage($params['mode'] =='status_accept' ? 'EC_MESS_STATUS_NOTIFY_Y_SITE' : 'EC_MESS_STATUS_NOTIFY_N_SITE',
			array(
				'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
				'#ACTIVE_FROM#' => $params["from"]
			)
		);

		$fields['MESSAGE_OUT'] = Loc::getMessage($params['mode'] == 'status_accept' ? 'EC_MESS_STATUS_NOTIFY_Y' : 'EC_MESS_STATUS_NOTIFY_N',
			array(
				'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
				'#ACTIVE_FROM#' => $params["from"]
			)
		);

		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_DETAILS', ['#LINK#' => $params["pathToEvent"]]);

		return $fields;
	}

	public static function NotifyComment($eventId, $params)
	{
		if (!Loader::includeModule("im") || intval($eventId) <= 0)
		{
			return;
		}

		$userId = intval($params["USER_ID"]);
		if ($event = CCalendarEvent::GetById($eventId))
		{
			$instanceDate = false;

			if (
				!isset($params['LOG'])
				&& Loader::includeModule('socialnetwork')
			)
			{
				$dbResult = CSocNetLog::GetList(
					[],
					["ID" => $params["LOG_ID"]],
					false,
					false,
					["ID", "SOURCE_ID", "PARAMS"]
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
					$arLog['PARAMS'] = unserialize($arLog['PARAMS'], ['allowed_classes' => false]);
					if (!is_array($arLog['PARAMS']))
						$arLog['PARAMS'] = [];
				}

				if (isset($arLog['PARAMS']['COMMENT_XML_ID']) && $arLog['PARAMS']['COMMENT_XML_ID'])
				{
					$instanceDate = CCalendarEvent::ExtractDateFromCommentXmlId($arLog['PARAMS']['COMMENT_XML_ID']);
				}
			}

			$strMsgAddComment = Loc::getMessage('EC_COMMENT_MESSAGE_ADD');

			$res = \Bitrix\Main\UserTable::getList([
				'filter' => ['=ID' => $userId],
				'select' => ['ID', 'PERSONAL_GENDER']
			]);

			if (($user = $res->fetch()) && in_array($user['PERSONAL_GENDER'], ['F', 'M']))
			{
				$strMsgAddComment = Loc::getMessage('EC_COMMENT_MESSAGE_ADD_'.$user['PERSONAL_GENDER']);
			}

			$imMessageFields = array(
				"FROM_USER_ID" => $userId,
				"NOTIFY_TYPE" => IM_NOTIFY_FROM,
				"NOTIFY_MODULE" => "calendar",
				"NOTIFY_EVENT" => "event_comment"
			);

			$aId = isset($event['PARENT_ID']) ? $event['PARENT_ID'] : $event['ID'];

			// Here we don't need info about users
			$attendees = CCalendarEvent::GetAttendees($aId);
			if (is_array($attendees) && is_array($attendees[$aId]))
			{
				if (!$instanceDate)
				{
					$instanceDate = CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false);
				}

				$attendees = $attendees[$aId];

				$excludeUserIdList = [];

				if (
					$arLog
					&& Loader::includeModule('socialnetwork')
				)
				{
					$res = \Bitrix\Socialnetwork\LogFollowTable::getList(array(
						'filter' => array(
							"=CODE" => "L".$arLog['ID'],
							"=TYPE" => "N"
						),
						'select' => ['USER_ID']
					));

					while ($unFollower = $res->fetch())
					{
						$excludeUserIdList[] = $unFollower["USER_ID"];
					}
				}

				$commentCropped = truncateText($params['MESSAGE'], 100);
				foreach($attendees as $attendee)
				{
					if (in_array($attendee["USER_ID"], $excludeUserIdList))
					{
						continue;
					}

					$url = CCalendar::GetPathForCalendarEx($attendee["USER_ID"]);
					$url = CHTTP::urlAddParams($url, ['EVENT_ID' => $eventId, 'EVENT_DATE' => $instanceDate]);

					if ($attendee["USER_ID"] != $userId && $attendee["STATUS"] != 'N')
					{
						$imMessageFields = array_merge($imMessageFields, ["TO_USER_ID" => $attendee["USER_ID"]]);

						$imMessageFields["NOTIFY_MESSAGE"] = str_replace(
							[
								"#EVENT_TITLE#",
								"#COMMENT#"
							],
							[
								$url <> '' ? "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".$event["NAME"]."</a>" : $event["NAME"],
								$commentCropped
							],
							$strMsgAddComment
						);
						$imMessageFields["NOTIFY_MESSAGE_OUT"] = str_replace(
								[
									"#EVENT_TITLE#",
									"#COMMENT#"
								],
								[
									$event["NAME"],
									$commentCropped
								],
								$strMsgAddComment
							).($url <> ''? " (".$url.")" : "");

						$imMessageFields["NOTIFY_TAG"] = "CALENDAR|COMMENT|".$aId."|".$instanceDate;

						CIMNotify::Add($imMessageFields);
					}
				}
			}
		}
	}

	public static function ClearNotifications($eventId = false, $userId = false)
	{
		if (Loader::includeModule("im"))
		{
			if ($eventId && $userId)
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

	private static function NotifyFailIcalInvite($fields = [], $params)
	{
		$fields['NOTIFY_EVENT'] = "info";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|"."icalfail";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|"."icalfail";

		foreach ($params['items'] as $item)
		{
			$usersList[] = $item['email'];
		}

		$userString = implode(', ', $usersList);

		if ($params['icalMethod'] === 'cancel')
		{
			$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_FAIL_ICAL_CANCEL', [
					'#USERS_LIST#' => $userString,
					'#NAME#' => $params['name'],
				]
			);

			$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_FAIL_ICAL_CANCEL_OUT', [
					'#USERS_LIST#' => $userString,
					'#NAME#' => $params['name'],
				]
			);

			$fields['TITLE'] = Loc::getMessage('EC_MESS_FAIL_ICAL_INVITE_TITLE_CANCEL', ['#TITLE#' => $params['name']]);
		}
		elseif ($params['icalMethod'] === 'edit')
		{
			$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_FAIL_ICAL_EDIT', [
					'#USERS_LIST#' => $userString,
					'#NAME#' => $params['name'],
				]
			);

			$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_FAIL_ICAL_EDIT_OUT', [
					'#USERS_LIST#' => $userString,
					'#NAME#' => $params['name'],
				]
			);

			$fields['TITLE'] = Loc::getMessage('EC_MESS_FAIL_ICAL_INVITE_TITLE_EDIT', ['#TITLE#' => $params['name']]);
		}
		elseif ($params['icalMethod'] === 'request')
		{
			$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_FAIL_ICAL_REQUEST', [
					'#USERS_LIST#' => $userString,
					'#NAME#' => $params['name'],
				]
			);

			$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_FAIL_ICAL_REQUEST_OUT', [
					'#USERS_LIST#' => $userString,
					'#NAME#' => $params['name'],
				]
			);

			$fields['TITLE'] = Loc::getMessage('EC_MESS_FAIL_ICAL_INVITE_TITLE_REQUEST', ['#TITLE#' => $params['name']]);
		}



		return $fields;
	}
}
?>