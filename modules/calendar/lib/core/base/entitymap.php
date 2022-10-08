<?php

namespace Bitrix\Calendar\Core\Base;

use Bitrix\Main\ArgumentException;

/**
 * Wrapper for working with entities that have implemented the interface EntityInterface
 */
class EntityMap extends Map
{
	/**
	 * @param EntityInterface[] $items
	 *
	 * @return $this
	 *
	 * @throws ArgumentException
	 */
	public function addItems(array $items): self
	{
		foreach ($items as $item)
		{
			$this->add($item);
		}

		return $this;
	}

	/**
	 * @param object|null $item
	 * @param scalar|null $key
	 *
	 * @return $this
	 *
	 * @throws ArgumentException
	 */
	public function add($item, $key = null): self
	{
		$key = $key ?? $item->getId();

		return parent::add($item, $key);
	}

	/**
	 * @param EntityInterface $item
	 * @param $key
	 *
	 * @return $this
	 */
	public function updateItem($item, $key = null): self
	{
		$key = $key ?? $item->getId();

		return parent::updateItem($item, $key);
	}
}
