<?php

namespace Bitrix\UI\EntitySelector;

class RecentItem implements \JsonSerializable
{
	protected $id;
	protected $entityId;
	protected $lastUseDate;
	protected $loaded = false;
	protected $available = true;

	public function __construct(array $options)
	{
		$id = $options['id'] ?? null;
		if ((is_string($id) && $id !== '') || is_int($id))
		{
			$id = (string)(int)$id === $id ? (int)$id : $id;
			$this->id = $id;
		}

		$entityId = $options['entityId'] ?? null;
		if (is_string($entityId) && $entityId !== '')
		{
			$this->entityId = strtolower($entityId);
		}

		if (isset($options['loaded']) && is_bool($options['loaded']))
		{
			$this->setLoaded($options['loaded']);
		}

		if (isset($options['available']) && is_bool($options['available']))
		{
			$this->setAvailable($options['available']);
		}

		if (isset($options['lastUseDate']) && is_int($options['lastUseDate']))
		{
			$this->setLastUseDate($options['lastUseDate']);
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

	public function getLastUseDate(): ?int
	{
		return $this->lastUseDate;
	}

	public function setLastUseDate(int $lastUseDate): self
	{
		$this->lastUseDate = $lastUseDate;

		return $this;
	}

	public function isLoaded(): bool
	{
		return $this->loaded;
	}

	public function setLoaded(bool $flag): self
	{
		$this->loaded = $flag;

		return $this;
	}

	public function isAvailable(): bool
	{
		return $this->available;
	}

	public function setAvailable(bool $flag): self
	{
		$this->available = $flag;

		return $this;
	}

	public function jsonSerialize()
	{
		return [$this->getEntityId(), $this->getId()];
	}
}
