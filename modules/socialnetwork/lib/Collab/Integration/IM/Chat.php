<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\IM;

use Bitrix\Im\V2;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;

Loader::includeModule('im');

class Chat
{
	/**
	 * @see V2\Chat::MANAGE_RIGHTS_NONE,
	 * @see V2\Chat::MANAGE_RIGHTS_MEMBER,
	 * @see V2\Chat::MANAGE_RIGHTS_OWNER,
	 * @see V2\Chat::MANAGE_RIGHTS_MANAGERS,
	 */
	public const ALLOWED_RIGHTS = [
		'NONE',
		'MEMBER',
		'OWNER',
		'MANAGER',
	];

	public static function getCollabIdByDialog(string $dialogId): int
	{
		if (!Loader::includeModule('im'))
		{
			return 0;
		}

		$chatId = Dialog::getChatId($dialogId);
		if ($chatId <= 0)
		{
			return 0;
		}

		return (int)V2\Chat::getInstance($chatId)->getEntityId();
	}

	public static function getDialogIdByCollabId(int $collabId): string
	{
		if (!Loader::includeModule('im'))
		{
			return '';
		}

		$collab = CollabRegistry::getInstance()->get($collabId);
		if ($collab === null)
		{
			return '';
		}

		return $collab->getDialogId();
	}

	public static function deleteByCollabId(int $collabId): Result
	{
		$result = new Result();

		$collab = CollabRegistry::getInstance()->get($collabId);
		if ($collab === null)
		{
			$result->addError(new Error('Collab not found'));

			return $result;
		}

		return static::deleteByChatId($collab->getChatId());
	}

	public static function deleteByChatId(int $chatId): Result
	{
		$result = new Result();

		$deleteResult = V2\Chat::getInstance($chatId)->deleteChat();

		$result->addErrors($deleteResult->getErrors());

		return $result;
	}
}