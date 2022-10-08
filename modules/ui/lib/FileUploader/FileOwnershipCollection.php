<?php

namespace Bitrix\UI\FileUploader;

class FileOwnershipCollection implements \IteratorAggregate
{
	/** @var FileOwnership[] */
	private array $items = [];

	public function __construct(array $ids)
	{
		foreach ($ids as $id)
		{
			$this->items[] = new FileOwnership($id);
		}
	}

	/**
	 * @return FileOwnership[]
	 */
	public function getAll(): array
	{
		return $this->items;
	}

	public function count(): int
	{
		return count($this->items);
	}

	/**
	 * @return \ArrayIterator|FileOwnership[]
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->items);
	}
}