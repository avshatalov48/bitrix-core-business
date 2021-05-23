<?php

namespace Bitrix\Mail\Integration\Calendar\ICal;

use Bitrix\Calendar\ICal\MailInvitation\IncomingInvitationReplyHandler;
use Bitrix\Calendar\ICal\Parser\Calendar;
use Bitrix\Calendar\ICal\Parser\Dictionary;
use Bitrix\Mail\Helper\Message;
use Bitrix\Mail\Internals\MailMessageAttachmentTable;
use Bitrix\Mail\MailMessageTable;
use Bitrix\Main\Event;

class ICalMailEventManager
{
	public static function onMailMessageNew(Event $event)
	{
		$message = $event->getParameter('message');
		$attachments = $event->getParameter('attachments');
		$userId = $event->getParameter('userId');

		if (ICalMailManager::hasICalAttachments($attachments))
		{
			Message::ensureAttachments($message);
			$files = static::getFiles($message);

			foreach ($files as $file)
			{
				$data = ICalMailManager::getFileContent($file['FILE_ID']);
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

				if ($icalComponent->getMethod() === Dictionary::METHOD['reply']
					&& $icalComponent->hasOneEvent()
				)
				{
					ICalMailManager::handleReply($icalComponent);

					break;
				}
			}
		}
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
