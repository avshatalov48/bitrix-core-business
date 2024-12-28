<?php

namespace Bitrix\Im\V2\Recent;

use Bitrix\Im\Model\EO_Recent;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Type\DateTime;

class RecentItem implements RestConvertible
{
	protected string $dialogId;
	protected int $chatId;
	protected int $messageId;
	protected int $counter = 0;
	protected int $lastReadMessageId = 0;
	protected bool $pinned = false;
	protected bool $unread = false;
	protected ?DateTime $dateUpdate = null;
	protected ?DateTime $dateLastActivity = null;
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

	public function getCounter(): int
	{
		return $this->counter;
	}

	public function setCounter(int $counter): RecentItem
	{
		$this->counter = $counter;
		return $this;
	}

	public function getLastReadMessageId(): int
	{
		return $this->lastReadMessageId;
	}

	public function setLastReadMessageId(int $lastReadMessageId): RecentItem
	{
		$this->lastReadMessageId = $lastReadMessageId;
		return $this;
	}

	public function getDateUpdate(): ?DateTime
	{
		return $this->dateUpdate;
	}

	public function setDateUpdate(?DateTime $dateUpdate): RecentItem
	{
		$this->dateUpdate = $dateUpdate;
		return $this;
	}

	public function getDateLastActivity(): ?DateTime
	{
		return $this->dateLastActivity;
	}

	public function setDateLastActivity(?DateTime $dateLastActivity): RecentItem
	{
		$this->dateLastActivity = $dateLastActivity;
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
			'lastReadMessageId' => $this->lastReadMessageId,
			'counter' => $this->counter,
			'dateUpdate' => $this->dateUpdate,
			'dateLastActivity' => $this->dateLastActivity,
		];
	}
}
