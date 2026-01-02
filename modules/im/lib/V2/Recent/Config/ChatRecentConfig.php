<?php

namespace Bitrix\Im\V2\Recent\Config;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Rest\PopupDataItem;

class ChatRecentConfig implements PopupDataItem
{
	private Chat $chat;

	public function __construct(Chat $chat)
	{
		$this->chat = $chat;
	}

	public function merge(PopupDataItem $item): PopupDataItem
	{
		return $this;
	}

	public static function getRestEntityName(): string
	{
		return 'recentConfig';
	}

	public function toRestFormat(array $option = []): ?array
	{
		return [
			'chatId' => $this->chat->getChatId(),
			'sections' => $this->getSections(),
		];
	}

	public function toPullFormat(): array
	{
		return [
			'chatId' => $this->chat->getChatId(),
			'sections' => $this->chat->getRecentSections(),
		];
	}

	private function getSections(): array
	{
		if ($this->chat->getRole() === Chat::ROLE_GUEST)
		{
			return $this->chat->getRecentSectionsForGuest();
		}

		return $this->chat->getRecentSections();
	}
}