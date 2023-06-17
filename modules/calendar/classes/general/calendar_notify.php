<?
/** var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);

use Bitrix\Calendar\Core\Mappers;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Calendar\Sharing;

class CCalendarNotify
{
	const PUSH_MESSAGE_MAX_LENGTH = 255;

	public static function Send($params)
	{
		if (!Loader::includeModule("im"))
		{
			return false;
		}

		$culture = \Bitrix\Main\Context::getCurrent()->getCulture();

		$params['rrule'] = CCalendarEvent::GetRRULEDescription($params['fields'] ?? null, false, false);
		$params["eventId"] = (int)($params["eventId"] ?? null);
		$mode = $params['mode'];
		$fromUser = (int)$params["userId"];
		$toUser = (int)$params["guestId"];
		if (!$fromUser || !$toUser || ($toUser === $fromUser && !in_array($mode, ['status_accept', 'status_decline', 'fail_ical_invite'])))
		{
			return false;
		}

		$fromTimestamp = CCalendar::Timestamp($params["from"] ?? null);
		if (($params['fields']['DT_SKIP_TIME'] ?? null) === 'Y')
		{
			$params["from"] = CCalendar::Date($fromTimestamp, false);
			$params["from_formatted"] = FormatDate($culture->getFullDateFormat(), $fromTimestamp);

		}
		else
		{
			$params["from"] = CCalendar::Date($fromTimestamp, true, true, true);
			$params["from_formatted"] = FormatDate($culture->getFullDateFormat(), $fromTimestamp)
				. ' '
				. FormatDate($culture->getShortTimeFormat(), $fromTimestamp);
		}

		$notifyFields = [
			'EMAIL_TEMPLATE' => "CALENDAR_INVITATION",
			'NOTIFY_MODULE' => "calendar",
		];

		if ($mode === 'accept' || $mode === 'decline')
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
			'filter' => ['=ID' => $toUser, '=ACTIVE' => 'Y'],
			'select' => ['ID']
		]);
		if (!$userOrm->fetch())
		{
			return false;
		}

		$eventId = $params["eventId"] ?? null;
		if (($params['isSharing'] ?? false) && $params['mode'] === 'status_accept')
		{
			$params["pathToCalendar"] = CCalendar::GetPathForCalendarEx($notifyFields['FROM_USER_ID'] ?? null);
		}
		else
		{
			$params["pathToCalendar"] = CCalendar::GetPathForCalendarEx($notifyFields['TO_USER_ID'] ?? null);
		}

		if (!empty($params["pathToCalendar"]) && $eventId)
		{
			$params["pathToCalendar"] = CHTTP::urlDeleteParams($params["pathToCalendar"], ["action", "sessid", "bx_event_calendar_request", "EVENT_ID"]);

			if (($params['isSharing'] ?? false) && $mode === 'cancel_sharing')
			{
				$params["pathToEvent"] = CHTTP::urlAddParams($params["pathToCalendar"], ['EVENT_ID' => $eventId, 'IS_SHARING' => 1]);
			}
			else
			{
				$params["pathToEvent"] = CHTTP::urlAddParams($params["pathToCalendar"], ['EVENT_ID' => $eventId]);
			}
		}

		$notifyFields = [
			'FROM_USER_ID' => $fromUser,
			'TO_USER_ID' => $toUser,
			'EMAIL_TEMPLATE' => "CALENDAR_INVITATION",
			'NOTIFY_MODULE' => "calendar",
		];

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
			case 'cancel_sharing':
				$notifyFields = self::CancelSharing($notifyFields, $params);
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
			case 'delete_location':
				$notifyFields = self::DeleteLocation($notifyFields, $params);
				break;
			case 'cancel_booking':
				$notifyFields = self::CancelBooking($notifyFields, $params);
				break;
		}

		$messageId = CIMNotify::Add($notifyFields);
		if (!empty($params['markRead']) && $messageId > 0)
		{
			$CIMNotify = new CIMNotify((int)($params["userId"] ?? null));
			$CIMNotify->MarkNotifyRead($messageId);
		}

		foreach(GetModuleEvents("calendar", "OnSendInvitationMessage", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$params]);
		}

		if (($params['isSharing'] ?? false) && $mode === 'decline')
		{
			self::notifySharingUser($params["eventId"]);
		}
	}

	protected static function notifySharingUser(int $eventId): void
	{
		/** @var \Bitrix\Calendar\Core\Event\Event $event */
		$event = (new Mappers\Event())->getById($eventId);
		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = (new Sharing\Link\Factory())->getEventLinkByEventId($eventId);

		if (!$eventLink)
		{
			return;
		}

		$host = CUser::GetByID($eventLink->getHostId())->Fetch();
		$email = $host['PERSONAL_MAILBOX'] ?? null;
		$phone = $host['PERSONAL_PHONE'] ?? null;
		$userContact = !empty($email) ? $email : $phone;

		$notificationService = null;
		if ($userContact && Sharing\SharingEventManager::isEmailCorrect($userContact))
		{
			$notificationService = (new Sharing\Notification\Mail())
				->setEventLink($eventLink)
				->setEvent($event)
			;
		}

		if ($notificationService !== null)
		{
			$notificationService->notifyAboutMeetingStatus($userContact);
		}
	}

	public static function Invite($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "invite";
		$fields['NOTIFY_TYPE'] = IM_NOTIFY_CONFIRM;
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID'];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|" . $params['eventId'] ?? null;

		if (!empty($params['rrule']))
		{
			$fromTs = CCalendar::Timestamp($params['fields']['DATE_FROM'] ?? null);
			$toTs = CCalendar::Timestamp($params['fields']['DATE_TO'] ?? null);

			if (($params['fields']['DT_SKIP_TIME'] ?? null) === "Y")
			{
				$toTs += CCalendar::DAY_LENGTH;
			}
			else
			{
				$fromTs = $fromTs
					- CCalendar::GetTimezoneOffset($params['fields']['TZ_FROM'] ?? null)
					+ CCalendar::GetCurrentOffsetUTC($fields['TO_USER_ID'] ?? null);
				$toTs = $toTs
					- CCalendar::GetTimezoneOffset($params['fields']['TZ_TO'] ?? null)
					+ CCalendar::GetCurrentOffsetUTC($fields['TO_USER_ID'] ?? null);
			}

			$params['from_to_html'] = CCalendar::GetFromToHtml(
				$fromTs,
				$toTs,
				($params['fields']['DT_SKIP_TIME'] ?? null) === 'Y',
				$params['fields']['DT_LENGTH'] ?? null,
				true
			);

			$fields['MESSAGE'] = Loc::getMessage('EC_MESS_REC_INVITE_SITE', [
				'#TITLE#' => $params["name"] ?? null,
				'#ACTIVE_FROM#' => $params['from_to_html'],
				'#RRULE#' => $params["rrule"] ?? null
			]);
			$fields['MESSAGE_OUT'] = Loc::getMessage('EC_MESS_REC_INVITE', [
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params['from_to_html'],
				'#RRULE#' => $params["rrule"]
			]);
		}
		else
		{
			$fields['MESSAGE'] = Loc::getMessage('EC_MESS_INVITE_SITE', [
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from_formatted"]
			]);

			$fields['MESSAGE_OUT'] = Loc::getMessage('EC_MESS_INVITE', [
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from_formatted"]
			]);
		}

		if ($params['location'])
		{
			$fields['MESSAGE'] .= "\n\n" . Loc::getMessage('EC_EVENT_REMINDER_LOCATION', [
				'#LOCATION#' => $params['location']
			]);
			$fields['MESSAGE_OUT'] .= "\n\n" . Loc::getMessage('EC_EVENT_REMINDER_LOCATION', [
				'#LOCATION#' => $params['location']
			]);
		}

		if ($params['isSharing'] ?? false)
		{
			$fields['MESSAGE'] = Loc::getMessage('EC_MESS_INVITE_SITE_SHARING', [
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from_formatted"],
			]);
		}

		$fields['PUSH_MESSAGE'] = str_replace(
				['[B]', '[/B]'],
				['', ''],
				$fields['MESSAGE']
			);

		$fields['MESSAGE'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_DETAILS_SITE', ['#LINK#' => $params["pathToEvent"]]);

		$fields['NOTIFY_BUTTONS'] = [
			['TITLE' => Loc::getMessage('EC_MESS_INVITE_CONF_Y_SITE'), 'VALUE' => 'Y', 'TYPE' => 'accept'],
			['TITLE' => Loc::getMessage('EC_MESS_INVITE_CONF_N_SITE'), 'VALUE' => 'N', 'TYPE' => 'cancel']
		];

		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_CONF_Y', ['#LINK#' => $params["pathToEvent"].'&CONFIRM=Y']);
		$fields['MESSAGE_OUT'] .= "\n".Loc::getMessage('EC_MESS_INVITE_CONF_N', ['#LINK#' => $params["pathToEvent"].'&CONFIRM=N']);
		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_DETAILS', ['#LINK#' => $params["pathToEvent"]]);

		$fields['TITLE'] = Loc::getMessage('EC_MESS_INVITE_TITLE',
			[
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"]
			]
		);

		return $fields;
	}

	public static function ChangeNotify($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID'];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		// Was changed only one field in this case we could be more specific
		if (count($params['entryChanges']) === 1)
		{
			$change = $params['entryChanges'][0];
			switch($change['fieldKey'])
			{
				case 'NAME':
					$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_TITLE_CHANGED',
						[
							'#OLD_TITLE#' => $change['oldValue'],
							'#NEW_TITLE#' => "[url=".$params["pathToEvent"]."]".$change['newValue']."[/url]",
							'#ACTIVE_FROM#' => $params["from_formatted"]
						]
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_TITLE_CHANGED',
						[
							'#OLD_TITLE#' => $change['oldValue'],
							'#NEW_TITLE#' => $change['newValue'],
							'#ACTIVE_FROM#' => $params["from_formatted"]
						]
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
						[
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
							'#OLD_DATE_FROM#' => $change['oldValue'],
							'#NEW_DATE_FROM#' => $change['newValue']
						]
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_DATE_FROM_CHANGED',
						[
							'#TITLE#' => $params["name"],
							'#OLD_DATE_FROM#' => $change['oldValue'],
							'#NEW_DATE_FROM#' => $change['newValue']
						]
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
						[
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
							'#OLD_DATE_TO#' => $change['oldValue'],
							'#NEW_DATE_TO#' => $change['newValue']
						]
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_DATE_TO_CHANGED',
						[
							'#TITLE#' => $params["name"],
							'#OLD_DATE_TO#' => $change['oldValue'],
							'#NEW_DATE_TO#' => $change['newValue']
						]
					);
					break;
				case 'LOCATION':
					$locationMessageCode = empty($change['newValue']) ? 'EC_NOTIFY_LOCATION_CHANGED_NONE' : 'EC_NOTIFY_LOCATION_CHANGED';
					$fields['MESSAGE'] = Loc::getMessage($locationMessageCode,
						[
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
							'#ACTIVE_FROM#' => $params["from"],
							'#NEW_VALUE#' => \CCalendar::GetTextLocation($change['newValue'])
						]
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage($locationMessageCode,
						[
							'#TITLE#' => $params["name"],
							'#ACTIVE_FROM#' => $params["from"],
							'#NEW_VALUE#' => \CCalendar::GetTextLocation($change['newValue'])
						]
					);
					break;
				case 'ATTENDEES':
					$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_ATTENDEES_CHANGED',
						[
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
							'#ACTIVE_FROM#' => $params["from_formatted"]
						]
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_ATTENDEES_CHANGED',
						[
							'#TITLE#' => $params["name"],
							'#ACTIVE_FROM#' => $params["from_formatted"]
						]
					);
					break;
				case 'DESCRIPTION':
					$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_DESCRIPTION_CHANGED',
						[
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
							'#ACTIVE_FROM#' => $params["from_formatted"]
						]
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_DESCRIPTION_CHANGED',
						[
							'#TITLE#' => $params["name"],
							'#ACTIVE_FROM#' => $params["from_formatted"]
						]
					);
					break;
				case 'RRULE':
				case 'EXDATE':
					$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_RRULE_CHANGED',
						[
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]"
						]
					);

					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_RRULE_CHANGED',
						[
							'#TITLE#' => $params["name"]
						]
					);
					break;
				case 'IMPORTANCE':
					$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_IMPORTANCE_CHANGED',
						[
							'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
							'#ACTIVE_FROM#' => $params["from_formatted"]
						]
					);
					$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_IMPORTANCE_CHANGED',
						[
							'#TITLE#' => $params["name"],
							'#ACTIVE_FROM#' => $params["from_formatted"]
						]
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
					'#ACTIVE_FROM#' => $params["from_formatted"],
					'#CHANGED_FIELDS_LIST#' => $changedFieldsListMessage
				]
			);

			$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_ENTRY_CHANGED',
				[
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => $params["from_formatted"],
					'#CHANGED_FIELDS_LIST#' => $changedFieldsListMessage
				]
			);
		}

		$fields['MESSAGE'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_DETAILS_SITE', ['#LINK#' => $params["pathToEvent"]]);
		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_DETAILS', ['#LINK#' => $params["pathToEvent"]]);

		$fields['TITLE'] = Loc::getMessage('EC_MESS_INVITE_CHANGED_TITLE',['#TITLE#' => $params["name"]]);
		return $fields;
	}


	public static function Cancel($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID']."|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = Loc::getMessage('EC_MESS_INVITE_CANCEL_SITE', [
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from_formatted"]
			]
		);
		$fields['MESSAGE_OUT'] = Loc::getMessage('EC_MESS_INVITE_CANCEL', [
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from_formatted"]
			]
		);
		$fields['MESSAGE'] .= "\n\n".Loc::getMessage('EC_MESS_VIEW_OWN_CALENDAR', ['#LINK#' => $params["pathToCalendar"]]);
		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_VIEW_OWN_CALENDAR_OUT', ['#LINK#' => $params["pathToCalendar"]]);
		$fields['TITLE'] = Loc::getMessage('EC_MESS_INVITE_CANCEL_TITLE', ['#TITLE#' => $params["name"]]);
		return $fields;
	}

	public static function CancelInstance($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$params["from"]."|".$fields['TO_USER_ID']."|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = Loc::getMessage('EC_MESS_REC_THIS_CANCEL_SITE', [
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from_formatted"]
			]
		);
		$fields['MESSAGE_OUT'] = Loc::getMessage('EC_MESS_REC_THIS_CANCEL', [
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from_formatted"]
			]
		);
		$fields['MESSAGE'] .= "\n\n".Loc::getMessage('EC_MESS_VIEW_OWN_CALENDAR', ['#LINK#' => $params["pathToCalendar"]]);
		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_VIEW_OWN_CALENDAR_OUT', ['#LINK#' => $params["pathToCalendar"]]);
		$fields['TITLE'] = Loc::getMessage('EC_MESS_INVITE_CANCEL_TITLE', ['#TITLE#' => $params["name"]]);
		return $fields;
	}

	public static function CancelAllReccurent($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID']."|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = Loc::getMessage('EC_MESS_REC_ALL_CANCEL_SITE', [
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from_formatted"]
			]
		);
		$fields['MESSAGE_OUT'] = Loc::getMessage('EC_MESS_REC_ALL_CANCEL', [
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from_formatted"]
			]
		);
		$fields['MESSAGE'] .= "\n\n".Loc::getMessage('EC_MESS_VIEW_OWN_CALENDAR', ['#LINK#' => $params["pathToCalendar"]]);
		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_VIEW_OWN_CALENDAR_OUT', ['#LINK#' => $params["pathToCalendar"]]);
		$fields['TITLE'] = Loc::getMessage('EC_MESS_INVITE_CANCEL_TITLE', ['#TITLE#' => $params["name"]]);

		return $fields;
	}

	public static function CancelSharing($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID']."|sharing|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = Loc::getMessage('EC_MESS_INVITE_CANCEL_SHARING', [
			'#TITLE#' => $params["name"],
			'#ACTIVE_FROM#' => $params['from_formatted'],
		]);

		$fields['MESSAGE'] .= "\n\n" . Loc::getMessage('EC_MESS_INVITE_CANCEL_SHARING_SITE', [
			'#LINK#' =>  $params["pathToEvent"],
		]);

		$fields['MESSAGE_OUT'] = $fields['MESSAGE'];
		$fields['TITLE'] = Loc::getMessage('EC_MESS_INVITE_CANCEL_TITLE', ['#TITLE#' => $params["name"]]);

		return $fields;
	}

	public static function MeetingStatus($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "info";
		$fields['FROM_USER_ID'] = (int)$params["guestId"];
		$fields['TO_USER_ID'] = (int)$params["userId"];
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$params['mode'];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = Loc::getMessage(
			$params['mode'] === 'accept'
				? 'EC_MESS_INVITE_ACCEPTED_SITE_1'
				: 'EC_MESS_INVITE_DECLINED_SITE_1',
			[
				'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
				'#ACTIVE_FROM#' => $params["from_formatted"]
			]
		);

		$fields['MESSAGE_OUT'] = Loc::getMessage(
			$params['mode'] ==='accept'
				? 'EC_MESS_INVITE_ACCEPTED_1'
				: 'EC_MESS_INVITE_DECLINED_1',
			[
				'#GUEST_NAME#' => CCalendar::GetUserName($params['guestId']),
				'#TITLE#' => $params["name"],
				'#ACTIVE_FROM#' => $params["from_formatted"]
			]
		);

		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_DETAILS', ['#LINK#' => $params["pathToEvent"]]);

		return $fields;
	}
	public static function MeetingStatusInfo($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "info";
		$fields['FROM_USER_ID'] = (int)$params["guestId"];
		$fields['TO_USER_ID'] = (int)$params["userId"];
		$fields['NOTIFY_TAG'] = "CALENDAR|STATUS|".$params['eventId']."|". (int)$params["userId"];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|STATUS|".$params['eventId'];

		if (($params['isSharing'] ?? false) && $params['mode'] === 'status_accept')
		{
			$fields['MESSAGE'] = Loc::getMessage(
				'EC_MESS_AUTO_INVITE_ACCEPT',
				[
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => $params["from_formatted"]
				]
			);

			$fields['MESSAGE'] .=  "\n\n" . Loc::getMessage('EC_MESS_AUTO_INVITE_ACCEPT_DETAILS', ['#LINK#' => $params["pathToEvent"]]);
		}
		else
		{
			$fields['MESSAGE'] = Loc::getMessage(
				$params['mode'] === 'status_accept'
					? 'EC_MESS_STATUS_NOTIFY_Y_SITE'
					: 'EC_MESS_STATUS_NOTIFY_N_SITE',
				[
					'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
					'#ACTIVE_FROM#' => $params["from_formatted"]
				]
			);
		}

		$fields['MESSAGE_OUT'] = Loc::getMessage(
			$params['mode'] === 'status_accept'
				? 'EC_MESS_STATUS_NOTIFY_Y'
				: 'EC_MESS_STATUS_NOTIFY_N',
			[
				'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
				'#ACTIVE_FROM#' => $params["from_formatted"]
			]
		);

		$fields['MESSAGE_OUT'] .= "\n\n".Loc::getMessage('EC_MESS_INVITE_DETAILS', ['#LINK#' => $params["pathToEvent"]]);

		return $fields;
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function NotifyComment($eventId, $params)
	{
		if (!Loader::includeModule("im") || (int)$eventId <= 0)
		{
			return;
		}

		$userId = (int)$params["USER_ID"];
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
				if ($arLog['PARAMS'])
				{
					$arLog['PARAMS'] = unserialize($arLog['PARAMS'], ['allowed_classes' => false]);
					if (!is_array($arLog['PARAMS']))
					{
						$arLog['PARAMS'] = [];
					}
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

			$imMessageFields = [
				"FROM_USER_ID" => $userId,
				"NOTIFY_TYPE" => IM_NOTIFY_FROM,
				"NOTIFY_MODULE" => "calendar",
				"NOTIFY_EVENT" => "event_comment"
			];

			$aId = $event['PARENT_ID'] ?? $event['ID'];

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
					$res = \Bitrix\Socialnetwork\LogFollowTable::getList([
						'filter' => [
							"=CODE" => "L".$arLog['ID'],
							"=TYPE" => "N"
						],
						'select' => ['USER_ID']
					]);

					while ($unFollower = $res->fetch())
					{
						$excludeUserIdList[] = (int)$unFollower["USER_ID"];
					}
				}

				$commentCropped = truncateText(CTextParser::clearAllTags($params['MESSAGE']), 120);
				foreach($attendees as $attendee)
				{
					$attendeeId = (int)$attendee['USER_ID'];
					if (in_array($attendeeId, $excludeUserIdList, true))
					{
						continue;
					}

					$url = CCalendar::GetPathForCalendarEx($attendeeId);
					$url = CHTTP::urlAddParams($url, ['EVENT_ID' => $eventId, 'EVENT_DATE' => $instanceDate]);

					if ($attendeeId !== $userId && $attendee["STATUS"] !== 'N')
					{
						$imMessageFields['TO_USER_ID'] = $attendeeId;

						$imMessageFields["NOTIFY_MESSAGE"] = str_replace(
							[
								"#EVENT_TITLE#",
								"#COMMENT#"
							],
							[
								$url ? "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".$event["NAME"]."</a>" : $event["NAME"],
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
							).($url ? " (".$url.")" : "");

						$imMessageFields["NOTIFY_TAG"] = "CALENDAR|COMMENT|".$aId."|".$instanceDate;

						CIMNotify::Add($imMessageFields);
					}
				}
			}
		}
	}

	/**
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function ClearNotifications($eventId = false, $userId = false): void
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

	private static function NotifyFailIcalInvite($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "info";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|"."icalfail";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|"."icalfail";

		foreach ($params['items'] as $item)
		{
			if (is_string($item))
			{
				$usersList[] = $item;
			}
			if (isset($item['email']) && is_string($item['email']))
			{
				$usersList[] = $item['email'];
			}
		}

		$userString = implode(', ', $usersList);

		if ($params['icalMethod'] === 'cancel')
		{
			$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_FAIL_ICAL_CANCEL', [
				'#USERS_LIST#' => $userString,
				'#NAME#' => $params['name'],
			]);

			$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_FAIL_ICAL_CANCEL_OUT', [
				'#USERS_LIST#' => $userString,
				'#NAME#' => $params['name'],
			]);

			$fields['TITLE'] = Loc::getMessage('EC_MESS_FAIL_ICAL_INVITE_TITLE_CANCEL', ['#TITLE#' => $params['name']]);
		}
		elseif ($params['icalMethod'] === 'edit')
		{
			$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_FAIL_ICAL_EDIT', [
				'#USERS_LIST#' => $userString,
				'#NAME#' => $params['name'],
			]);

			$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_FAIL_ICAL_EDIT_OUT', [
				'#USERS_LIST#' => $userString,
				'#NAME#' => $params['name'],
			]);

			$fields['TITLE'] = Loc::getMessage('EC_MESS_FAIL_ICAL_INVITE_TITLE_EDIT', ['#TITLE#' => $params['name']]);
		}
		elseif ($params['icalMethod'] === 'request')
		{
			$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_FAIL_ICAL_REQUEST', [
				'#USERS_LIST#' => $userString,
				'#NAME#' => $params['name'],
			]);

			$fields['MESSAGE_OUT'] = Loc::getMessage('EC_NOTIFY_FAIL_ICAL_REQUEST_OUT', [
				'#USERS_LIST#' => $userString,
				'#NAME#' => $params['name'],
			]);

			$fields['TITLE'] = Loc::getMessage('EC_MESS_FAIL_ICAL_INVITE_TITLE_REQUEST', [
				'#TITLE#' => $params['name']
			]);
		}



		return $fields;
	}

	public static function DeleteLocation($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "delete_location";
		$fields['FROM_USER_ID'] = (int)$params["userId"];
		$fields['TO_USER_ID'] = (int)$params["guestId"];
		$fields['NOTIFY_TAG'] = "CALENDAR|LOCATION|".$params['locationId']."|". (int)$params["userId"];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|LOCATION|".$params['locationId'];

		$fields['MESSAGE'] = Loc::getMessage('EC_NOTIFY_DELETE_LOCATION', [
			'#LOCATION#' => $params["location"]
		]);

		return $fields;
	}

	public static function CancelBooking($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = 'release_location';
		$fields['FROM_USER_ID'] = (int)$params['userId'];
		$fields['TO_USER_ID'] = (int)$params['guestId'];
		$fields['NOTIFY_TAG'] =
			'CALENDAR|LOCATION|' . (int)$params['locationId']
			. '|' . (int)$params['userId'] . '|' . (int)$params['eventId'] . '|' . 'cancel'
		;
		$fields['NOTIFY_SUB_TAG'] = 'CALENDAR|LOCATION|' . $params['locationId'];

		switch ($params['recursionMode'])
		{
			case 'all':
				$notificationCode = 'EC_NOTIFY_CANCEL_BOOKING_ALL';
				break;
			case 'next':
				$notificationCode = 'EC_NOTIFY_CANCEL_BOOKING_NEXT';
				break;
			default:
				$notificationCode = 'EC_NOTIFY_CANCEL_BOOKING_THIS';
				break;
		}
		$fromTime = '';
		$fromDate = '';
		if($params['fields']['DT_SKIP_TIME'] === 'N')
		{
			$fromTime = Loc::getMessage('EC_NOTIFY_CANCEL_BOOKING_TIME', [
				'#FROM_TIME#'=> mb_substr($params['from'], -5, 5)
			]);
			$fromDate = mb_substr($params['from'], 0, -6);
		}
		$fields['MESSAGE'] =
			Loc::getMessage($notificationCode, [
				'#FROM#' => $params['from_formatted'],
				'#LINK#' => $params['pathToEvent'],
				'#EVENT#' => $params['eventName'],
				'#FREQUENCY#' => $params['rrule'],
				'#FROM_TIME#' => $fromTime,
				'#FROM_DATE#' => $fromDate,
			])
			.
			Loc::getMessage('EC_NOTIFY_CANCEL_BOOKING_ENDING')
		;

		return $fields;
	}
}
?>