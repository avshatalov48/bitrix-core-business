<?php
namespace Bitrix\Catalog\Product\Store\DistributionStrategy;

use Bitrix\Catalog\EO_StoreBatchDocumentElement_Collection;
use Bitrix\Catalog\EO_StoreBatch;
use Bitrix\Catalog\Product\Store\BatchManager;
use Bitrix\Catalog\StoreBatchDocumentElementTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Sale\ShipmentItemStore;

Loader::includeModule('sale');

/**
 * Class Batch
 *
 * @package Bitrix\Catalog\Product\Store\DistributionStrategy
 */
final class ShipmentStore extends Base
{
	private ShipmentItemStore $shipmentItemStore;
	public function __construct(BatchManager $batchManager, ShipmentItemStore $shipmentItemStore)
	{
		parent::__construct($batchManager, $shipmentItemStore->getStoreId());

		$this->shipmentItemStore = $shipmentItemStore;
	}

	protected function addRegistryItem(EO_StoreBatch $batchItem, float $amount): Result
	{
		return StoreBatchDocumentElementTable::add([
			'SHIPMENT_ITEM_STORE_ID' => $this->shipmentItemStore->getId(),
			'AMOUNT' => -$amount,
			'PRODUCT_BATCH_ID' => $batchItem->getId(),
			'BATCH_PRICE' => $batchItem->getPurchasingPrice(),
			'BATCH_CURRENCY' => $batchItem->getPurchasingCurrency(),
		]);
	}

	protected function getRegistryItems(): EO_StoreBatchDocumentElement_Collection
	{
		return StoreBatchDocumentElementTable::getList([
				'filter' => ['=SHIPMENT_ITEM_STORE_ID' => $this->shipmentItemStore->getId()],
			])
			->fetchCollection()
		;
	}
}