<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\IM;

use Bitrix\Main\Loader;

class Dialog
{
	public static function getDialogId(int $chatId): string
	{
		if (!Loader::includeModule('im'))
		{
			return '';
		}

		if ($chatId <= 0)
		{
			return '';
		}

		return \Bitrix\Im\V2\Chat::getInstance($chatId)->getDialogId();
	}

	public static function getChatId(string $dialogId): int
	{
		if (!Loader::includeModule('im'))
		{
			return 0;
		}

		return (int)\Bitrix\Im\Dialog::getChatId($dialogId);
	}
}