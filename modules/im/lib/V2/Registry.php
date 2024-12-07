<?php

namespace Bitrix\Im\V2;

/**
 * @template T
 * @extends \ArrayObject<int,T>
 */
class Registry extends \ArrayObject
{
	public function unsetByKeys(array $keys): void
	{
		foreach ($keys as $key)
		{
			unset($this[$key]);
		}
	}

	/**
	 * @param Registry<T> $registry
	 * @return static
	 */
	public function merge(Registry $registry): self
	{
		foreach ($registry as $item)
		{
			$this[] = $item;
		}

		return $this;
	}

	/**
	 * @param callable $predicate
	 * @return $this
	 */
	public function filter(callable $predicate): self
	{
		$newCollection = clone $this;
		$keyToUnset = [];

		foreach ($newCollection as $key => $item)
		{
			if (!$predicate($item))
			{
				$keyToUnset[] = $key;
			}
		}

		foreach ($keyToUnset as $key)
		{
			unset($newCollection[$key]);
		}

		return $newCollection;
	}

	/**
	 * @return T|null
	 */
	public function getAny()
	{
		foreach ($this as $item)
		{
			return $item;
		}

		return null;
	}
}