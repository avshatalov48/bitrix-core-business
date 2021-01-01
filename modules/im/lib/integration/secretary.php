<?php
namespace Bitrix\Im\Integration;

use Bitrix\Im\Alias;
use Bitrix\Im\Call\Conference;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

class Secretary
{
	/**
	 * @param array $fields
	 * 'USERS' - (array) users to add to chat
	 * 'TITLE' - (string) chat name
	 * 'MESSAGE' - (string) welcome message
	 *
	 * @return Result
	 */
	public static function createChat(array $fields = []): Result
	{
		$result = new Result();

		$chat = new \CIMChat(0);
		$chatId = $chat->Add(
			[
				'USERS' => $fields['USERS'] ?? false,
				'TITLE' => $fields['TITLE'] ?? '',
				'MESSAGE' => $fields['MESSAGE'] ?? false
			]
		);

		if (!$chatId)
		{
			return $result->addError(new Error(Loc::getMessage('IM_INT_SECRETARY_CHAT_CREATION_ERROR')));
		}

		$result->setData(['CHAT_ID' => $chatId]);

		return $result;
	}

	public static function createCall(array $users = [], string $title = '')
	{
		//todo
	}

	/**
	 * @param array $fields
	 * 'USERS' - (array) users to add to chat
	 * 'TITLE' - (string) chat name
	 *
	 * @return Result
	 */
	public static function createConference(array $fields = []): Result
	{
		$result = new Result();

		$aliasData = Alias::addUnique(
			[
				"ENTITY_TYPE" => Alias::ENTITY_TYPE_VIDEOCONF,
				"ENTITY_ID" => 0
			]
		);

		$creationResult = Conference::add(
			[
				'USERS' => $fields['USERS'] ?? [],
				'TITLE' => $fields['TITLE'] ?? '',
				'ALIAS_DATA' => $aliasData
			]
		);

		if (!$creationResult->isSuccess())
		{
			return $result->addErrors($creationResult->getErrors());
		}

		$result->setData(['ALIAS_DATA' => $aliasData]);

		return $result;
	}
}