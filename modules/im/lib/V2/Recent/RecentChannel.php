<?php

namespace Bitrix\Im\V2\Recent;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\EO_Chat_Collection;
use Bitrix\Im\V2\Chat;

class RecentChannel extends Recent
{
	public static function getOpenChannels(int $limit, ?int $lastMessageId = null): self
	{
		$recent = new static();
		$chatEntities = static::getOrmEntities($limit, $lastMessageId);

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

	protected static function getOrmEntities(int $limit, ?int $lastMessageId = null): EO_Chat_Collection
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
}
