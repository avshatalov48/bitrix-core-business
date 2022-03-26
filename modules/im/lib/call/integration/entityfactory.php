<?php

namespace Bitrix\Im\Call\Integration;

use Bitrix\Im\Call\Call;
use Bitrix\Main\ArgumentException;

class EntityFactory
{
	/**
	 * Return proxy object, to access entity, associated with the call.
	 *
	 * @param Call $call The call object.
	 * @param string $entityType Type of the associated entity.
	 * @param integer $entityId Id of the associated entity.
	 * @return AbstractEntity
	 * @throws ArgumentException
	 */
	public static function createEntity(Call $call, $entityType, $entityId)
	{
		if($entityType === EntityType::CHAT)
		{
			return new Chat($call, $entityId);
		}

		throw new ArgumentException("Unknown entity type: " . $entityType);
	}
}