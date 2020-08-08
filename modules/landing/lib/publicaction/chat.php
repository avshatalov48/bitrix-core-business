<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\PublicActionResult;
use \Bitrix\Landing\Chat\Chat as ChatCore;

class Chat
{
	/**
	 * Returns chat list.
	 * @param array $params Additional params.
	 * @return PublicActionResult
	 */
	public static function getList(array $params = [])
	{
		static $internal = true;

		$result = new PublicActionResult();
		$params = $result->sanitizeKeys($params);

		$items = [];
		foreach (ChatCore::getList($params) as $item)
		{
			$items[] = $item;
		}
		$result->setResult($items);

		return $result;
	}

	/**
	 * Invite current user to the chat and returns IM chat id.
	 * @param int $internalId Internal chat id.
	 * @return PublicActionResult
	 */
	public static function joinChat($internalId)
	{
		static $internal = true;

		$result = new PublicActionResult();
		$result->setResult(
			ChatCore::joinChat(intval($internalId))
		);

		return $result;
	}
}