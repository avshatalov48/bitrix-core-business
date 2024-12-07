<?php

namespace Bitrix\Mail\Integration\Calendar\ICal;

use Bitrix\Calendar\ICal\MailInvitation\IncomingInvitationRequestHandler;
use Bitrix\Calendar\ICal\Parser\Calendar;
use Bitrix\Calendar\ICal\Parser\Dictionary;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Mail\Helper\Message;
use Bitrix\Mail\Integration\Intranet\Secretary;
use Bitrix\Mail\Internals\MailMessageAttachmentTable;
use Bitrix\Mail\Internals\MessageAccessTable;
use Bitrix\Mail\MailMessageTable;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;

class ICalMailEventManager
{
	public static function onMailMessageNew(Event $event)
	{
		$message = $event->getParameter('message');
		$attachments = $event->getParameter('attachments');
		$userId = $event->getParameter('userId');
		$icalAccess = $event->getParameter('icalAccess');

		if (ICalMailManager::hasICalAttachments($attachments))
		{
			Message::ensureAttachments($message);
			$files = static::getFiles($message);

			foreach ($files as $file)
			{
				$data = ICalMailManager::getFileContent($file['FILE_ID']);
				if (!is_string($data))
				{
					continue;
				}

				$icalComponent = ICalMailManager::parseRequest($data);

				if (!($icalComponent instanceof Calendar))
				{
					continue;
				}

				if (empty($message['OPTIONS']['iCal']))
				{
					$message['OPTIONS']['iCal'] = $data;

					MailMessageTable::update($message['ID'], [
						'OPTIONS' => $message['OPTIONS'],
					]);
				}

				if (!$icalComponent->hasOneEvent())
				{
					break;
				}

				if ($icalComponent->getMethod() === Dictionary::METHOD['reply'])
				{
					$result = ICalMailManager::handleReply($icalComponent);

					break;
				}
				elseif (
					($icalComponent->getMethod() === Dictionary::METHOD['request'])
					&& $icalAccess
				)
				{
					$result = ICalMailManager::handleRequest(
						$icalComponent,
						$userId,
						IncomingInvitationRequestHandler::MEETING_STATUS_QUESTION_CODE,
						$message
					);

					if ($result !== null)
					{
						Secretary::provideAccessToMessage(
							$message['ID'],
							Message::ENTITY_TYPE_CALENDAR_EVENT,
							$result,
							$userId
						);
					}

					break;
				}
				elseif ($icalComponent->getMethod() === Dictionary::METHOD['cancel'])
				{
					$result = ICalMailManager::handleCancel($icalComponent, $userId);

					break;
				}
			}
		}
	}

	public static function onUnbindEvent($calendarEventId, $entry): void
	{
		Loader::includeModule('calendar');
		$calendarEventId = (int)$calendarEventId;

		$userParams = [];

		if ($calendarEventId <= 0)
		{
			return;
		}

		$events = EventTable::getList([
			'select' => ['ID', 'OWNER_ID'],
			'filter' => [
				'PARENT_ID' => $calendarEventId,
				'!ID' => $calendarEventId,
			],
		])->fetchAll();

		$entityValues = array_map(static fn($event) => (int)$event['ID'], $events);

		$messageAccessCollection = MessageAccessTable::getList([
			'select' => ['MESSAGE_ID', 'ENTITY_ID'],
			'filter' => [
				'ENTITY_TYPE' => MessageAccessTable::ENTITY_TYPE_CALENDAR_EVENT,
				'ENTITY_ID' => $entityValues,
			],
		])->fetchAll();

		foreach ($events as $event)
		{
			$messageAccess = array_filter($messageAccessCollection, static fn($msgAccess) => (int)$msgAccess['ENTITY_ID'] === (int)$event['ID']);
			if ($messageAccess)
			{
				$messageId = (int)$messageAccess[0]['MESSAGE_ID'];
				$userParams[(int)$event['OWNER_ID']] = compact('messageId');
			}
		}

		MessageAccessTable::deleteByFilter([
			'ENTITY_TYPE' => MessageAccessTable::ENTITY_TYPE_CALENDAR_EVENT,
			'ENTITY_ID' => $entityValues,
		]);

		\Bitrix\Pull\Event::add(array_keys($userParams), [
				'module_id' => 'mail',
				'command' => 'unbindItem',
				'params' => [
					'type' => 'meeting',
				],
				'user_params' => $userParams,
			]
		);
	}

	private static function getFiles($message)
	{
		if ($message['ATTACHMENTS'] > 0)
		{
			return MailMessageAttachmentTable::getList([
				'select' => [
					'ID',
					'FILE_ID',
					'FILE_NAME',
					'FILE_SIZE',
					'CONTENT_TYPE',
				],
				'filter' => [
					'=MESSAGE_ID'   => $message['ID'],
					'@CONTENT_TYPE' => ICalMailManager::CONTENT_TYPES
				],
			])->fetchAll();
		}

		return [];
	}
}
