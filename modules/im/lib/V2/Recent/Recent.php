<?php

namespace Bitrix\Im\V2\Recent;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Message\MessagePopupItem;
use Bitrix\Im\V2\Registry;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\RestConvertible;

/**
 * @extends Registry<RecentItem>
 */
class Recent extends Registry implements RestConvertible, PopupDataAggregatable
{
	use ContextCustomer;

	public function getPopupData(array $excludedList = []): PopupData
	{
		$messageIds = [];
		$chats = [];

		foreach ($this as $item)
		{
			$messageIds[] = $item->getMessageId();
			$chats[] = Chat::getInstance($item->getChatId());
		}

		return new PopupData([
			new MessagePopupItem($messageIds, true),
			new Chat\ChatPopupItem($chats),
			new BirthdayPopupItem(),
		], $excludedList);
	}

	final public static function getRestEntityName(): string
	{
		return 'recentItems';
	}

	public function toRestFormat(array $option = []): array
	{
		$rest = [];

		foreach ($this as $item)
		{
			$rest[] = $item->toRestFormat();
		}

		return $rest;
	}
}
