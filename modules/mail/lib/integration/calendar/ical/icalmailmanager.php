<?php


namespace Bitrix\Mail\Integration\Calendar\ICal;

use Bitrix\Calendar\ICal\IncomingEventManager;
use Bitrix\Calendar\ICal\MailInvitation\InboxManager;
use Bitrix\Calendar\ICal\MailInvitation\IncomingInvitationReplyHandler;
use Bitrix\Calendar\ICal\Parser\Calendar;
use Bitrix\Main\Loader;

Loader::includeModule('calendar');

class ICalMailManager
{
	public const CONTENT_TYPES = ['application/ics', 'text/calendar'];

	public static function manageRequest($params): void
	{
		IncomingEventManager::handleRequest($params);
	}

	public static function handleReply(Calendar $icalComponent): bool
	{
		return IncomingInvitationReplyHandler::fromComponent($icalComponent)
			->handle()
			->isSuccess();
	}

	/**
	 * @param string $content
	 * @return Calendar
	 */
	public static function parseRequest(string $content): ?Calendar
	{
		return InboxManager::createInstance($content)
			->parseContent()
			->getComponent();
	}

	public static function parseFile($fileId)
	{
		$data = ICalMailManager::getFileContent($fileId);

		if (!empty($data))
		{
			return ICalMailManager::parseRequest($data);
		}

		return null;
	}

	public static function getFileContent($fileId)
	{
		$fileArray = \CFile::makeFileArray($fileId);
		if (!empty($fileArray))
		{
			return \Bitrix\Main\IO\File::getFileContents($fileArray['tmp_name']);
		}

		return null;
	}

	public static function hasICalAttachments(array $attachments)
	{
		foreach ($attachments as $item)
		{
			if (in_array($item['CONTENT-TYPE'], ICalMailManager::CONTENT_TYPES))
			{
				return true;
			}
		}

		return false;
	}
}
