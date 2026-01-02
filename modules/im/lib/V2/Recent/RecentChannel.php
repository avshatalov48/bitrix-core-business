<?php

namespace Bitrix\Im\V2\Recent;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class RecentChannel extends Recent
{
	public static function getOpenChannels(int $limit, ?int $lastMessageId = null): self
	{
		$recent = new static();
		$userId = $recent->getContext()->getUserId();
		$chatEntities = static::getEntities($limit, $userId, $lastMessageId);

		foreach ($chatEntities as $entity)
		{
			$recentItem = new RecentItem();
			$recentItem
				->setMessageId((int)$entity['LAST_MESSAGE_ID'])
				->setChatId((int)$entity['ID'])
				->setDialogId('chat' . $entity['ID'])
				->setPinned($entity['PINNED'] === 'Y')
			;
			$recent[] = $recentItem;
		}

		return $recent;
	}

	protected static function getEntities(int $limit, int $userId, ?int $lastMessageId = null): array
	{
		$query = ChatTable::query()
			->setSelect(['ID', 'LAST_MESSAGE_ID', 'PINNED' => 'RECENT.PINNED'])
			->where('TYPE', Chat::IM_TYPE_OPEN_CHANNEL)
			->registerRuntimeField(new Reference(
					'RECENT',
					RecentTable::class,
					Join::on('this.ID', 'ref.ITEM_ID')
						->where('ref.ITEM_TYPE', Chat::IM_TYPE_OPEN_CHANNEL)
						->where('ref.USER_ID', $userId),
				)
			)
			->setLimit($limit)
			->setOrder(['LAST_MESSAGE_ID' => 'DESC'])
		;

		if (isset($lastMessageId))
		{
			$query->where('LAST_MESSAGE_ID', '<', $lastMessageId);
		}

		return $query->fetchAll();
	}
}
