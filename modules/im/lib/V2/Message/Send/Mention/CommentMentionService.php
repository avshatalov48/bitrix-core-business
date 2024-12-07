<?php

namespace Bitrix\Im\V2\Message\Send\Mention;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Send\MentionService;

class CommentMentionService extends MentionService
{
	protected function getChat(Message $message): Chat
	{
		$chat = $message->getChat();

		if ($chat instanceof Chat\CommentChat)
		{
			$chat = $chat->getParentChat();
		}

		return $chat;
	}

	protected function needToSendPull(): bool
	{
		return false;
	}

	protected function getNotifyTextCode(string $userGender): string
	{
		return "IM_MESSAGE_MENTION_COMMENT_{$userGender}";
	}

	protected function getTitleWithContext(string $title, Message $message): string
	{
		$chatId = $message->getChat()->getParentChatId();
		$messageId = $message->getChat()->getParentMessageId();

		return "[CONTEXT=chat{$chatId}/{$messageId}]{$title}[/CONTEXT]";
	}
}