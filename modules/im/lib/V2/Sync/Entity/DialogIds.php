<?php

namespace Bitrix\Im\V2\Sync\Entity;

use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Sync\Entity;

class DialogIds implements Entity
{
	use ContextCustomer;

	private Chats $chats;
	private array $dialogIds = [];
	private array $chatsWithoutDialogIds = [];

	public function __construct(Chats $chats)
	{
		$this->chats = $chats;
	}

	public function load(array $restData): self
	{
		$this
			->fillChatIds($restData)
			->loadFromFetchedChats($restData['chats'] ?? [])
			->loadFromNonPrivateChats()
			->loadFromPrivateChat()
		;

		return $this;
	}

	private function fillChatIds($restData): self
	{
		foreach ($restData['messages'] as $message)
		{
			$this->chatsWithoutDialogIds[(int)$message['chat_id']] = (int)$message['chat_id'];
		}

		foreach ($restData['pinSync']['addedPins'] as $pin)
		{
			$this->chatsWithoutDialogIds[(int)$pin['chatId']] = (int)$pin['chatId'];
		}

		foreach ($restData['chats'] as $chat)
		{
			$this->chatsWithoutDialogIds[(int)$chat['id']] = (int)$chat['id'];
		}

		foreach ($this->chats->getShortInfoChatIds() as $chatId)
		{
			$this->chatsWithoutDialogIds[$chatId] = $chatId;
		}

		return $this;
	}

	private function loadFromFetchedChats(array $chats): self
	{
		if (!$this->needContinue())
		{
			return $this;
		}

		foreach ($chats as $chat)
		{
			$this->add($chat['id'], $chat['dialogId']);
		}

		return $this;
	}

	private function loadFromNonPrivateChats(): self
	{
		if (!$this->needContinue())
		{
			return $this;
		}

		foreach ($this->chatsWithoutDialogIds as $chatId)
		{
			$chat = Chat::getInstance($chatId);
			if ($chat instanceof Chat\NullChat)
			{
				unset($this->chatsWithoutDialogIds[$chatId]);
				continue;
			}
			if ($chat->getType() !== Chat::IM_TYPE_PRIVATE)
			{
				$this->add($chat->getId(), $chat->getDialogId());
			}
		}

		return $this;
	}

	private function loadFromPrivateChat(): self
	{
		if (!$this->needContinue())
		{
			return $this;
		}

		$result = RelationTable::query()
			->setSelect(['CHAT_ID', 'USER_ID'])
			->whereIn('CHAT_ID', $this->chatsWithoutDialogIds)
			->where('MESSAGE_TYPE', Chat::IM_TYPE_PRIVATE)
			->whereNot('USER_ID', $this->getContext()->getUserId())
			->fetchAll()
		;

		foreach ($result as $row)
		{
			$this->add((int)$row['CHAT_ID'], $row['USER_ID']);
		}

		return $this;
	}

	private function needContinue(): bool
	{
		return !empty($this->chatsWithoutDialogIds);
	}

	private function add(int $chatId, string $dialogId): void
	{
		if (isset($this->dialogIds[$chatId]))
		{
			return;
		}

		$this->dialogIds[$chatId] = $dialogId;
		unset($this->chatsWithoutDialogIds[$chatId]);
	}

	public static function getRestEntityName(): string
	{
		return 'dialogIds';
	}

	public function toRestFormat(array $option = []): ?array
	{
		return $this->dialogIds;
	}
}
