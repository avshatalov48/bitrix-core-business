<?php

namespace Bitrix\Im\V2\Recent;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message\CounterService;
use Bitrix\Main\Type\DateTime;

class RecentCollab extends Recent
{
	public static function getCollabs(int $limit, ?DateTime $lastMessageDate = null): self
	{
		$recent = new static();
		$userId = $recent->getContext()->getUserId();
		$recentEntities = static::getOrmEntities($limit, $userId, Chat::IM_TYPE_COLLAB, $lastMessageDate);

		$chatIds = $recentEntities->getItemCidList();
		$counters = (new CounterService($userId))->getForEachChat($chatIds);

		foreach ($recentEntities as $entity)
		{
			$recent[] = $recent[] = RecentItem::initByEntity($entity, $counters[$entity->getItemCid()] ?? 0);
		}

		return $recent;
	}
}
