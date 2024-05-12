<?php

namespace Bitrix\Im\V2\Sync\Entity;

use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Sync\Entity;

class DialogIds implements Entity
{
	use ContextCustomer;

	private Messages $messages;
	private PinMessages $pins;
	private Chats $chats;
	private array $dialogIds = [];
	private array $chatsWithoutDialogIds = [];
	private bool $isLoaded = false;

	public function __construct(Messages $messages, PinMessages $pins, Chats $chats)
	{
		$this->messages = $messages;
		$this->pins = $pins;
		$this->chats = $chats;
	}

	public function getData(): array
	{
		return ['dialogIds' => $this->getDialogIds()];
	}

	private function getDialogIds(): array
	{
		if (!$this->isLoaded)
		{
			$this->load();
		}

		return $this->dialogIds;
	}

	private function load(): void
	{
		$this
			->fillChatIds()
			->loadFromFetchedRecent()
			->loadFromFetchedChats()
			->loadFromNonPrivateChats()
			->loadFromPrivateChat()
		;
		$this->isLoaded = true;
	}

	private function fillChatIds(): self
	{
		foreach ($this->messages->getFullMessages() as $message)
		{
			$this->chatsWithoutDialogIds[$message->getChatId()] = $message->getChatId();
		}

		foreach ($this->pins->getPins() as $pin)
		{
			$this->chatsWithoutDialogIds[$pin->getChatId()] = $pin->getChatId();
		}

		foreach ($this->chats->getChats() as $chat)
		{
			$this->chatsWithoutDialogIds[(int)$chat['id']] = (int)$chat['id'];
		}

		return $this;
	}

	private function loadFromFetchedRecent(): self
	{
		$recent = $this->chats->getRecent();

		foreach ($recent as $recentItem)
		{
			$chatId = (int)$recentItem['chat_id'];
			$dialogId = (string)$recentItem['id'];
			$this->add($chatId, $dialogId);
		}

		return $this;
	}

	private function loadFromFetchedChats(): self
	{
		if (!$this->needContinue())
		{
			return $this;
		}

		foreach ($this->chats->getChats() as $chat)
		{
			if ($chat['message_type'] !== Chat::IM_TYPE_PRIVATE)
			{
				$chatId = $chat['id'];
				$dialogId = 'chat' . $chatId;
				$this->add($chatId, $dialogId);
			}
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
}