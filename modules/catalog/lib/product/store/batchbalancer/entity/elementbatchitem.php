<?php
namespace Bitrix\Catalog\Product\Store\BatchBalancer\Entity;

use Bitrix\Catalog\EO_StoreBatch_Collection;
use Bitrix\Catalog\EO_StoreBatchDocumentElement;
use Bitrix\Catalog\StoreDocumentElementTable;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Catalog\StoreBatchTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\ShipmentItemStoreTable;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * Class Arrival
 *
 * @package Bitrix\Catalog\Product\Store\BatchBalancer\Entity
 */
class ElementBatchItem implements InventoryTreeItem
{
	private EO_StoreBatchDocumentElement $bindingElement;
	private int $storeId;
	private string $storeItemHash;

	public function __construct(EO_StoreBatchDocumentElement $bindingElement, int $storeId)
	{
		$this->bindingElement = $bindingElement;

		$this->storeId = $storeId;
	}

	public function getElement(): EO_StoreBatchDocumentElement
	{
		return $this->bindingElement;
	}

	public function isArrivalElement(): bool
	{
		return $this->bindingElement->getAmount() > 0;
	}

	public function getStoreId(): int
	{
		return $this->storeId;
	}

	public function setStoreItemHash(string $storeItemHash): void
	{
		$this->storeItemHash = $storeItemHash;
	}

	public function getStoreItemHash(): string
	{
		return $this->storeItemHash;
	}

	public function getAmount(): float
	{
		return abs($this->bindingElement->getAmount());
	}

	public function save(): Result
	{
		if (empty($this->bindingElement->getProductBatchId()))
		{
			$result = new Result();
			$result->addError(new Error('Empty store batch'));

			return $result;
		}

		return $this->bindingElement->save();
	}
}