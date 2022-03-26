<?php

namespace Bitrix\Mail\Collection;
use Bitrix\Mail\Item\Base as Item;

/**
 * @todo move to main if possible
 * @see Item
 */
abstract class Immutable implements \Iterator, \Countable
{
	protected $position = 0;

	/** @var Item[] */
	protected $items = array();

	protected function appendItem(Item $item, $throwException = true)
	{
		if ($this->ensureItem($item, $throwException)) {
			$this->items[] = $item;
			return true;
		}
		return false;
	}

	protected function appendCollection(Immutable $collection, $throwException = true)
	{
		foreach ($collection as $item) {
			$this->appendItem($item, $throwException);
		}
	}

	public function ensureItem(Item $item, $throwException = true)
	{
		if ($throwException) {
			// TODO: unsupported item special exception
			throw new \Bitrix\Main\SystemException('unsupported item');
		}
		return false;
	}

	abstract public function createItem(array $array);

	public function __construct(array $data = [])
	{
		foreach ($data as $item) {
			$this->appendItem($item);
		}
	}

	/**
	 * @param array $array
	 * @return static
	 * @throws \Exception
	 */
	public static function fromArray(array $array)
	{
		$collection = new static();
		foreach ($array as $row) {
			if ($item = $collection->createItem($row)) {
				$collection->appendItem($item);
			}
		}
		return $collection;
	}

	public function getFirst(&$position)
	{
		$position = 0;
		return $this->getNext($position);
	}

	public function getNext(&$position)
	{
		return isset($this->items[$position]) ? $this->items[$position++] : null;
	}

	/**
	 * @return Item
	 * @see \Iterator
	 */
	public function current()
	{
		return $this->items[$this->position];
	}

	/**
	 * @return void
	 * @see \Iterator
	 */
	public function next()
	{
		++$this->position;
	}

	/**
	 * @return int
	 * @see \Iterator
	 */
	public function key()
	{
		return $this->position;
	}

	/**
	 * @return bool
	 * @see \Iterator
	 */
	public function valid()
	{
		return isset($this->items[$this->position]);
	}

	/**
	 * @see \Iterator
	 */
	public function rewind()
	{
		$this->position = 0;
	}

	/**
	 * @return int
	 * @see \Countable
	 */
	public function count()
	{
		return count($this->items);
	}
}