<?php

namespace Bitrix\Im\V2\Notification\Group;

class GroupItemCollection implements \IteratorAggregate, \Countable
{
	/** @var GroupItem[] */
	private array $items;

	/**
	 * @param GroupItem ...$items
	 */
	public function __construct(GroupItem ...$items)
	{
		$this->items = $items;
	}

	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->items);
	}

	public function count(): int
	{
		return count($this->items);
	}

	public function toRestFormat(): array
	{
		return array_map(fn(GroupItem $item): array => $item->toRestFormat(), $this->items);
	}

	/**
	 * @return GroupItem[]
	 */
	public function getAll(): array
	{
		return $this->items;
	}
}