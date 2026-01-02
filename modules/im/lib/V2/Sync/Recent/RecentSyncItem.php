<?php

namespace Bitrix\Im\V2\Sync\Recent;

use Bitrix\Im\V2\Recent\RecentItem;

class RecentSyncItem extends RecentItem
{
	public function toRestFormat(array $option = []): array
	{
		return [
			'dialogId' => $this->dialogId,
			'chatId' => $this->chatId,
			'messageId' => $this->messageId,
			'pinned' => $this->pinned,
			'unread' => $this->unread,
			'dateUpdate' => $this->dateUpdate,
			'dateLastActivity' => $this->dateLastActivity,
		];
	}
}
