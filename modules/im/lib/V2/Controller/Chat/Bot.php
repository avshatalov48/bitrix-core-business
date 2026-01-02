<?php

namespace Bitrix\Im\V2\Controller\Chat;

use Bitrix\Im\V2\Bot\BotError;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Controller\BaseController;

class Bot extends BaseController
{
	/**
	 * @restMethod im.v2.Chat.Bot.sendContext
	 */
	public function sendContextAction(Chat $chat, array $context): ?array
	{
		$params = $this->getAdditionalChatParams($chat);
		$params['CONTEXT'] = $context;

		$result = \Bitrix\Im\Bot::onContextGet($chat, $params);
		if (!$result)
		{
			$this->addError(new BotError(BotError::BOT_CONTEXT_ERROR));

			return null;
		}

		return ['result' => true];
	}

	protected function getAdditionalChatParams(Chat $chat): array
	{
		return [
			'CHAT_TYPE' => $chat->getType(),
			'MESSAGE_TYPE' => $chat->getType(),
			'USER_ID' => $chat->getContext()->getUserId(),
			'CHAT_AUTHOR_ID' => $chat->getAuthorId(),
			'CHAT_ENTITY_TYPE' => $chat->getEntityType(),
			'CHAT_ENTITY_ID' => $chat->getEntityId(),
		];
	}
}
