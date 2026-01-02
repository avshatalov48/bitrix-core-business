<?php

namespace Bitrix\Im\V2\Sync\Entity;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\PrivateChat;
use Bitrix\Im\V2\Sync\ChatsSync;
use Bitrix\Im\V2\Sync\Recent\RecentSync;
use Bitrix\Im\V2\Sync\Entity;
use Bitrix\Im\V2\Sync\Event;

class Chats implements Entity
{
	private array $chatIds = [];
	private array $messageIds = [];
	private array $recentChatIds = [];
	private array $dialogIds = [];
	private array $shortInfoChatIds = [];
	private array $deletedChatIds = [];
	private array $completeDeleteChatIds = [];
	private bool $readAll = false;
	private array $chats;
	private RecentSync $recent;

	public function add(Event $event): void
	{
		$entityId = $event->entityId;
		switch ($event->eventName)
		{
			case Event::DELETE_EVENT:
				$this->shortInfoChatIds[$entityId] = $entityId;
				$this->deletedChatIds[$entityId] = $entityId;
				break;
			case Event::COMPLETE_DELETE_EVENT:
				$this->shortInfoChatIds[$entityId] = $entityId;
				$this->completeDeleteChatIds[$entityId] = $entityId;
				break;
			case Event::ADD_EVENT:
				$this->chatIds[$entityId] = $entityId;
				break;
			case Event::READ_ALL_EVENT:
				$this->readAll = true;
				break;
		}
	}

	private function getRecent(): RecentSync
	{
		$this->getChats();

		if (!isset($this->recent))
		{
			$this->recent = RecentSync::getRecentSync($this->chatIds);

			$entities = $this->recent->getEntityIds();
			$this->recentChatIds = $entities['chatIds'];
			$this->messageIds = $entities['messageIds'];
			$this->dialogIds = $entities['dialogIds'];
		}

		return $this->recent;
	}

	public function getShortInfoChatIds(): array
	{
		return $this->shortInfoChatIds;
	}

	public function getMessageIds(): array
	{
		return $this->messageIds;
	}

	public function getChatItems(): ChatsSync
	{
		return new ChatsSync($this->getChats(), $this->getRecent());
	}

	private function getChats(): array
	{
		if (isset($this->chats))
		{
			return $this->chats;
		}

		$this->chats = [];
		foreach ($this->chatIds as $chatId)
		{
			$chat = Chat::getInstance((int)$chatId);
			if (
				$chat instanceof Chat\NullChat
				|| $chat instanceof Chat\NotifyChat
			)
			{
				unset($this->chatIds[$chatId]);
			}
			else
			{
				$this->fillDialogId($chat);
				$this->chats[$chatId] = $chat;
			}
		}

		return $this->chats;
	}

	private function fillDialogId(Chat $chat): void
	{
		if (!$chat instanceof PrivateChat)
		{
			return;
		}

		$dialogId = $this->dialogIds[$chat->getId()] ?? null;
		if ($dialogId !== null)
		{
			$chat->setDialogId((string)$dialogId);
		}
	}

	public static function getRestEntityName(): string
	{
		return 'chatSync';
	}

	public function toRestFormat(array $option = []): ?array
	{
		$this->getRecent();

		$result = [
			'addedRecent' => $this->recentChatIds,
			'addedChats' => $this->chatIds,
			'deletedChats' => $this->deletedChatIds,
			'completeDeletedChats' => $this->completeDeleteChatIds,
		];

		if ($this->readAll)
		{
			$result['readAllChats'] = true;
		}

		return $result;
	}
}
