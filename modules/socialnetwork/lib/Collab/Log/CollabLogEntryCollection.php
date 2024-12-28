<?php

namespace Bitrix\Socialnetwork\Collab\Log;

use Bitrix\Main\Type\Contract\Arrayable;
use Countable;

class CollabLogEntryCollection implements Arrayable, Countable
{
	private array $collection = [];

	public function add(AbstractCollabLogEntry $logEntry): self
	{
		$this->collection[] = $logEntry;

		return $this;
	}

	public function toArray(): array
	{
		$result = [];

		foreach ($this->collection as $logEntry)
		{
			$result[] = $logEntry->toArray();
		}

		return $result;
	}

	public function count(): int
	{
		return count($this->collection);
	}

	public function isEmpty(): bool
	{
		return empty($this->collection);
	}
}
