<?php

namespace Bitrix\Calendar\Core\Base;

use Bitrix\Main\ArgumentException;

/**
 *
 */
abstract class Map extends Collection
{
	/**
	 * @param $item
	 * @param $key
	 *
	 * @return $this
	 *
	 * @throws ArgumentException
	 */
	public function add($item, $key = null): self
	{
		if ($key === null)
		{
			throw new ArgumentException('you must transfer the key');
		}

		$this->collection[$key] = $item;

		if ($this->generator)
		{
			$this->generator->send($item);
		}

		return $this;
	}

	/**
	 * @param array $items
	 *
	 * @return Collection
	 */
	public function addItems(array $items): Collection
	{
		// $this->collection = array_replace($this->collection, $items);

		$this->collection = $this->collection + $items;
		return $this;
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function getItem($key)
	{
		return $this->collection[$key] ?? null;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function has($key): bool
	{
		return isset($this->collection[$key]);
	}

	/**
	 * @param $key
	 * @param $item
	 *
	 * @return $this
	 */
	public function updateItem($item, $key): Map
	{
		$this->collection[$key] = $item;

		return $this;
	}

	/**
	 * @param array $keys
	 * @return $this
	 */
	public function getItemsByKeys(array $keys): Map
	{
		return new static(array_intersect_key($this->collection, array_flip($keys)));
	}
}
