<?php

namespace Bitrix\Calendar\Core\Base;

use Countable;
use IteratorAggregate;
use ArrayIterator;

abstract class Collection implements IteratorAggregate, Countable
{
	/**
	 * @var array
	 */
	protected array $collection = [];
	protected ?\Generator $generator = null;

	/**
	 * @param array $collection
	 */
	public function __construct(array $collection = [])
	{
		$this->collection = $collection;
	}

	/**
	 * @return ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->collection);
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return count($this->collection);
	}

	/**
	 * @param $item
	 * @return $this
	 */
	public function add($item): Collection
	{
		$this->collection[] = $item;

		if ($this->generator)
		{
			$this->generator->send($item);
		}

		return $this;
	}

	/**
	 * @param array $items
	 * @return $this
	 */
	public function addItems(array $items): Collection
	{

		$this->collection = array_merge($this->collection, $items);

		return $this;
	}

	/**
	 * @param $key
	 *
	 * @return $this
	 */
	public function remove($key): Collection
	{
		unset($this->collection[$key]);

		return $this;
	}

	/**
	 * @return array
	 */
	public function getCollection(): array
	{
		return $this->collection;
	}

	/**
	 * @return mixed
	 *
	 * @throws BaseException
	 */
	public function fetch()
	{
		if ($this->generator === null)
		{
			$this->generator = $this->fetchGenerator();
		}

		$item = $this->generator->current();
		$this->generator->next();

		return $item;
	}

	/**
	 * @return \Generator|null
	 */
	protected function fetchGenerator(): ?\Generator
	{
		if (empty($this->collection))
		{
			return null;
		}

		foreach ($this->collection as $key => $item)
		{
			yield $key => $item;
		}
	}

	/**
	 * @return void
	 */
	public function rewindGenerator(): void
	{
		if (!$this->generator->valid())
		{
			$this->generator->rewind();
		}
	}
}
