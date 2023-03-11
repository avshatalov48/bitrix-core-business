<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class CollectionBase
 * @package Bitrix\Sale\Internals
 */
abstract class CollectionBase
	implements \ArrayAccess, \Countable, \IteratorAggregate
{
	/** @var CollectableEntity[] $collection */
	protected $collection = array();

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator(): \Traversable
	{
		return new \ArrayIterator($this->collection);
	}


	/**
	 * Whether a offset exists
	 */
	public function offsetExists($offset): bool
	{
		return isset($this->collection[$offset]) || array_key_exists($offset, $this->collection);
	}

	/**
	 * Offset to retrieve
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		if (isset($this->collection[$offset]) || array_key_exists($offset, $this->collection))
		{
			return $this->collection[$offset];
		}

		return null;
	}

	/**
	 * Offset to set
	 */
	public function offsetSet($offset, $value): void
	{
		if($offset === null)
		{
			$this->collection[] = $value;
		}
		else
		{
			$this->collection[$offset] = $value;
		}
	}

	/**
	 * Offset to unset
	 */
	public function offsetUnset($offset): void
	{
		unset($this->collection[$offset]);
	}

	/**
	 * Count elements of an object
	 */
	public function count(): int
	{
		return count($this->collection);
	}

	/**
	 * Return the current element
	 */
	public function current()
	{
		return current($this->collection);
	}

	/**
	 * Move forward to next element
	 */
	public function next()
	{
		return next($this->collection);
	}

	/**
	 * Return the key of the current element
	 */
	public function key()
	{
		return key($this->collection);
	}

	/**
	 * Checks if current position is valid
	 */
	public function valid()
	{
		$key = $this->key();
		return $key !== null;
	}

	/**
	 * Rewind the Iterator to the first element
	 */
	public function rewind()
	{
		return reset($this->collection);
	}

	/**
	 * Checks if collection is empty.
	 *
	 * @return bool
	 */
	public function isEmpty()
	{
		return empty($this->collection);
	}

	public function toArray()
	{
		$result = [];

		foreach ($this->collection as $entity)
		{
			$result[] = $entity->toArray();
		}

		return $result;
	}
}
