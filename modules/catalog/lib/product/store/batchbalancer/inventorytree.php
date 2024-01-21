<?php
namespace Bitrix\Catalog\Product\Store\BatchBalancer;

use Bitrix\Catalog\Product\Store\BatchBalancer\Entity\StoreItem;
use Bitrix\Main\Result;
use Bitrix\Catalog\Product\Store\BatchBalancer\Entity\ElementBatchItem;

/**
 * Class InventoryTree
 *
 * @package Bitrix\Catalog\Product\Store\BatchBalancer
 */
abstract class InventoryTree extends \ArrayObject
{
	public function save(): Result
	{
		$result = new Result();
		foreach ($this->getIterator() as $item)
		{
			$item->save();
		}

		return $result;
	}
}