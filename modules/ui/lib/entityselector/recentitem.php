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
		if (!empty($options['id']) && (is_string($options['id']) || is_int($options['id'])))
		{
			$id = $options['id'];
			$id = is_string($id) && (string)(int)$id === $id ? (int)$id : $id;

			$this->id = $id;
		}

		if (!empty($options['entityId']) && is_string($options['entityId']))
		{
			$this->entityId = $options['entityId'];
		}

		if (!empty($options['loaded']) && is_bool($options['loaded']))
		{
			$this->setLoaded($options['loaded']);
		}

		if (!empty($options['available']) && is_bool($options['available']))
		{
			$this->setAvailable($options['available']);
		}

		if (!empty($options['lastUseDate']) && is_int($options['lastUseDate']))
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
