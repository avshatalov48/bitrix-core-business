<?php

namespace Bitrix\Rest\V3\Attributes;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Rest\V3\Exceptions\InvalidClassInstanceProvidedException;

#[\Attribute]
class OrmEntity extends AbstractAttribute
{
	public function __construct(public readonly string $entity)
	{
		if (!is_subclass_of($this->entity, DataManager::class))
		{
			throw new InvalidClassInstanceProvidedException($this->entity, DataManager::class);
		}
	}
}
