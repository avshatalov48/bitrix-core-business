<?php

namespace Bitrix\UI\EntitySelector;

class PreselectedItem implements \JsonSerializable
{
	protected $id;
	protected $entityId;
	protected $item;

	public function __construct(array $options)
	{
		if (!empty($options['id']) && (is_string($options['id']) || is_int($options['id'])))
		{
			$this->id = $options['id'];
		}

		if (!empty($options['entityId']) && is_string($options['entityId']))
		{
			$this->entityId = $options['entityId'];
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
