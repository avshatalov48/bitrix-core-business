<?php

namespace Bitrix\Im\V2\Recent;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message\CounterService;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Type\DateTime;

class RecentExternalChat extends Recent
{
	public static function getExternalChats(string $type, int $limit, ?DateTime $lastMessageDate = null): self
	{
		$recent = new static();
		$userId = $recent->getContext()->getUserId();
		$entityType = self::getEntityTypeByType($type);
		$recentEntities = static::getOrmEntities($limit, $userId, Chat::IM_TYPE_EXTERNAL, $lastMessageDate, $entityType);

		$chatIds = $recentEntities->getItemCidList();
		$counters = (new CounterService($userId))->getForEachChat($chatIds);

		foreach ($recentEntities as $entity)
		{
			$recent[] = RecentItem::initByEntity($entity, $counters[$entity->getItemCid()] ?? 0);
		}

		return $recent;
	}

	protected static function getEntityTypeByType(string $type): string
	{
		return (new Converter(Converter::TO_SNAKE | Converter::TO_UPPER))->process($type);
	}
}
