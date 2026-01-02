<?php

namespace Bitrix\Im\V2\Chat\MessagesAutoDelete;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Rest\PopupDataItem;

class MessagesAutoDeleteConfigs implements PopupDataItem
{
	/**
	 * @var int[]
	 */
	protected array $chatIds = [];
	protected bool $showDefaultValues = false;

	public function __construct(array $chatIds)
	{
		$this->chatIds = array_unique($chatIds);
	}

	public function merge(PopupDataItem $item): self
	{
		if ($item instanceof self)
		{
			$this->chatIds = array_unique(array_merge($this->chatIds, $item->chatIds));
		}

		return $this;
	}

	public function showDefaultValues(): self
	{
		$this->showDefaultValues = true;
		return $this;
	}

	public static function getRestEntityName(): string
	{
		return 'messagesAutoDeleteConfigs';
	}

	public function toRestFormat(array $option = []): ?array
	{
		$rest = [];

		foreach ($this->chatIds as $chatId)
		{
			$chat = Chat::getInstance($chatId);

			if ($this->showDefaultValues || $chat->getMessagesAutoDeleteDelay())
			{
				$rest[] = [
					'delay' => $chat->getMessagesAutoDeleteDelay(),
					'chatId' => $chat->getChatId(),
				];
			}
		}

		return $rest;
	}
}