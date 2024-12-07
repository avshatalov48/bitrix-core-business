<?php

namespace Bitrix\Calendar\Access\Model;

use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory;
use Bitrix\Main\Access\AccessibleItem;

final class EventCategoryModel implements AccessibleItem
{
	private ?int $id = null;
	private ?int $creatorId = null;
	private ?bool $closed = null;

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function getCreatorId(): ?int
	{
		return $this->creatorId;
	}

	public function setCreatorId(int $creatorId): self
	{
		$this->creatorId = $creatorId;

		return $this;
	}

	public function isClosed(): ?bool
	{
		return $this->closed;
	}

	public function setClosed(bool $closed): self
	{
		$this->closed = $closed;

		return $this;
	}

	public static function createFromObject(EventCategory $category): self
	{
		return self::createFromId($category->getId())
			->setCreatorId($category->getCreatorId())
			->setClosed($category->getClosed())
		;
	}

	public static function createFromEntity(OpenEventCategory $category): self
	{
		return self::createFromId($category->getId())
			->setCreatorId($category->getCreatorId())
			->setClosed($category->getClosed())
		;
	}

	public static function createFromId(int $itemId): self
	{
		return self::createNew()->setId($itemId);
	}

	public static function createNew(): self
	{
		return new self();
	}
}
