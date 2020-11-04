<?php


namespace Bitrix\Mail\Integration\Calendar\ICal;

use Bitrix\Calendar\ICal\IncomingEventManager;
use Bitrix\Main\Loader;

Loader::includeModule('calendar');

class ICalMailManager
{
	const CONTENT_TYPES = ['application/ics', 'text/calendar'];

	public static function manageRequest($params)
	{
		IncomingEventManager::handleRequest($params);
	}

	public static function manageReply($data): array
	{
		IncomingEventManager::handleReply($data);
		return [];
	}

	public static function parseRequest($data): array
	{
		return IncomingEventManager::getDataInfo($data);
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
			return file_get_contents($fileArray['tmp_name']);
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
