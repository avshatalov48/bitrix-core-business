<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Rest\PopupDataItem;

class ChatPopupItem implements PopupDataItem
{
	use ContextCustomer;

	/**
	 * @var Chat[]
	 */
	protected array $chats = [];
	protected ?array $chatIds = null;

	/**
	 * @var Chat[] $chats
	 */
	public function __construct(array $chats)
	{
		foreach ($chats as $chat)
		{
			$this->chats[$chat->getChatId()] = $chat;
		}
	}

	public function merge(PopupDataItem $item): PopupDataItem
	{
		return $this;
	}

	public static function getRestEntityName(): string
	{
		return 'chats';
	}

	public function toRestFormat(array $option = []): array
	{
		$rest = [];

		Chat::fillSelfRelations($this->chats);
		$this->fillDialogIds();

		foreach ($this->chats as $chat)
		{
			$rest[] = $chat->toRestFormat(['CHAT_SHORT_FORMAT' => true]);
		}

		return $rest;
	}

	protected function fillDialogIds(): void
	{
		$privateChatIds = [];
		foreach ($this->chats as $chat)
		{
			if ($chat instanceof PrivateChat && !$chat->hasDialogId())
			{
				$privateChatIds[] = $chat->getChatId();
			}
		}

		$dialogIds = PrivateChat::getDialogIds($privateChatIds, $this->getContext()->getUserId());

		foreach ($dialogIds as $chatId => $dialogId)
		{
			$this->chats[$chatId]?->setDialogId($dialogId);
		}
	}

	protected function getChatIds(): array
	{
		if ($this->chatIds === null)
		{
			foreach ($this->chats as $chat)
			{
				$this->chatIds[] = $chat->getChatId();
			}
		}

		return $this->chatIds ?? [];
	}
}
