<?php
namespace Bitrix\Catalog\Product\Store\BatchBalancer;

use Bitrix\Catalog\Product\Store\BatchBalancer\Entity\StoreItem;


/**
 * Class StoreTree
 *
 * @package Bitrix\Catalog\Product\Store\BatchBalancer
 */
final class StoreTree extends InventoryTree
{
	public function push(StoreItem $item): void
	{
		$this->append($item);
	}

	public function getByHash(string $hash): ?StoreItem
	{
		foreach ($this->getIterator() as $item)
		{
			if ($item->getHash() === $hash)
			{
				return $item;
			}
		}

		return null;
	}
}