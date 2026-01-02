<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Message\Reaction;

use Bitrix\Im\Bot;
use Bitrix\Im\V2\Message;

class ReactionEvent
{
	public const ADD_REACTION = 'ADD';
	public const DELETE_REACTION = 'DELETE';

	private Message $message;
	private ReactionItem $reactionItem;
	private string $type;

	public function __construct(Message $message, ReactionItem $reactionItem, string $type)
	{
		$this->message = $message;
		$this->reactionItem = $reactionItem;
		$this->type = $type;
	}

	public function sendBotEvent(): void
	{
		Bot::onReactionChange(
			$this->message,
			array_merge($this->getReactionParams(), $this->getMessageParams(), $this->getChatParams())
		);
	}

	public function getReactionParams(): array
	{
		return [
			'REACTION_TYPE' => $this->type,
			'REACTION' => $this->reactionItem->getReaction(),
			'REACTION_AUTHOR_ID' => $this->reactionItem->getUserId(),
		];
	}

	public function getMessageParams(): array
	{
		return [
			'MESSAGE' => $this->message->getMessage(),
			'TEMPLATE_ID' => $this->message->getUuid(),
			'MESSAGE_TYPE' => $this->message->getChat()->getType(),
			'MESSAGE_OUT' => $this->message->getMessageOut() ?? '',
			'AUTHOR_ID' => $this->message->getAuthorId(),
		];
	}

	public function getChatParams(): array
	{
		$chat = $this->message->getChat();

		return [
			'CHAT_ID' => $chat->getId(),
			'CHAT_TITLE' => $chat->getTitle() ?? '',
			'CHAT_AUTHOR_ID' => $chat->getAuthorId(),
			'CHAT_TYPE' => $chat->getType(),
			'CHAT_ENTITY_TYPE' => $chat->getEntityType(),
			'CHAT_ENTITY_ID' => $chat->getEntityId(),
		];
	}
}
