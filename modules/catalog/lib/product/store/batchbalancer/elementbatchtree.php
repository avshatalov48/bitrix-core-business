<?php
namespace Bitrix\Catalog\Product\Store\BatchBalancer;

use Bitrix\Catalog\Product\Store\BatchBalancer\Entity\ElementBatchItem;

/**
 * Class ElementBatchTree
 *
 * @package Bitrix\Catalog\Product\Store\BatchBalancer
 */
final class ElementBatchTree extends InventoryTree
{
	public function push(ElementBatchItem $item): void
	{
		$this->append($item);
	}
}