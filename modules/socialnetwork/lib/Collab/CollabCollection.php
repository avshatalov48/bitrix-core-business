<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab;

use ArrayAccess;
use ArrayIterator;
use Bitrix\Main\Type\Contract\Arrayable;
use Countable;
use IteratorAggregate;

/**
 * @method array getIdList()
 * @method array getNameList()
 */
class CollabCollection implements ArrayAccess, IteratorAggregate, Arrayable, Countable
{
	/** @var Collab[]  */
	protected array $collabs = [];

	public function __construct(Collab ...$collabs)
	{
		foreach ($collabs as $collab)
		{
			$this->collabs[$collab->getId()] = $collab;
		}
	}

	public function isEmpty(): bool
	{
		return empty($this->collabs);
	}

	public function offsetExists(mixed $offset): bool
	{
		return isset($this->collabs[$offset]);
	}

	public function offsetGet(mixed $offset): ?Collab
	{
		return $this->collabs[$offset] ?? null;
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->collabs[$offset] = $value;
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->collabs[$offset]);
	}

	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->collabs);
	}

	public function toArray(): array
	{
		$data = [];
		foreach ($this->collabs as $collab)
		{
			$data[$collab->getId()] = $collab->toArray();
		}

		return $data;
	}

	public function getFirst(): ?Collab
	{
		foreach ($this->collabs as $collab)
		{
			return $collab;
		}

		return null;
	}

	public function count(): int
	{
		return count($this->collabs);
	}

	public function __call(string $name, array $arguments = [])
	{
		$operation = substr($name, 0, 3);
		$subOperation =  lcfirst(substr($name, -4));

		if ($operation === 'get' && $subOperation === 'list')
		{
			$property = strtoupper(substr($name, 3, -4));

			return array_column($this->toArray(), $property);
		}

		return null;
	}
}