<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Rest\PopupDataItem;

class ChatPopupItem implements PopupDataItem
{
	/**
	 * @var Chat[]
	 */
	private array $chats;

	public function __construct(array $chats)
	{
		$this->chats = $chats;
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

		foreach ($this->chats as $chat)
		{
			$rest[] = $chat->toRestFormat(['CHAT_SHORT_FORMAT' => true]);
		}

		return $rest;
	}
}