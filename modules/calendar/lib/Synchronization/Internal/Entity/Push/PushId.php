<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Entity\Push;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\Contract\Arrayable;

class PushId implements Arrayable
{
	/**
	 * @param string $entityType
	 * @param int $entityId
	 *
	 * @throws ArgumentException
	 */
	public function __construct(public readonly string $entityType, public readonly int $entityId)
	{
		if (mb_strlen($this->entityType) > 24)
		{
			throw new ArgumentException('The entityType max length is 24');
		}

		if ($this->entityId < 1)
		{
			throw new ArgumentException('The entityId should be a positive number');
		}
	}

	/**
	 * @param int $id
	 *
	 * @return self
	 *
	 * @throws ArgumentException
	 */
	public static function buildForSectionConnection(int $id): self
	{
		return new self(EntityType::SectionConnection->value, $id);
	}

	/**
	 * @param int $id
	 *
	 * @return self
	 *
	 * @throws ArgumentException
	 */
	public static function buildForConnection(int $id): self
	{
		return new self(EntityType::Connection->value, $id);
	}

	public function toArray(): array
	{
		return [
			'ENTITY_TYPE' => $this->entityType,
			'ENTITY_ID' => $this->entityId,
		];
	}
}
