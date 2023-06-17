<?php

namespace Bitrix\Calendar\Access\Model;

use Bitrix\Calendar\Access\AccessibleSection;
use Bitrix\Main\Access\AccessibleItem;

class SectionModel implements AccessibleSection
{
	private static array $cache = [];

	private int $id = 0;
	private string $type = '';
	private int $ownerId = 0;

	public static function createFromId(int $itemId): AccessibleItem
	{
		if (!isset(static::$cache[$itemId]))
		{
			$model = new self();
			$model->setId($itemId);
			static::$cache[$itemId] = $model;
		}

		return static::$cache[$itemId];
	}

	public static function createNew(): self
	{
		return new self();
	}

	public static function createFromArray(array $fields): self
	{
		if (($fields['ID'] ?? false) && (int)$fields['ID'] > 0)
		{
			$model = self::createFromId((int)$fields['ID']);
		}
		else
		{
			$model = self::createNew();
		}

		if (($fields['CAL_TYPE'] ?? false) && is_string($fields['CAL_TYPE']))
		{
			$model->setType($fields['CAL_TYPE']);
		}

		if (($fields['OWNER_ID'] ?? false) && (int)$fields['OWNER_ID'] > 0)
		{
			$model->setOwnerId((int)$fields['OWNER_ID']);
		}

		return $model;
	}

	public static function createFromEventModel(EventModel $eventModel): self
	{
		if ($eventModel->getSectionId() > 0)
		{
			$model = self::createFromId($eventModel->getSectionId());
		}
		else
		{
			$model = self::createNew();
		}

		$model
			->setType($eventModel->getSectionType())
			->setOwnerId($eventModel->getOwnerId())
		;

		return $model;
	}

	public static function createFromEventModelParentFields(EventModel $eventModel): self
	{
		if ($eventModel->getSectionId() > 0)
		{
			$model = self::createFromId($eventModel->getParentEventSectionId());
		}
		else
		{
			$model = self::createNew();
		}

		$model
			->setType($eventModel->getParentEventSectionType())
			->setOwnerId($eventModel->getParentEventOwnerId())
		;

		return $model;
	}

	public function setId(int $id): self
	{
		$this->id = $id;
		return $this;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function setType(string $type): self
	{
		$this->type = $type;
		return $this;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setOwnerId(int $ownerId): self
	{
		$this->ownerId = $ownerId;
		return $this;
	}

	public function getOwnerId(): int
	{
		return $this->ownerId;
	}
}