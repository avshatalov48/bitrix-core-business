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
	public const NOTIFY_USERS_ADDED_TO_MULTI_LINK = 'users_added_to_multi_link';

	public static function Send($params)
	{
		if (!Loader::includeModule("im"))
		{
			return false;
		}

//		$params['rrule'] = CCalendarEvent::GetRRULEDescription($params['fields'] ?? null, false, false);
		$params["eventId"] = (int)($params["eventId"] ?? null);
		$mode = $params['mode'];
		$fromUser = (int)$params["userId"];
		$toUser = (int)$params["guestId"];
		if (!$fromUser || !$toUser || ($toUser === $fromUser && !in_array($mode, ['status_accept', 'status_decline', 'fail_ical_invite'])))
		{
			return false;
		}

		$params['from_timestamp'] = CCalendar::Timestamp($params["from"] ?? null);
		if (($params['fields']['DT_SKIP_TIME'] ?? null) === 'Y')
		{
			$params["from"] = CCalendar::Date($params['from_timestamp'], false);
		}
		else
		{
			$params["from"] = CCalendar::Date($params['from_timestamp'], true, true, true);
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

		$spaceEventData = $notifyFields;
		$spaceEventData['ID'] = $params['eventId'] ?? null;
		$spaceEventData['ATTENDEES_CODES'] = $params['fields']['ATTENDEES_CODES'] ?? null;
		unset($spaceEventData['TITLE']);
		(new \Bitrix\Calendar\Integration\SocialNetwork\SpaceService())->addEvent(
			$mode,
			$spaceEventData
		);

		foreach(GetModuleEvents("calendar", "OnSendInvitationMessage", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$params]);
		}

		return true;
	}

	public static function Invite($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "invite";
		$fields['NOTIFY_TYPE'] = IM_NOTIFY_CONFIRM;
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID'];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|" . $params['eventId'] ?? null;

		if (!empty($params['fields']['RRULE']))
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

			$params['from_to_html'] = fn (?string $languageId = null) => CCalendar::GetFromToHtml(
				$fromTs,
				$toTs,
				($params['fields']['DT_SKIP_TIME'] ?? null) === 'Y',
				$params['fields']['DT_LENGTH'] ?? null,
				true,
				$languageId
			);
		}

		$inviteMessage = static function (?string $languageId = null) use ($params)
		{
			if (!empty($params['fields']['RRULE']))
			{
				$result = Loc::getMessage(
					'EC_MESS_REC_INVITE_SITE',
					[
						'#TITLE#' => $params["name"] ?? null,
						'#ACTIVE_FROM#' => $params['from_to_html']($languageId),
						'#RRULE#' => CCalendarEvent::GetRRULEDescription($params['fields'], false, false, $languageId)
					],
					$languageId
				);
			}
			else
			{
				$result = Loc::getMessage(
					'EC_MESS_INVITE_SITE',
					[
						'#TITLE#' => $params["name"],
						'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
					],
					$languageId
				);
			}

			if ($params['location'])
			{
				$result .= "\n\n" . Loc::getMessage(
					'EC_EVENT_REMINDER_LOCATION',
					[
						'#LOCATION#' => $params['location']
					],
					$languageId
				);
			}

			if ($params['isSharing'] ?? false)
			{
				$result = Loc::getMessage(
					'EC_MESS_INVITE_SITE_SHARING',
					[
						'#TITLE#' => $params["name"],
						'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId),
					],
					$languageId
				);
			}

			$result .= "\n\n" . Loc::getMessage('EC_MESS_INVITE_DETAILS_SITE', ['#LINK#' => $params["pathToEvent"]], $languageId);

			return $result;
		};

		$inviteMessageOut = static function (?string $languageId, ?string $ownerName) use ($params)
		{
			if (!empty($params['fields']['RRULE']))
			{
				$result = Loc::getMessage(
					'EC_MESS_REC_INVITE',
					[
						'#OWNER_NAME#' => $ownerName,
						'#TITLE#' => $params["name"],
						'#ACTIVE_FROM#' => $params['from_to_html']($languageId),
						'#RRULE#' => CCalendarEvent::GetRRULEDescription($params['fields'], false, false, $languageId)
					],
					$languageId
				);
			}
			else
			{
				$result = Loc::getMessage(
					'EC_MESS_INVITE',
					[
						'#OWNER_NAME#' => $ownerName,
						'#TITLE#' => $params["name"],
						'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
					],
					$languageId
				);
			}

			if ($params['location'])
			{
				$result .= "\n\n" . Loc::getMessage(
					'EC_EVENT_REMINDER_LOCATION',
					['#LOCATION#' => $params['location']],
					$languageId
				);
			}

			$result .= "\n\n" . Loc::getMessage('EC_MESS_INVITE_CONF_Y', ['#LINK#' => $params["pathToEvent"] . '&CONFIRM=Y'], $languageId)
				. "\n" . Loc::getMessage('EC_MESS_INVITE_CONF_N', ['#LINK#' => $params["pathToEvent"] . '&CONFIRM=N'], $languageId)
				. "\n\n" . Loc::getMessage('EC_MESS_INVITE_DETAILS', ['#LINK#' => $params["pathToEvent"]], $languageId)
			;

			return $result;
		};

		$fields['MESSAGE'] = fn (?string $languageId = null) => $inviteMessage($languageId);

		$ownerName = CCalendar::GetUserName($params['userId']);
		$fields['MESSAGE_OUT'] = fn (?string $languageId = null) => $inviteMessageOut($languageId, $ownerName);

		$fields['PUSH_MESSAGE'] = fn (?string $languageId = null) => str_replace(
			['[B]', '[/B]'],
			['', ''],
			$inviteMessage($languageId)
		);

		$fields['NOTIFY_LINK'] = $params["pathToEvent"];

		$fields['NOTIFY_BUTTONS'] = [
			['TITLE' => Loc::getMessage('EC_MESS_INVITE_CONF_Y_SITE'), 'VALUE' => 'Y', 'TYPE' => 'accept'],
			['TITLE' => Loc::getMessage('EC_MESS_INVITE_CONF_N_SITE'), 'VALUE' => 'N', 'TYPE' => 'cancel']
		];

		$fields['TITLE'] = fn (?string $languageId = null) => Loc::getMessage(
			'EC_MESS_INVITE_TITLE',
			[
				'#OWNER_NAME#' => CCalendar::GetUserName($params['userId']),
				'#TITLE#' => $params["name"]
			],
			$languageId
		);

		return $fields;
	}

	public static function ChangeNotify($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID'];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$getValueWithViewEventUrl = static function ($value) use ($params)
		{
			return '[url=' . $params["pathToEvent"] . ']' . $value . '[/url]';
		};

		$changeMessage = static function (?string $languageId, $isOutMessage, $changedLocation) use ($params, $getValueWithViewEventUrl)
		{
			$result = '';

			if (count($params['entryChanges']) === 1)
			{
				$change = $params['entryChanges'][0];
				switch($change['fieldKey'])
				{
					case 'NAME':
						$result = Loc::getMessage(
							'EC_NOTIFY_TITLE_CHANGED',
							[
								'#OLD_TITLE#' => $change['oldValue'],
								'#NEW_TITLE#' => $isOutMessage ? $change['newValue'] : $getValueWithViewEventUrl($change['newValue']),
								'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
							],
							$languageId
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

						$result = Loc::getMessage(
							'EC_NOTIFY_DATE_FROM_CHANGED',
							[
								'#TITLE#' => $isOutMessage ? $params["name"] : $getValueWithViewEventUrl($params["name"]),
								'#OLD_DATE_FROM#' => $change['oldValue'],
								'#NEW_DATE_FROM#' => $change['newValue']
							],
							$languageId
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

						$result = Loc::getMessage(
							'EC_NOTIFY_DATE_TO_CHANGED',
							[
								'#TITLE#' => $isOutMessage ? $params["name"] : $getValueWithViewEventUrl($params["name"]),
								'#OLD_DATE_TO#' => $change['oldValue'],
								'#NEW_DATE_TO#' => $change['newValue']
							],
							$languageId
						);

						break;
					case 'LOCATION':
						$locationMessageCode = empty($change['newValue'])
							? 'EC_NOTIFY_LOCATION_CHANGED_NONE'
							: 'EC_NOTIFY_LOCATION_CHANGED'
						;
						$result = Loc::getMessage(
							$locationMessageCode,
							[
								'#TITLE#' => $isOutMessage ? $params["name"] : $getValueWithViewEventUrl($params["name"]),
								'#ACTIVE_FROM#' => $params["from"],
								'#NEW_VALUE#' => $changedLocation
							],
							$languageId
						);
						break;
					case 'ATTENDEES':
						$result = Loc::getMessage(
							'EC_NOTIFY_ATTENDEES_CHANGED',
							[
								'#TITLE#' => $isOutMessage ? $params["name"] : $getValueWithViewEventUrl($params["name"]),
								'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
							],
							$languageId
						);

						break;
					case 'DESCRIPTION':
						$result = Loc::getMessage(
							'EC_NOTIFY_DESCRIPTION_CHANGED',
							[
								'#TITLE#' => $isOutMessage ? $params["name"] : $getValueWithViewEventUrl($params["name"]),
								'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
							],
							$languageId
						);
						break;
					case 'RRULE':
					case 'EXDATE':
						$result = Loc::getMessage(
							'EC_NOTIFY_RRULE_CHANGED',
							[
								'#TITLE#' => $isOutMessage ? $params["name"] : $getValueWithViewEventUrl($params["name"])
							],
							$languageId
						);
						break;
					case 'IMPORTANCE':
						$result = Loc::getMessage(
							'EC_NOTIFY_IMPORTANCE_CHANGED',
							[
								'#TITLE#' => $isOutMessage ? $params["name"] : $getValueWithViewEventUrl($params["name"]),
								'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
							],
							$languageId
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
					$changedFieldsList[] = Loc::getMessage('EC_NOTIFY_FIELD_' . $key, null, $languageId);
				}
				$changedFieldsListMessage = implode(', ', array_unique($changedFieldsList));

				$result = Loc::getMessage(
					'EC_NOTIFY_ENTRY_CHANGED',
					[
						'#TITLE#' => $isOutMessage ? $params["name"] : $getValueWithViewEventUrl($params["name"]),
						'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId),
						'#CHANGED_FIELDS_LIST#' => $changedFieldsListMessage
					],
					$languageId
				);
			}

			return $result;
		};

		$changedLocation = (
			($entryChange = current($params['entryChanges']))
			&& $entryChange['fieldKey'] === 'LOCATION'
			&& !empty($entryChange['newValue'])
		)
			? CCalendar::GetTextLocation($entryChange['newValue'])
			: ''
		;
		$fields['MESSAGE'] = fn (?string $languageId = null) => $changeMessage($languageId, false, $changedLocation)
			. "\n\n" . Loc::getMessage('EC_MESS_INVITE_DETAILS_SITE', ['#LINK#' => $params["pathToEvent"]], $languageId)
		;
		$fields['MESSAGE_OUT'] = fn (?string $languageId = null) => $changeMessage($languageId, true, $changedLocation)
			. "\n\n" . Loc::getMessage('EC_MESS_INVITE_DETAILS', ['#LINK#' => $params["pathToEvent"]], $languageId)
		;
		$fields['NOTIFY_LINK'] = $params["pathToEvent"];
		$fields['TITLE'] = fn (?string $languageId = null) => Loc::getMessage(
			'EC_MESS_INVITE_CHANGED_TITLE',
			['#TITLE#' => $params["name"]],
			$languageId
		);

		return $fields;
	}


	public static function Cancel($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID']."|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];
		$fields['MESSAGE'] = fn (?string $languageId = null) =>
			Loc::getMessage(
				'EC_MESS_INVITE_CANCEL_SITE',
				[
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
				],
				$languageId
			)
			. "\n\n"
			. Loc::getMessage(
				'EC_MESS_VIEW_OWN_CALENDAR',
				['#LINK#' => $params["pathToCalendar"]],
				$languageId
			)
		;

		$ownerName = CCalendar::GetUserName($params['userId']);
		$fields['MESSAGE_OUT'] = fn (?string $languageId = null) =>
			Loc::getMessage(
				'EC_MESS_INVITE_CANCEL',
				[
					'#OWNER_NAME#' => $ownerName,
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
				],
				$languageId
			)
			. "\n\n"
			. Loc::getMessage(
				'EC_MESS_VIEW_OWN_CALENDAR_OUT',
				['#LINK#' => $params["pathToCalendar"]],
				$languageId
			)
		;

		$fields['TITLE'] = fn (?string $languageId = null) => Loc::getMessage(
			'EC_MESS_INVITE_CANCEL_TITLE',
			['#TITLE#' => $params["name"]],
			$languageId
		);
		return $fields;
	}

	public static function CancelInstance($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$params["from"]."|".$fields['TO_USER_ID']."|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = fn (?string $languageId = null) =>
			Loc::getMessage(
				'EC_MESS_REC_THIS_CANCEL_SITE',
				[
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
				],
				$languageId
			)
			. "\n\n"
			. Loc::getMessage(
				'EC_MESS_VIEW_OWN_CALENDAR',
				['#LINK#' => $params["pathToCalendar"]],
				$languageId
			)
		;

		$ownerName = CCalendar::GetUserName($params['userId']);
		$fields['MESSAGE_OUT'] = fn (?string $languageId = null) =>
			Loc::getMessage(
				'EC_MESS_REC_THIS_CANCEL',
				[
					'#OWNER_NAME#' => $ownerName,
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
				],
				$languageId
			)
			. "\n\n"
			. Loc::getMessage('EC_MESS_VIEW_OWN_CALENDAR_OUT', ['#LINK#' => $params["pathToCalendar"]])
		;

		$fields['TITLE'] = fn (?string $languageId = null) => Loc::getMessage(
			'EC_MESS_INVITE_CANCEL_TITLE',
			['#TITLE#' => $params["name"]],
			$languageId
		);
		return $fields;
	}

	public static function CancelAllReccurent($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID']."|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = fn (?string $languageId = null) =>
			Loc::getMessage(
				'EC_MESS_REC_ALL_CANCEL_SITE',
				[
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
				],
				$languageId
			)
			. "\n\n"
			. Loc::getMessage(
				'EC_MESS_VIEW_OWN_CALENDAR',
				['#LINK#' => $params["pathToCalendar"]],
				$languageId
			);

		$ownerName = CCalendar::GetUserName($params['userId']);
		$fields['MESSAGE_OUT'] = fn (?string $languageId = null) =>
			Loc::getMessage(
				'EC_MESS_REC_ALL_CANCEL',
				[
					'#OWNER_NAME#' => $ownerName,
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
				],
				$languageId
			)
			. "\n\n"
			. Loc::getMessage(
				'EC_MESS_VIEW_OWN_CALENDAR_OUT',
				['#LINK#' => $params["pathToCalendar"]],
				$languageId
			)
		;

		$fields['TITLE'] = fn (?string $languageId = null) => Loc::getMessage(
			'EC_MESS_INVITE_CANCEL_TITLE',
			['#TITLE#' => $params["name"]],
			$languageId
		);

		return $fields;
	}

	public static function CancelSharing($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "change";
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$fields['TO_USER_ID']."|sharing|cancel";
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = fn (?string $languageId = null) =>
			Loc::getMessage(
				'EC_MESS_INVITE_CANCEL_SHARING',
				[
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId),
				],
				$languageId
			)
			. "\n\n"
			. Loc::getMessage(
				'EC_MESS_INVITE_CANCEL_SHARING_SITE',
				['#LINK#' =>  $params["pathToEvent"]],
				$languageId
			)
		;

		$fields['MESSAGE_OUT'] = $fields['MESSAGE'];
		$fields['TITLE'] = fn (?string $languageId = null) => Loc::getMessage(
			'EC_MESS_INVITE_CANCEL_TITLE',
			['#TITLE#' => $params["name"]],
			$languageId
		);

		return $fields;
	}

	public static function MeetingStatus($fields = [], $params = [])
	{
		$fields['NOTIFY_EVENT'] = "info";
		$fields['FROM_USER_ID'] = (int)$params["guestId"];
		$fields['TO_USER_ID'] = (int)$params["userId"];
		$fields['NOTIFY_TAG'] = "CALENDAR|INVITE|".$params['eventId']."|".$params['mode'];
		$fields['NOTIFY_SUB_TAG'] = "CALENDAR|INVITE|".$params['eventId'];

		$fields['MESSAGE'] = fn (?string $languageId = null) => Loc::getMessage(
			$params['mode'] === 'accept'
				? 'EC_MESS_INVITE_ACCEPTED_SITE_1'
				: 'EC_MESS_INVITE_DECLINED_SITE_1',
			[
				'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
				'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
			],
			$languageId
		);
		$fields['NOTIFY_LINK'] = $params["pathToEvent"];

		$ownerName = CCalendar::GetUserName($params['guestId']);
		$fields['MESSAGE_OUT'] = fn (?string $languageId = null) =>
			Loc::getMessage(
				$params['mode'] ==='accept'
					? 'EC_MESS_INVITE_ACCEPTED_1'
					: 'EC_MESS_INVITE_DECLINED_1',
				[
					'#GUEST_NAME#' => $ownerName,
					'#TITLE#' => $params["name"],
					'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
				],
				$languageId
			)
			. "\n\n"
			. Loc::getMessage('EC_MESS_INVITE_DETAILS', ['#LINK#' => $params["pathToEvent"]], $languageId)
		;


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
			$fields['MESSAGE'] = fn (?string $languageId = null) =>
				Loc::getMessage(
					'EC_MESS_AUTO_INVITE_ACCEPT',
					[
						'#TITLE#' => $params["name"],
						'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
					],
					$languageId
				)
				. "\n\n"
				. Loc::getMessage('EC_MESS_AUTO_INVITE_ACCEPT_DETAILS', ['#LINK#' => $params["pathToEvent"]], $languageId);
		}
		else
		{
			$fields['MESSAGE'] = fn (?string $languageId = null) => Loc::getMessage(
				$params['mode'] === 'status_accept'
					? 'EC_MESS_STATUS_NOTIFY_Y_SITE'
					: 'EC_MESS_STATUS_NOTIFY_N_SITE',
				[
					'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
					'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
				],
				$languageId
			);

			$fields['NOTIFY_LINK'] = $params["pathToEvent"];
		}

		$fields['MESSAGE_OUT'] = fn (?string $languageId = null) =>
			Loc::getMessage(
				$params['mode'] === 'status_accept'
					? 'EC_MESS_STATUS_NOTIFY_Y'
					: 'EC_MESS_STATUS_NOTIFY_N',
				[
					'#TITLE#' => "[url=".$params["pathToEvent"]."]".$params["name"]."[/url]",
					'#ACTIVE_FROM#' => self::getFromFormatted($params, $languageId)
				],
				$languageId
			)
			. "\n\n"
			. Loc::getMessage('EC_MESS_INVITE_DETAILS', ['#LINK#' => $params["pathToEvent"]], $languageId)
		;


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

			$gender = null;
			$res = \Bitrix\Main\UserTable::getList([
				'filter' => ['=ID' => $userId],
				'select' => ['ID', 'PERSONAL_GENDER']
			]);

			if (($user = $res->fetch()) && in_array($user['PERSONAL_GENDER'], ['F', 'M']))
			{
				$gender = $user['PERSONAL_GENDER'];
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

						$imMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => self::getCommentAddNotifyMessage(
							$url ? "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".$event["NAME"]."</a>" : $event["NAME"],
							$commentCropped,
							$gender,
							$languageId
						);

						$imMessageFields['NOTIFY_MESSAGE_OUT'] = fn (?string $languageId = null) => self::getCommentAddNotifyMessage(
							$event["NAME"],
							$commentCropped,
							$gender,
							$languageId
						) . ($url ? ' (' . $url . ')' : '');

						$imMessageFields["NOTIFY_TAG"] = "CALENDAR|COMMENT|".$aId."|".$instanceDate;

						CIMNotify::Add($imMessageFields);
					}
				}
			}
		}
	}

	private static function getCommentAddNotifyMessage($eventTitle, $comment, $gender = null, $languageId = null): ?string
	{
		$phrase = $gender ? 'EC_COMMENT_MESSAGE_ADD_' . $gender : 'EC_COMMENT_MESSAGE_ADD';

		return Loc::getMessage(
			$phrase,
			[
				'#EVENT_TITLE#' => $eventTitle,
				'#COMMENT#' => $comment,
			],
			$languageId
		);
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
			$fields['MESSAGE'] = fn (?string $languageId = null) => Loc::getMessage(
				'EC_NOTIFY_FAIL_ICAL_CANCEL',
				[
					'#USERS_LIST#' => $userString,
					'#NAME#' => $params['name'],
				],
				$languageId
			);

			$fields['MESSAGE_OUT'] = fn (?string $languageId = null) => Loc::getMessage(
				'EC_NOTIFY_FAIL_ICAL_CANCEL_OUT',
				[
					'#USERS_LIST#' => $userString,
					'#NAME#' => $params['name'],
				],
				$languageId
			);

			$fields['TITLE'] = fn (?string $languageId = null) => Loc::getMessage(
				'EC_MESS_FAIL_ICAL_INVITE_TITLE_CANCEL',
				['#TITLE#' => $params['name']],
				$languageId
			);
		}
		elseif ($params['icalMethod'] === 'edit')
		{
			$fields['MESSAGE'] = fn (?string $languageId = null) => Loc::getMessage(
				'EC_NOTIFY_FAIL_ICAL_EDIT',
				[
					'#USERS_LIST#' => $userString,
					'#NAME#' => $params['name'],
				],
				$languageId
			);

			$fields['MESSAGE_OUT'] = fn (?string $languageId = null) => Loc::getMessage(
				'EC_NOTIFY_FAIL_ICAL_EDIT_OUT',
				[
					'#USERS_LIST#' => $userString,
					'#NAME#' => $params['name'],
				],
				$languageId);

			$fields['TITLE'] = fn (?string $languageId = null) => Loc::getMessage(
				'EC_MESS_FAIL_ICAL_INVITE_TITLE_EDIT',
				['#TITLE#' => $params['name']],
				$languageId
			);
		}
		elseif ($params['icalMethod'] === 'request')
		{
			$fields['MESSAGE'] = fn (?string $languageId = null) => Loc::getMessage(
				'EC_NOTIFY_FAIL_ICAL_REQUEST',
				[
					'#USERS_LIST#' => $userString,
					'#NAME#' => $params['name'],
				],
				$languageId
			);

			$fields['MESSAGE_OUT'] = fn (?string $languageId = null) => Loc::getMessage(
				'EC_NOTIFY_FAIL_ICAL_REQUEST_OUT',
				[
					'#USERS_LIST#' => $userString,
					'#NAME#' => $params['name'],
				],
				$languageId
			);

			$fields['TITLE'] = fn (?string $languageId = null) => Loc::getMessage(
				'EC_MESS_FAIL_ICAL_INVITE_TITLE_REQUEST',
				['#TITLE#' => $params['name']],
				$languageId
			);
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

		$fields['MESSAGE'] = fn (?string $languageId = null) => Loc::getMessage(
			'EC_NOTIFY_DELETE_LOCATION',
			['#LOCATION#' => $params["location"]],
			$languageId
		);

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

		$notificationCode = match ($params['recursionMode'])
		{
			'all' => 'EC_NOTIFY_CANCEL_BOOKING_ALL',
			'next' => 'EC_NOTIFY_CANCEL_BOOKING_NEXT',
			default => 'EC_NOTIFY_CANCEL_BOOKING_THIS',
		};

		$fields['MESSAGE'] = fn (?string $languageId = null) =>
			Loc::getMessage(
				$notificationCode,
				[
					'#FROM#' => self::getFromFormatted($params, $languageId),
					'#LINK#' => $params['pathToEvent'],
					'#EVENT#' => $params['eventName'],
					'#FREQUENCY#' => !empty($params['fields']['RRULE'])
						? CCalendarEvent::GetRRULEDescription($params['fields'], false, false, $languageId)
						: ''
					,
					'#FROM_TIME#' => $params['fields']['DT_SKIP_TIME'] === 'N'
						? Loc::getMessage(
							'EC_NOTIFY_CANCEL_BOOKING_TIME',
							[
								'#FROM_TIME#'=> mb_substr($params['from'], -5, 5)
							],
							$languageId
						)
						: ''
					,
				],
				$languageId
			)
			. Loc::getMessage('EC_NOTIFY_CANCEL_BOOKING_ENDING', null, $languageId)
		;

		return $fields;
	}

	private static function getFromFormatted($params, ?string $languageId = null): string
	{
		$culture = \Bitrix\Main\Context::getCurrent()?->getCulture();
		$result = FormatDate($culture?->getFullDateFormat(), $params['from_timestamp'], false);

		if (($params['fields']['DT_SKIP_TIME'] ?? null) !== 'Y')
		{
			$result .= ' ' . FormatDate($culture?->getShortTimeFormat(), $params['from_timestamp'], false);
		}

		return $result;
	}
}
?>