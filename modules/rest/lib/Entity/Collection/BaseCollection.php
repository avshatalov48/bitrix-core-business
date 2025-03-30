<?php

declare(strict_types=1);

namespace Bitrix\Rest\Entity\Collection;

use Bitrix\Main\ArgumentException;
use Closure;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @template T of object
 * @implements IteratorAggregate<int, T>
 */
abstract class BaseCollection implements Countable, IteratorAggregate
{
	/**
	 * @var list<T>
	 */
	private array $items = [];

	/**
	 * @param list<T> $items
	 * @throws ArgumentException
	 */
	public function __construct(mixed ...$items)
	{
		foreach ($items as $item)
		{
			$this->add($item);
		}
	}

	/**
	 * @return class-string<T>
	 */
	abstract protected static function getItemClassName(): string;

	/**
	 * @param T $item
	 * @throws ArgumentException
	 */
	public function add(mixed $item): void
	{
		if (!$this->isValidType($item))
		{
			$type = static::getItemClassName();
			throw new ArgumentException("Item must be of type {$type}");
		}

		$this->items[] = $item;
	}

	/**
	 * @return T|null
	 */
	public function get(int $index): mixed
	{
		return $this->items[$index] ?? null;
	}

	/**
	 * @return T|null
	 */
	public function first(): mixed
	{
		return $this->get(0);
	}

	/**
	 * @return list<T>
	 */
	public function all(): array
	{
		return $this->items;
	}

	public function remove(int $index): void
	{
		if (isset($this->items[$index]))
		{
			unset($this->items[$index]);
			$this->items = array_values($this->items);
		}
	}

	public function count(): int
	{
		return count($this->items);
	}

	public function isEmpty(): bool
	{
		return $this->count() === 0;
	}

	/**
	 * @return Traversable<int, T>
	 */
	public function getIterator(): Traversable
	{
		return new \ArrayIterator($this->items);
	}

	/**
	 * @param T $item
	 * @return bool
	 */
	public function contains(mixed $item): bool
	{
		return in_array($item, $this->items, true);
	}

	public function clear(): void
	{
		$this->items = [];
	}

	/**
	 * @param Closure(T):bool $callback
	 * @return static<T>
	 * @throws ArgumentException
	 */
	public function filter(Closure $callback): self
	{
		$filtered = array_filter($this->items, $callback);
		$collection = new static();

		foreach ($filtered as $item)
		{
			$collection->add($item);
		}

		return $collection;
	}

	/**
	 * @template ReturnT
	 * @param Closure(T):ReturnT $callback
	 * @return list<ReturnT>
	 */
	public function map(Closure $callback): array
	{
		return array_map($callback, $this->items);
	}

	public function __clone(): void
	{
		$this->items = $this->map(static fn($item) => clone $item);
	}

	/**
	 * @param mixed $item
	 * @return bool
	 */
	private function isValidType(mixed $item): bool
	{
		return $item instanceof (static::getItemClassName());
	}
}
