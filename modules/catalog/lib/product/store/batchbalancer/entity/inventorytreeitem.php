<?php
namespace Bitrix\Catalog\Product\Store\BatchBalancer\Entity;

use Bitrix\Main\Result;

/**
 * Class Entity
 *
 * @package Bitrix\Catalog\Product\Store\BatchBalancer\Entity
 */
interface InventoryTreeItem
{
	public function save(): Result;
}