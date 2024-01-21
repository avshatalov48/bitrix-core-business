<?php
namespace Bitrix\Catalog\Product\Store\DistributionStrategy;

use Bitrix\Catalog\EO_StoreDocumentElement;
use Bitrix\Catalog\EO_StoreBatchDocumentElement_Collection;
use Bitrix\Catalog\EO_StoreBatch;
use Bitrix\Catalog\Product\Store\BatchManager;
use Bitrix\Catalog\StoreBatchDocumentElementTable;
use Bitrix\Main\Result;

/**
 * Class Batch
 *
 * @package Bitrix\Catalog\Product\Store\DistributionStrategy
 */
final class DeductDocument extends Base
{
	private EO_StoreDocumentElement $documentElement;
	public function __construct(BatchManager $batchManager, EO_StoreDocumentElement $documentElement)
	{
		parent::__construct($batchManager, $documentElement->getStoreFrom());

		$this->documentElement = $documentElement;
	}

	protected function addRegistryItem(EO_StoreBatch $batchItem, float $amount): Result
	{
		return StoreBatchDocumentElementTable::add([
			'DOCUMENT_ELEMENT_ID' => $this->documentElement->getId(),
			'AMOUNT' => -$amount,
			'PRODUCT_BATCH_ID' => $batchItem->getId(),
			'BATCH_PRICE' => $batchItem->getPurchasingPrice(),
			'BATCH_CURRENCY' => $batchItem->getPurchasingCurrency(),
		]);
	}

	protected function getRegistryItems(): EO_StoreBatchDocumentElement_Collection
	{
		return StoreBatchDocumentElementTable::getList([
				'filter' => [
					'=DOCUMENT_ELEMENT_ID' => $this->documentElement->getId(),
					'<AMOUNT' => 0,
				],
			])
			->fetchCollection()
		;
	}
}