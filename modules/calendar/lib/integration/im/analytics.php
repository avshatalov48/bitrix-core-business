<?php

namespace Bitrix\Calendar\Integration\Im;

use Bitrix\Calendar\Sharing;
use Bitrix\Im;
use Bitrix\Main;

class Analytics
{
	public static function onCallCreate(Main\Event $moduleEvent): void
	{
		Main\Application::getInstance()->addBackgroundJob([self::class, 'sendAnalyticsCallStarted'], [$moduleEvent]);
	}

	public static function sendAnalyticsCallStarted(Main\Event $moduleEvent): void
	{
		if (!Main\Loader::includeModule('im'))
		{
			return;
		}

		$chatId = (int)($moduleEvent->getParameters()['chatId'] ?? null);
		if ($chatId <= 0)
		{
			return;
		}

		$conferenceId = Im\V2\Chat::getInstance($chatId)->getAliasName();
		if (!is_string($conferenceId))
		{
			return;
		}

		$parentLink = (new Sharing\Link\Factory())->getParentLinkByConferenceId($conferenceId);
		if (is_null($parentLink))
		{
			return;
		}

		Sharing\Analytics::getInstance()->sendCallStarted($parentLink);
	}
}