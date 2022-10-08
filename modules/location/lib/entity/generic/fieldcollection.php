<?php

namespace Bitrix\Location\Entity\Generic;

use Bitrix\Main\SystemException;

/**
 * Class FieldCollection
 * @package Bitrix\Location\Entity\Generic
 * @internal
 */
abstract class FieldCollection extends Collection
{
	/** @var IField[] */
	protected $items = [];

	/**
	 * @param int $type
	 * @return bool
	 */
	public function isItemExist(int $type): bool
	{
		foreach($this->items as $item)
		{
			if($item->getType() === $type)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $type
	 * @return IField|null
	 */
	public function getItemByType(int $type): ?IField
	{
		foreach($this->items as $item)
		{
			if($item->getType() === $type)
			{
				return $item;
			}
		}

		return null;
	}

	/**
	 * @return IField[]
	 */
	public function getSortedItems(): array
	{
		$result = $this->items;

		uasort(
			$result,
			function ($a, $b)
			{
				if ($a->getType() == $b->getType())
				{
					return 0;
				}
				return ($a->getType() < $b->getType()) ? -1 : 1;
			}
		);

		return $result;
	}

	/**
	 * @param mixed $item
	 * @return int
	 * @throws SystemException
	 */
	public function addItem($item): int
	{
		if($this->isItemExist($item->getType()))
		{
			throw new SystemException('Item with type "'.$item->getType().'" already exist in this collection');
		}

		return parent::addItem($item);
	}
}
