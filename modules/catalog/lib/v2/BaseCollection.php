<?php

namespace Bitrix\Catalog\v2;

use Bitrix\Main\InvalidOperationException;
use Bitrix\Main\Result;

/**
 * Class BaseCollection
 *
 * @package Bitrix\Catalog\v2
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
abstract class BaseCollection implements \IteratorAggregate, \Countable
{
	/** @var \Bitrix\Catalog\v2\BaseEntity */
	private $parent;

	/** @var \Bitrix\Catalog\v2\BaseEntity[] */
	protected $items = [];
	/** @var \Bitrix\Catalog\v2\BaseEntity[] */
	protected $removedItems = [];

	/** @var \Closure */
	private $iteratorCallback;
	/** @var bool */
	private $loaded = false;
	/** @var bool */
	private $parentChanged = false;

	public function getParent(): ?BaseEntity
	{
		return $this->parent;
	}

	public function setParent(?BaseEntity $parent): self
	{
		if ($this->parent !== null && $this->parent !== $parent)
		{
			$this->parentChanged = true;
		}

		$this->parent = $parent;

		return $this;
	}

	public function add(BaseEntity ...$items): self
	{
		foreach ($items as $item)
		{
			$this->addInternal($item);
		}

		return $this;
	}

	protected function addInternal(BaseEntity $item): void
	{
		$item->setParentCollection($this);
		$this->setItem($item);
	}

	public function remove(BaseEntity ...$items): self
	{
		foreach ($items as $item)
		{
			if ($item->getParentCollection() !== $this)
			{
				continue;
			}

			$item->setParentCollection(null);
			$this->unsetItem($item);
		}

		return $this;
	}

	private function setItem(BaseEntity $item): self
	{
		// ToDo merge changed items? same sku with different hashes...
		$this->items[$item->getHash()] = $item;
		unset($this->removedItems[$item->getHash()]);

		return $this;
	}

	private function unsetItem(BaseEntity $item): self
	{
		unset($this->items[$item->getHash()]);
		$this->removedItems[$item->getHash()] = $item;

		return $this;
	}

	/**
	 * @param \Bitrix\Catalog\v2\BaseEntity ...$items
	 * @return $this
	 * @internal
	 */
	public function clearRemoved(BaseEntity ...$items): self
	{
		foreach ($items as $item)
		{
			unset($this->removedItems[$item->getHash()]);
		}

		return $this;
	}

	/**
	 * @return $this
	 * @internal
	 */
	public function clearChanged(): self
	{
		foreach ($this->items as $item)
		{
			$item->clearChangedFields();
		}

		$this->clearRemoved(...$this->getRemovedItems());

		return $this;
	}

	public function isEmpty(): bool
	{
		return empty($this->items);
	}

	public function isChanged(): bool
	{
		if ($this->parentChanged)
		{
			return true;
		}

		if (!empty($this->removedItems))
		{
			return true;
		}

		foreach ($this->items as $entity)
		{
			if ($entity->isChanged())
			{
				return true;
			}
		}

		return false;
	}

	public function findById(int $id): ?BaseEntity
	{
		foreach ($this->getIterator() as $item)
		{
			if ($item->getId() === $id)
			{
				return $item;
			}
		}

		return null;
	}

	public function toArray(): array
	{
		$result = [];
		$counter = 0;

		foreach ($this->items as $entity)
		{
			$fields = $entity->getFields();

			if ($entity->isNew())
			{
				$result['n'.$counter++] = $fields;
			}
			else
			{
				$result[$entity->getId()] = $fields;
			}
		}

		return $result;
	}

	/*public function max($field)
	{
		return array_reduce($this->collection, static function ($result, $item) use ($field) {
			$value = $item[$field] ?? null;

			return $result === null || $value > $result ? $value : $result;
		});
	}

	public function min($field)
	{
		return array_reduce($this->collection, static function ($result, $item) use ($field) {
			$value = $item[$field] ?? null;

			return $result === null || $value < $result ? $value : $result;
		});
	}

	public function avg($field)
	{
		return array_sum(array_column($this->collection, $field)) / count($this->collection);
	}*/

	/**
	 * @return \ArrayIterator|\Traversable|\Bitrix\Catalog\v2\BaseEntity[]
	 */
	public function getRemovedItems(): \ArrayIterator
	{
		return new \ArrayIterator(array_values($this->removedItems));
	}

	/**
	 * @return \ArrayIterator|\Traversable|\Bitrix\Catalog\v2\BaseEntity[]
	 */
	public function getIterator()
	{
		$this->loadItems();

		// workaround - spread operator (...) doesn't work with associative arrays
		return new \ArrayIterator(array_values($this->items));
	}

	protected function loadItems(): void
	{
		if ($this->iteratorCallback && !$this->isLoaded())
		{
			$this->loadByIteratorCallback();
		}

		$this->loaded = true;
	}

	/**
	 * @return bool
	 */
	protected function isLoaded(): bool
	{
		return $this->loaded;
	}

	protected function loadByIteratorCallback(): void
	{
		$iterator = $this->iteratorCallback;
		$params = [
			'filter' => $this->getAlreadyLoadedFilter(),
		];

		foreach ($iterator($params) as $entity)
		{
			$this->addInternal($entity);
		}
	}

	protected function getAlreadyLoadedFilter(): array
	{
		return [];
	}

	/**
	 * @param \Closure $iteratorCallback
	 * @return BaseCollection
	 * @internal
	 */
	public function setIteratorCallback(\Closure $iteratorCallback): BaseCollection
	{
		if ($this->iteratorCallback !== null)
		{
			throw new InvalidOperationException('Collection iterator already exists.');
		}

		$this->iteratorCallback = $iteratorCallback;

		return $this;
	}

	/**
	 * @return \Bitrix\Main\Result
	 * @internal
	 */
	public function saveInternal(): Result
	{
		$result = new Result();

		if ($this->isChanged())
		{
			foreach ($this->items as $item)
			{
				$res = $item->saveInternal();

				if (!$res->isSuccess())
				{
					$result->addErrors($res->getErrors());
				}
			}

			foreach ($this->getRemovedItems() as $item)
			{
				$res = $item->deleteInternal();

				if ($res->isSuccess())
				{
					$this->clearRemoved($item);
				}
				else
				{
					$result->addErrors($res->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @return \Bitrix\Main\Result
	 * @internal
	 */
	public function deleteInternal(): Result
	{
		$result = new Result();

		$this->loadItems();
		$items = array_merge($this->items, $this->removedItems);

		foreach ($items as $item)
		{
			$res = $item->deleteInternal();

			if (!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}

		return $result;
	}

	public function count(): int
	{
		return count($this->getIterator());
	}
}