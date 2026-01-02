<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Entity;

/**
 * @internal
 */
class MessageBoxCollection implements MessageBoxCollectionInterface
{
	/**
	 * @var MessageBox[]
	 */
	protected array $items = [];

	/**
	 * @param MessageBox[] $items
	 */
	public function __construct(MessageBox... $items)
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

	public function add(MessageBox $item): void
	{
		$this->items[] = $item;
	}

	/**
	 * @return MessageBox[]
	 */
	public function toArray(): array
	{
		return $this->items;
	}
}
