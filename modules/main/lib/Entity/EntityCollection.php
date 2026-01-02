<?php

declare(strict_types=1);

namespace Bitrix\Main\Entity;

use Bitrix\Main\ArgumentTypeException;

class EntityCollection implements EntityCollectionInterface
{
	protected array $items = [];

	public function __construct(EntityInterface ...$items)
	{
		foreach ($items as $item)
		{
			$this->add($item);
		}
	}

	public function count(): int
	{
		return count($this->items);
	}

	public function isEmpty(): bool
	{
		return empty($this->items);
	}

	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->items);
	}

	/**
	 * {@inheritDoc}
	 */
	public function add(EntityInterface $item): void
	{
		if (!is_a($item, static::getEntityClass()))
		{
			throw new ArgumentTypeException('item', static::getEntityClass());
		}

		$this->items[] = $item;
	}

	public function find(callable $callback): ?EntityInterface
	{
		// On PHP 8.4 replace to
		// return array_find($this->items, $callback);

		foreach ($this->items as $key => $item)
		{
			if ($callback($item, $key))
			{
				return $item;
			}
		}

		return null;
	}

	public function filter(callable $callback): static
	{
		return new static(...array_filter($this->items, $callback));
	}

	/**
	 * {@inheritDoc}
	 */
	public function map(callable $callback): array
	{
		return array_map($callback, $this->items);
	}

	/**
	 * @return string
	 */
	protected static function getEntityClass(): string
	{
		return EntityInterface::class;
	}
}
