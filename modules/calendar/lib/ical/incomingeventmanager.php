<?php


namespace Bitrix\Calendar\ICal;


use Bitrix\Mail\MailMessageTable;
use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding;
use Bitrix\Tasks\Integration\Disk;
use Bitrix\Calendar\ICal\Basic\{Dictionary, ICalUtil};
use Bitrix\Main\Type\Date;


class IncomingEventManager
{
	public static function handleRequest($params)
	{
		$event = $params['event'];
		$userId = $params['userId'];
		$answer = $params['answer'];
		$userEmail = $params['emailTo'];
		$organizerEmail = $event['ORGANIZER_MAIL']['EMAIL'];

		$event = static::updateEvent($event, $userId, $answer);
		$attendees = static::handleAttendeesFromRequest([
			'attendees' => $event['ATTENDEES_MAIL'],
			'answer' => $answer,
			'userId' => $userId,
			'userEmail' => $userEmail,
			'organizerEmail' => $organizerEmail
		]);

		$parentId = \CCalendar::SaveEvent([
			'arFields' => static::updateParentEvent($event),
		]);

		\CCalendar::SaveEvent([
			'arFields' => static::updateChildEvent($event, $parentId),
		]);


		$replyStatus = OutcomingEventManager::getInstance([
			'icalMethod' => 'reply',
			'arFields' => $event,
			'userIndex' => $attendees,
			'receiver' => $event['ORGANIZER_MAIL'],
			'sender' => $attendees[$event['OWNER_ID']],
		])->replyInvitation()->getStatus();
	}

	public static function handleReply(array $params): bool
	{
		$uid = $params['event']['DAV_XML_ID'];
		$emailUser = $params['event']['ATTENDEES_MAIL'][0];
		$userId = ICalUtil::getUserIdByEmail($emailUser);
		$localEvent = ICalUtil::getEventByUId($userId, $uid);

		if (!empty($localEvent))
		{
			$status = $params['event']['ATTENDEES_MAIL'][0]['STATUS'];

			\CCalendarEvent::SetMeetingStatusEx([
				'attendeeId' => $localEvent['OWNER_ID'],
				'eventId' => $localEvent['ID'],
				'status' => Dictionary::LOCAL_ATTENDEES_STATUS[$status],
				'personalNotification' => $localEvent['MEETING_HOST'],
			]);
		}

		return true;
	}

	public static function handleReplyReceivedICalInvent(\Bitrix\Main\Event $event)
	{
		$attachments = $event->getParameter('attachments');

		if (is_array($attachments))
		{
			foreach($attachments as $key => $file)
			{
				if ($file['type'] === OutcomingEventManager::CONTENT_TYPE)
				{
					$fileObject = new \Bitrix\Main\IO\File($file['tmp_name'], $event->getParameter('site_id'));
					try
					{
						$fileContent = $fileObject->getContents();
					}
					catch (FileNotFoundException $e)
					{
						die();
					}
					$fileContent = Encoding::convertEncoding($fileContent, OutcomingEventManager::CHARSET, SITE_CHARSET);
					$icalComponent = static::getDataInfo($fileContent);

					if ($method === Dictionary::METHODS['reply'])
					{
						static::handleReply(['event' => $icalEvent]);
					}
				}
			}
		}
	}

	public static function handleCancel($params)
	{
		$event = $params['event'];
		$userId = $params['userId'];

		$originalValue = ICalUtil::getEventByUId($userId, $event['DAV_XML_ID']);
		if (!empty($originalValue))
		{
			$deleteParams = [
				'sendNotification' => true,
				'checkPermissions' => false,
			];

			\CCalendar::DeleteEvent($originalValue['ID'], true, $deleteParams);
		}
	}

	public static function getDataInfo($data, $params = []): array
	{

		$attachmentManager = IncomingAttachmentManager::getInstance([
			'data' => $data,
		]);
		$event = $attachmentManager->prepareEventAttachment()->getEvent();
		$method = $attachmentManager->getMethod();

		return [$event, $method];
	}

	public static function rehandleRequest($params)
	{
		$params['event']['SKIP_TIME'] = $params['event']['DT_SKIP_TIME'] === 'Y';

		$attendees = static::handleAttendeesByUser(
			ICalUtil::getUsersByCode($params['event']['ATTENDEES_CODES']),
			$params['answer'],
			$params['userId']
		);

		$params['event']['ORGANIZER_MAIL'] = $attendees[$params['event']['MEETING_HOST']];
		$params['event']['ORGANIZER_MAIL']['MAILTO'] = $params['event']['MEETING']['MAILTO'];
		$params['event']['ATTENDEES_MAIL'] = $attendees[$params['event']['OWNER_ID']];

		$replyStatus = OutcomingEventManager::getInstance([
			'icalMethod' => 'reply',
			'arFields' => $params['event'],
			'userIndex' => $attendees,
			'receiver' => $params['event']['ORGANIZER_MAIL'],
			'sender' => $attendees[$params['event']['OWNER_ID']],
		])->replyInvitation()->getStatus();
	}

