<?php

namespace Bitrix\Im\V2;

/**
 * @template T
 */
class Registry extends \ArrayObject
{
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