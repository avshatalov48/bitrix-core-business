<?php

namespace Bitrix\Location\Entity\Generic;

/**
 * Class Collection
 * @package Bitrix\Location\Entity\Generic
 * @internal
 */
class Collection
	implements \ArrayAccess, \Countable, \Iterator
{
	protected $items = [];

	/**
	 * @param array $items
	 */
	public function  __construct(array  $items = [])
	{
		$this->setItems($items);
	}

	/**
	 * @param array $items
	 */
	public function setItems(array $items): void
	{
		foreach($items as $item)
		{
			$this->addItem($item);
		}
	}

	/**
	 * @return array
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		if ($offset === null)
		{
			$this->items[] = $value;
		}
		else
		{
			$this->items[$offset] = $value;
		}
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->items[$offset]);
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->items[$offset]);
	}

	/**
	 * @param mixed $offset
	 * @return mixed|null
	 */
	public function offsetGet($offset)
	{
		return isset($this->items[$offset]) ? $this->items[$offset] : null;
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->items);
	}

	public function rewind()
	{
		reset($this->items);
	}

	/**
	 * @return mixed
	 */
	public function current()
	{
		return current($this->items);
	}

	/**
	 * @return int
	 */
	public function key()
	{
		return key($this->items);
	}

	public function next()
	{
		next($this->items);
	}

	/**
	 * @return bool
	 */
	public function valid()
	{
		return $this->current() !== false && isset($this->items[$this->key()]);
	}

	public function clear()
	{
		$this->items = [];
	}

	/**
	 * @param mixed $item
	 * @return int
	 */
	public function addItem($item): int
	{
		$this->items[] = $item;
		end($this->items);
		return key($this->items);
	}
}
