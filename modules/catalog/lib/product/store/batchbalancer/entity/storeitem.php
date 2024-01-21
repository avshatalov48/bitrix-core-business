<?php
namespace Bitrix\Catalog\Product\Store\BatchBalancer\Entity;

use Bitrix\Catalog\EO_StoreBatch;
use Bitrix\Main\Result;

/**
 * Class StoreItem
 *
 * @package Bitrix\Catalog\Product\Store\BatchBalancer\Entity
 */
final class StoreItem implements InventoryTreeItem
{
	private EO_StoreBatch $batch;
	private string $hash;
	public function __construct(EO_StoreBatch $batch)
	{
		$this->batch = $batch;

		$this->hash = md5(uniqid(rand(), true));
	}
	public function getStoreBatch(): ?EO_StoreBatch
	{
		return $this->batch;
	}

	public function getHash(): string
	{
		return $this->hash;
	}

	public function save(): Result
	{
		return $this->batch->save();
	}
}