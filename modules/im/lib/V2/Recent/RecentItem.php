<?php

namespace Bitrix\Im\V2\Recent;

use Bitrix\Im\Model\EO_Recent;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Rest\RestConvertible;

class RecentItem implements RestConvertible
{
	protected string $dialogId;
	protected int $chatId;
	protected int $messageId;
	protected bool $pinned = false;
	protected bool $unread = false;
	protected array $options = [];
	protected array $invited = [];

	public static function initByEntity(EO_Recent $entity): self
	{
		$recentItem = new static();

		$recentItem->dialogId = static::formDialogId($entity->getItemId(), $entity->getItemType());
		$recentItem->chatId = $entity->getItemCid();
		$recentItem->messageId = $entity->getItemMid();
		$recentItem->pinned = $entity->getPinned();
		$recentItem->unread = $entity->getUnread();

		return $recentItem;
	}

	public function getDialogId(): string
	{
		return $this->dialogId;
	}

	public function setDialogId(string $dialogId): RecentItem
	{
		$this->dialogId = $dialogId;
		return $this;
	}

	public function getChatId(): int
	{
		return $this->chatId;
	}

	public function setChatId(int $chatId): RecentItem
	{
		$this->chatId = $chatId;
		return $this;
	}

	public function getMessageId(): int
	{
		return $this->messageId;
	}

	public function setMessageId(int $messageId): RecentItem
	{
		$this->messageId = $messageId;
		return $this;
	}

	public function isPinned(): bool
	{
		return $this->pinned;
	}

	public function setPinned(bool $pinned): RecentItem
	{
		$this->pinned = $pinned;
		return $this;
	}

	public function isUnread(): bool
	{
		return $this->unread;
	}

	public function setUnread(bool $unread): RecentItem
	{
		$this->unread = $unread;
		return $this;
	}

	public function getOptions(): array
	{
		return $this->options;
	}

	public function setOptions(array $options): RecentItem
	{
		$this->options = $options;
		return $this;
	}

	public function getInvited(): array
	{
		return $this->invited;
	}

	public function setInvited(array $invited): RecentItem
	{
		$this->invited = $invited;
		return $this;
	}

	protected static function formDialogId(int $id, string $type): string
	{
		if ($type === Chat::IM_TYPE_PRIVATE)
		{
			return (string)$id;
		}

		return "chat{$id}";
	}

	public static function getRestEntityName(): string
	{
		return 'recentItem';
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'dialogId' => $this->dialogId,
			'chatId' => $this->chatId,
			'messageId' => $this->messageId,
			'pinned' => $this->pinned,
			'unread' => $this->unread,
			'options' => $this->options,
			'invited' => $this->invited,
		];
	}
}