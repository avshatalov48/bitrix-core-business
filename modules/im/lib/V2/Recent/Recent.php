<?php

namespace Bitrix\Im\V2\Recent;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\EO_Chat_Collection;
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

	public static function getOpenChannels(int $limit, ?int $lastMessageId = null): self
	{
		$recent = new static();
		$chatEntities = static::getOpenChannelEntities($limit, $lastMessageId);

		foreach ($chatEntities as $entity)
		{
			$recentItem = new RecentItem();
			$recentItem
				->setMessageId($entity->getLastMessageId())
				->setChatId($entity->getId())
				->setDialogId('chat' . $entity->getId())
			;
			$recent[] = $recentItem;
		}

		return $recent;
	}

	protected static function getOpenChannelEntities(int $limit, ?int $lastMessageId = null): EO_Chat_Collection
	{
		$query = ChatTable::query()
			->setSelect(['ID', 'LAST_MESSAGE_ID'])
			->where('TYPE', Chat::IM_TYPE_OPEN_CHANNEL)
			->setLimit($limit)
			->setOrder(['LAST_MESSAGE_ID' => 'DESC'])
		;

		if (isset($lastMessageId))
		{
			$query->where('LAST_MESSAGE_ID', '<', $lastMessageId);
		}

		return $query->fetchCollection();
	}

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

	public static function getRestEntityName(): string
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