	private static function updateEvent(array $event, $userId, $answer): array
	{
		if (isset($event['DATE_FROM']))
		{
			$event['DATE_FROM_MAIL'] = $event['SKIP_TIME']
				? static::getDateString($event['DATE_FROM'])
				: static::getDateTimeString($event['DATE_FROM']);
			$event['DATE_FROM'] = static::getDateTimeString($event['DATE_FROM']);
		}

		if (isset($event['DATE_TO']))
		{
			$event['DATE_TO'] = static::getDateTimeString($event['DATE_TO']);
		}

		if (isset($event['DATE_CREATE']))
		{
			$event['DATE_CREATE'] = static::getDateTimeString($event['DATE_CREATE']);
		}

		if (isset($event['TIMESTAMP_X']))
		{
			$event['TIMESTAMP_X'] = static::getDateTimeString($event['TIMESTAMP_X']);
		}

		if (isset($event['DT_STAMP']))
		{
			$event['DT_STAMP'] = static::getDateTimeString($event['DT_STAMP']);
		}

		if (isset($event['TZ_FROM']))
		{
			$event['TZ_FROM'] = static::getTimezoneNameString($event['TZ_FROM']);
		}

		if (isset($event['TZ_TO']))
		{
			$event['TZ_TO'] = static::getTimezoneNameString($event['TZ_TO']);
		}

		$event['OWNER_ID'] = $userId;
		$event['MEETING_HOST'] = ICalUtil::getUserIdByEmail($event['ORGANIZER_MAIL']);
		$event['IS_MEETING'] = 1;
		$event['SECTION_CAL_TYPE'] = 'user';
		$event['ATTENDEES_CODES'] = ['U'.$event['OWNER_ID'], 'U'.$event['MEETING_HOST']];
		$event['MEETING_STATUS'] = $answer === 'confirmed' ? 'Y' : 'N';

		if (empty($event['ACCESSIBILITY']))
		{
			$event['ACCESSIBILITY'] = 'free';
		}

		if (empty($event['IMPORTANCE']))
		{
			$event['IMPORTANCE'] = 'normal';
		}

		if (empty($event['STATUS']))
		{
			$event['STATUS'] = $answer === 'ACCEPT' ? 'CONFIRMED' : 'CANCELLED';
		}

		if (empty($event['REMIND']))
		{
			$event['REMIND'] = [
				'type' => 'min',
				'count' => '15'
			];
		}

		if (empty($event['MEETING']))
		{
			$event['MEETING'] = [
				'HOST_NAME' => $event['ORGANIZER_MAIL']['name'],
				'NOTIFY' => 1,
				'REINVITE' => 0,
				'ALLOW_INVITE' => 0,
				'MEETING_CREATOR' => $event['MEETING_HOST'],
				'EXTERNAL_TYPE' => 'mail',
			];
		}

		$originalValue = ICalUtil::getEventByUId($userId, $event['DAV_XML_ID']);
		$event['ID'] = $originalValue ? $originalValue['ID'] : 0;

		return $event;
	}

	private static function getDateTimeString(Date $date): string
	{
		return $date->format(Date::convertFormatToPhp(FORMAT_DATETIME));
	}

	private static function getDateString(Date $date): string
	{
		return $date->format('d.m.Y');
	}

	private static function getTimezoneNameString(\DateTimeZone $tz)
	{
		return $tz->getName();
	}

	private static function handleAttendeesFromRequest(array $params): array
	{
		$result = [];

		foreach ($params['attendees'] as $attendee)
		{
			if ($attendee['EMAIL'] == $params['userEmail'])
			{
				$usersInfo = ICalUtil::getIndexUsersById([$params['userId']]);
				$attendee['NAME'] = $usersInfo[$params['userId']]['NAME'];
				$attendee['LAST_NAME'] = $usersInfo[$params['userId']]['LAST_NAME'];
				$attendee['STATUS'] = $params['answer'] === 'confirmed' ? 'accepted' : 'declined';
				$result[$params['userId']] = $attendee;
			}

			if ($attendee['EMAIL'] == $params['organizerEmail'])
			{
				$id = ICalUtil::getUserIdByEmail($attendee);
				$result[$id] = $attendee;
			}
		}

		return $result;
	}

	private static function handleAttendeesByUser(array $attendees, $answer, $userId)
	{
		$result = [];
		foreach ($attendees as $attendee)
		{
			if ($attendee['ID'] == $userId)
			{
				$attendee['STATUS'] = $answer ? 'accepted' : 'declined';
			}

			$result[$attendee['ID']] = $attendee;
		}

		return $result;
	}

	private static function updateChildEvent(array $event, $parentId)
	{
		$event['PARENT_ID'] = $parentId;
		$event['MEETING']['MAILTO'] = $event['ORGANIZER_MAIL']['MAILTO'];
		$event['DESCRIPTION'] .= "\r\n"
			. Loc::getMessage('EC_ORGANIZER_NAME_TITLE') . ': ' . static::getOrganizerString($event['ORGANIZER_MAIL']) . "\r\n"
			. Loc::getMessage('EC_ATTENDEES_LIST_TITLE') . ': ' . static::getAttendeesString($event['ATTENDEES_MAIL']);
		if (!empty($event['ATTACHMENT_LINK']))
		{
			$event['DESCRIPTION'] .= "\r\n"
				. Loc::getMessage('EC_FILES_TITLE') . ': ' . static::getFilesString($event['ATTACHMENT_LINK']);
		}
		return $event;
	}

	private static function getAttendeesString($attendees)
	{
		$res = [];
		foreach ($attendees as $attendee)
		{
			$res[] = $attendee['NAME'] . ' ' . $attendee['LAST_NAME'] . ' ('. $attendee['EMAIL'] .')';
		}

		return implode(', ', $res);
	}

	private static function getOrganizerString($organizer)
	{
		return $organizer['NAME'] . ' ' . $organizer['LAST_NAME'] . ' ('. $organizer['EMAIL'] .')';
	}

	private static function updateParentEvent(array $event)
	{
		$event['OWNER_ID'] = $event['MEETING_HOST'];
		unset($event['DAV_XML_ID']);

		return $event;
	}

	private static function getFilesString(array $attachments)
	{
		$res = [];
		foreach ($attachments as $attachment)
		{
			$res[] = $attachment['filename'] . ' (' . $attachment['link'] . ')';
		}
		return implode(', ', $res);
	}
}