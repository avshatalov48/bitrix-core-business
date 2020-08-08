<?php
namespace Bitrix\Calendar\ICal;

use Bitrix\Calendar\Util;
use Bitrix\Main\Localization\Loc;

class UserController
{
	private static
		$MAIL_TYPE_EXTERNAL = "CALENDAR_INVITATION_EXTERNAL";


	public static function inviteUser($userId, $params)
	{
		$eventFields = $params['arFields'];
		$invitedUser = $params['userIndex'][$userId];
		$email = $invitedUser['EMAIL'];

		$nameFormatted = str_replace(['<', '>', '"'], '', \CCalendar::GetUserName($invitedUser));
		$mailMessageId = "<CALENDAR_INVITE_".$eventFields["PARENT_ID"]."@".$GLOBALS["SERVER_NAME"].">";
		$mailMessageInReplyTo = "<CALENDAR_INVITE_".$eventFields["PARENT_ID"]."@".$GLOBALS["SERVER_NAME"].">";
		$siteId = SITE_ID;
		$fromName = Loc::getMessage('CALENDAR_SERVICE_NAME');
		$responseEmailAddress = "CALENDAR_INVITE_".$eventFields["PARENT_ID"]."@".$GLOBALS["SERVER_NAME"];

		$res = \Bitrix\Mail\User::getReplyTo(
			$siteId,
			$userId,
			'CALENDAR_EVENT',
			$eventFields["PARENT_ID"],
			self::getPublicUrl()
		);

		if (is_array($res))
		{
			list($replyTo, $backUrl) = $res;

			if (
				$replyTo
				&& $backUrl
			)
			{
				$icsAttachment = self::getIcsFileAttachment($eventFields,
				[
					'attendees' => self::prepareAttendeesData(
						$eventFields['ATTENDEES'],
						$params['currentAttendees'],
						$params['userIndex'],
						$params['userId']
					),
					'responseEmailAddress' => $responseEmailAddress
				]);
				$attachments = [$icsAttachment];
				// 1. create ical-content
				// 2. save file ($APPLICATION->SaveFileContent($abs_path, $filesrc_for_save))
				// 3. add b_file entry
				// 4. clear file after sending

				$fromName = str_replace(['<', '>', '"'], '', $fromName);
				$id = \CEvent::Send(
					self::$MAIL_TYPE_EXTERNAL,
					$siteId,
					[
						"=Reply-To" => $fromName.' <'.$replyTo.'>',
						"=Message-Id" => $mailMessageId,
						"=In-Reply-To" => $mailMessageInReplyTo == $mailMessageId ? '' : $mailMessageInReplyTo,
						"EMAIL_FROM" => $fromName.' <'.\Bitrix\Mail\User::getDefaultEmailFrom().'>',
						"EMAIL_TO" => (!empty($nameFormatted) ? ''.$nameFormatted.' <'.$email.'>' : $email),
						"RECIPIENT_ID" => $userId,
						"COMMENT_ID" => '',
						"POST_ID" => intval($eventFields["PARENT_ID"]),
						"POST_TITLE" => 'calendar invitation',
						"URL" => self::getPublicUrl()
					],
					'Y',
					'',
					$attachments
				);
			}
		}
	}

	private static function prepareAttendeesData($attendeeIdList, $currentAttendees, $userIndex, $currentUserId)
	{
		$attendeesList = [];
		$attendeeIndex = [];
		if (is_array($currentAttendees))
		{
			foreach($currentAttendees as $user)
			{
				$attendeeIndex[$user['USER_ID']] = $user;
			}
		}

		if (is_array($attendeeIdList))
		{
			foreach($attendeeIdList as $userId)
			{
				if (isset($userIndex[$userId]))
				{
					if ($attendeeIndex[$userId])
					{
						$attendeesList[] = [
							'id' => $userIndex[$userId]['ID'],
							'email' => $userIndex[$userId]['EMAIL'],
							'external_auth_id' => $userIndex[$userId]['EXTERNAL_AUTH_ID'],
							'name' => $attendeeIndex[$userId]['DISPLAY_NAME'],
							'status' => $attendeeIndex[$userId]['STATUS']
						];
					}
					else
					{
						$attendeesList[] = [
							'id' => $userIndex[$userId]['ID'],
							'email' => $userIndex[$userId]['EMAIL'],
							'external_auth_id' => $userIndex[$userId]['EXTERNAL_AUTH_ID'],
							'name' => \CCalendar::getUserName($userIndex[$userId]),
							'status' => $currentUserId === $userId ? 'H' : 'Q'
						];
					}
				}
			}
		}
		//eventFields['ATTENDEES'], $params['currentAttendees'], $params['userIndex']

		return $attendeesList;
	}

	private static function getPublicUrl()
	{
		return '/pub/event.php';
	}

	/**
	 * Get ical file
	 *
	 * @return int|bool File id.
	 */
	private static function getIcsFileAttachment($eventFields, $params = [])
	{
		$fileName = 'invite.ics';
		$fileData = array(
			'name' => $fileName,
			'type' => 'text/calendar',
			'content' => self::getIcsFileContent($eventFields, $params),
			'MODULE_ID' => 'calendar'
		);
		$fileId = \CFile::SaveFile($fileData, 'calendar');
		$fileArray = \CFile::GetFileArray($fileId);
		if (!is_array($fileArray))
		{
			return false;
		}

		//$storageTypeId = StorageType::getDefaultTypeID();
		//return StorageManager::saveEmailAttachment($fileArray, $storageTypeId, $siteId);
		return $fileId;
	}

	public static function getIcsFileContent($eventFields, $params = [])
	{
		$ics = new \Bitrix\Calendar\ICal\IcsBuilder(
			[
				'summary' => $eventFields['NAME'],
				'description' => '',
				'dtstart' => Util::getTimestamp($eventFields['DATE_FROM']),
				'dtend' => Util::getTimestamp($eventFields['DATE_TO']),
				'location' => \CCalendar::getTextLocation($eventFields['LOCATION']['NEW']),
				'uid' => isset($eventFields['DAV_XML_ID']) ? $eventFields['DAV_XML_ID'] : uniqid()
			]);

		if ($eventFields['SKIP_TIME'] !== 'Y')
		{
			$ics->setFullDayMode($eventFields['SKIP_TIME'] == 'Y');
			$ics->setConfig(
				[
					'timezoneFrom' => $eventFields['TZ_FROM'],
					'timezoneTo' => !empty($eventFields['TZ_TO']) ? $eventFields['TZ_TO'] : $eventFields['TZ_FROM']
				]
			);
		}

		$ics->setOrganizer($eventFields['MEETING']['HOST_NAME'], $params['responseEmailAddress']);
		$ics->setAttendees($params['attendees']);
		return $ics->render();
	}
}