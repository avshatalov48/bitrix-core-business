<?php

namespace Bitrix\UI\EntitySelector;

class PreselectedItem implements \JsonSerializable
{
	protected $id;
	protected $entityId;
	protected $item;

	public function __construct(array $options)
	{
		$id = $options['id'] ?? null;
		if ((is_string($id) && $id !== '') || is_int($id))
		{
			$this->id = $id;
		}

		$entityId = $options['entityId'] ?? null;
		if (is_string($entityId) && $entityId !== '')
		{
			$this->entityId = strtolower($entityId);
		}
	}

	public function getId()
	{
		return $this->id;
	}

	public function getEntityId(): string
	{
		return $this->entityId;
	}

	public function setItem(Item $item)
	{
		$this->item = $item;
	}

	public function getItem(): ?Item
	{
		return $this->item;
	}

	public function isLoaded()
	{
		return $this->getItem() !== null;
	}

	public function jsonSerialize()
	{
		return [$this->getEntityId(), $this->getId()];
	}
}
