<?php

namespace Bitrix\Rest\V3\Dto;

use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Contract\Arrayable;

class DtoCollection implements \IteratorAggregate, \Countable, Arrayable, \JsonSerializable
{
	/** @var string Dto class name */
	protected string $type;

	protected array $items = [];

	public function __construct(string $type)
	{
		if (!is_subclass_of($type, Dto::class))
		{
			throw new SystemException($type . ' is not instance of "' . Dto::class . '"');
		}

		$this->type = $type;
	}

	public function add(Dto $dto): void
	{
		$this->items[] = $dto;
	}

	public function getIterator(): \Traversable
	{
		return new \ArrayIterator($this->items);
	}

	public function getPropertyValues(string $propertyName): array
	{
		$values = [];
		foreach ($this->items as $item)
		{
			if (isset($item->$propertyName))
			{
				$values[] = $item->$propertyName;
			}
			elseif (method_exists($item, 'get' . ucfirst($propertyName)))
			{
				$methodName = 'get' . ucfirst($propertyName);
				$values[] = $item->$methodName();
			}
			elseif (method_exists($item, '__get'))
			{
				$values[] = $item->$propertyName;
			}
		}

		return $values;
	}

	public function first(): ?Dto
	{
		return $this->items[0] ?? null;
	}

	public function toArray(): array
	{
		$result = [];
		/** @var Dto $item */
		foreach ($this->items as $item)
		{
			$result[] = $item->toArray();
		}

		return $result;
	}

	public function count(): int
	{
		return count($this->items);
	}

	public function jsonSerialize(): mixed
	{
		$result = [];
		/** @var Dto $item */
		foreach ($this->items as $item)
		{
			$result[] = $item->toArray();
		}

		return $result;
	}
}